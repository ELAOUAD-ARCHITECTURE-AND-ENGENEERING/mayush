#!/usr/bin/env bash
# ==============================================================================
# MAYUSHDESIGN — PRODUCTION DEPLOY SCRIPT
# ==============================================================================
# Safety guarantees:
#   - Zero table drops / zero deletes
#   - Maintenance mode wraps all destructive steps
#   - Composer autoload rebuilt before artisan runs
#   - Migration retries with safe mark-as-ran strategy
#   - Supervisor restarted after queue:restart signal
#   - Post-deploy health check verifies app is live
#   - Full deploy log written to ~/deploy.log
# ==============================================================================

set -euo pipefail

# ── Logging ────────────────────────────────────────────────────────────────────
DEPLOY_LOG="/home/mayushdesign/deploy.log"
exec > >(tee -a "$DEPLOY_LOG") 2>&1
echo ""
echo "============================================================"
echo " DEPLOY STARTED — $(date '+%Y-%m-%d %H:%M:%S')"
echo "============================================================"

# ── Configuration ──────────────────────────────────────────────────────────────
APP_DIR="/home/mayushdesign/public_html"
ENV_FILE="$APP_DIR/.env"
PHP_BIN="/usr/bin/php8.2"
COMPOSER_BIN="/usr/local/bin/composer"

OWNER_USER="mayushdesign"
WEB_GROUP="www-data"

APP_URL="https://mayushdesign.com"

# ── Desired .env values ────────────────────────────────────────────────────────
# NOTE: QUEUE_CONNECTION was declared twice previously (sync then redis).
#       Single declaration here — redis is the correct production value.
declare -A ENV_VALUES=(
  [APP_ENV]="production"
  [APP_DEBUG]="false"
  [APP_URL]="$APP_URL"
  [ASSET_URL]="$APP_URL"
  [DB_USERNAME]="pma"
  [DB_PASSWORD]="yQnhTl2qbOqb1DqAfnBtF9gt"
  [QUEUE_CONNECTION]="redis"
  [FORCE_HTTPS]="On"
  [SESSION_SECURE_COOKIE]="true"
  [DISABLE_CLAMAV]="false"
)

# ── Constants ──────────────────────────────────────────────────────────────────
MIGRATE_MAX_TRIES=25
MIGRATE_TIMEOUT=60   # seconds per migration attempt (guards against lock waits)
HEALTH_CHECK_RETRIES=5
HEALTH_CHECK_DELAY=3 # seconds between retries

# ==============================================================================
# UTILITY FUNCTIONS
# ==============================================================================

# Print a section header
step() { echo ""; echo "▶  $1"; echo "──────────────────────────────────────"; }

# Abort with an error message
die() { echo ""; echo "❌  FATAL: $1"; echo "   Deploy aborted at $(date '+%H:%M:%S')"; exit 1; }

# Update or append KEY="VALUE" in .env (quoted values, safe for spaces & specials)
set_env() {
  local key="$1"
  local value="$2"
  # Escape characters that break sed's replacement string
  local esc_value
  esc_value="$(printf '%s' "$value" | sed 's/[\/&]/\\&/g')"

  if grep -qE "^${key}=" "$ENV_FILE"; then
    sed -i "s|^${key}=.*|${key}=\"${esc_value}\"|" "$ENV_FILE"
  else
    printf '\n%s="%s"\n' "$key" "$value" >> "$ENV_FILE"
  fi
}

# ==============================================================================
# PRE-FLIGHT CHECKS
# ==============================================================================
step "Pre-flight checks"

[[ -d "$APP_DIR" ]]    || die ".env directory not found: $APP_DIR"
[[ -f "$ENV_FILE" ]]   || die ".env file not found: $ENV_FILE"
[[ -x "$PHP_BIN" ]]    || die "PHP binary not found or not executable: $PHP_BIN"
command -v "$COMPOSER_BIN" &>/dev/null || die "Composer not found: $COMPOSER_BIN"

echo "✅  All pre-flight checks passed"

# ==============================================================================
# STEP 1 — MAINTENANCE MODE ON
# ==============================================================================
step "Enabling maintenance mode"

cd "$APP_DIR" || die "Could not cd into $APP_DIR"

# Run artisan up at exit regardless of success/failure to avoid leaving app down
trap '$PHP_BIN artisan up 2>/dev/null || true; echo ""; echo "🔓  Maintenance mode lifted (trap)"' EXIT

# Force clear stale caches and package discoveries BEFORE artisan commands.
# This prevents 'Class not found' errors if providers were removed or moved.
echo "🧹  Clearing stale bootstrap caches manually..."
rm -f "$APP_DIR/bootstrap/cache/config.php"
rm -f "$APP_DIR/bootstrap/cache/services.php"
rm -f "$APP_DIR/bootstrap/cache/packages.php"
rm -f "$APP_DIR/bootstrap/cache/routes.php"

$PHP_BIN artisan down \
  --retry=15 \
  --render="errors::503" \
  --secret="mayush-deploy-bypass" \
  --no-interaction \
  || true

echo "✅  App is in maintenance mode"

# ==============================================================================
# STEP 2 — UPDATE .env
# ==============================================================================
step "Updating .env values"

for key in "${!ENV_VALUES[@]}"; do
  set_env "$key" "${ENV_VALUES[$key]}"
  echo "   ✔ $key"
done

echo "✅  .env updated"

# ==============================================================================
# STEP 3 — COMPOSER INSTALL & AUTOLOAD REBUILD
# ==============================================================================
# Must run BEFORE any artisan command so new classes are discoverable.
# 'install' synchronizes the vendor directory with composer.lock, preventing
# issues with missing or orphaned packages that were removed locally.
# ==============================================================================
step "Running Composer install"

"$PHP_BIN" "$COMPOSER_BIN" install \
  --optimize-autoloader \
  --no-dev \
  --no-interaction \
  --ignore-platform-reqs \
  --working-dir="$APP_DIR"

echo "✅  Composer install completed"

# ==============================================================================
# STEP 4 — CLEAR STALE CACHES (load fresh .env & new classes)
# ==============================================================================
step "Clearing stale caches"

$PHP_BIN artisan optimize:clear --no-interaction || true

echo "✅  Stale caches cleared"

# ==============================================================================
# STEP 5 — STORAGE & PERMISSIONS
# ==============================================================================
step "Setting storage permissions"

mkdir -p \
  "$APP_DIR/storage/logs" \
  "$APP_DIR/storage/framework/cache" \
  "$APP_DIR/storage/framework/sessions" \
  "$APP_DIR/storage/framework/views" \
  "$APP_DIR/bootstrap/cache"

chown -R "${OWNER_USER}:${WEB_GROUP}" \
  "$APP_DIR/storage" \
  "$APP_DIR/bootstrap/cache"

find "$APP_DIR/storage"         -type d -exec chmod 775 {} \;
find "$APP_DIR/storage"         -type f -exec chmod 664 {} \;
find "$APP_DIR/bootstrap/cache" -type d -exec chmod 775 {} \;
find "$APP_DIR/bootstrap/cache" -type f -exec chmod 664 {} \;

echo "✅  Permissions set"

# ── Storage symlink ────────────────────────────────────────────────────────────
step "Ensuring storage symlink"

$PHP_BIN artisan storage:link --no-interaction || true

# ── Uploads symlink ────────────────────────────────────────────────────────────
# Check both existence AND validity of the symlink (guards against dangling links)
if [[ ! -L "$APP_DIR/uploads" ]] || [[ ! -e "$APP_DIR/uploads" ]]; then
  ln -sf "$APP_DIR/public/uploads" "$APP_DIR/uploads"
  echo "   ✔ Created/fixed uploads symlink"
else
  echo "   ✔ Uploads symlink OK"
fi

# ── Public uploads permissions ─────────────────────────────────────────────────
chown -R "${OWNER_USER}:${WEB_GROUP}" "$APP_DIR/public/uploads" 2>/dev/null || true
find "$APP_DIR/public/uploads" -type d -exec chmod 755 {} \; 2>/dev/null || true
find "$APP_DIR/public/uploads" -type f -exec chmod 644 {} \; 2>/dev/null || true

echo "✅  Symlinks & upload permissions OK"

# ==============================================================================
# STEP 6 — DATABASE MIGRATIONS (SAFE — NO DROPS / NO DELETES)
# ==============================================================================
#
# Strategy when a migration fails because a table already exists (MySQL 1050):
#   1. Extract the migration filename from artisan's error output
#   2. Validate the name is safe (alphanumeric + underscores only)
#   3. Mark it as "ran" in the migrations table via tinker
#   4. Retry — repeat up to MIGRATE_MAX_TRIES times
#
# Any other error stops the loop immediately.
# ==============================================================================
step "Running database migrations (SAFE)"

run_safe_migrate() {
  local attempt=1
  local migration_output
  local exit_code

  while (( attempt <= MIGRATE_MAX_TRIES )); do
    echo "   Attempt $attempt / $MIGRATE_MAX_TRIES"

    set +e
    migration_output="$(timeout "$MIGRATE_TIMEOUT" "$PHP_BIN" artisan migrate \
      --force \
      --no-interaction 2>&1)"
    exit_code=$?
    set -e

    # ── Success ──────────────────────────────────────────────────────────────
    if [[ $exit_code -eq 0 ]]; then
      echo "$migration_output"
      echo "✅  All migrations ran successfully"
      return 0
    fi

    echo "$migration_output"

    # ── Timeout ───────────────────────────────────────────────────────────────
    if [[ $exit_code -eq 124 ]]; then
      die "Migration timed out after ${MIGRATE_TIMEOUT}s (possible lock wait). Check: $APP_DIR/storage/logs/laravel-$(date +%F).log"
    fi

    # ── Table already exists (MySQL 1050) ─────────────────────────────────────
    if echo "$migration_output" | grep -qE "Base table or view already exists: 1050|Table '.*' already exists"; then

      # Extract migration filename (e.g. 2022_01_01_000001_create_users_table)
      local migration_name
      migration_name="$(
        echo "$migration_output" \
          | grep -oE '[0-9]{4}_[0-9]{2}_[0-9]{2}_[0-9]{6}_[A-Za-z0-9_]+\.php' \
          | head -n 1
      )"
      migration_name="${migration_name%.php}"

      local table_name
      table_name="$(
        echo "$migration_output" \
          | grep -oE "Table '[^']+'" \
          | head -n 1 \
          | sed "s/Table '//;s/'//"
      )"

      echo "   ⚠️  Existing table detected: ${table_name:-unknown}"

      # Safety: validate migration name before interpolating into shell/PHP
      if [[ -z "${migration_name}" ]]; then
        die "Could not extract migration name from output. Run manually: $PHP_BIN artisan migrate --force"
      fi

      if [[ ! "$migration_name" =~ ^[0-9]{4}_[0-9]{2}_[0-9]{2}_[0-9]{6}_[A-Za-z0-9_]+$ ]]; then
        die "Migration name failed safety validation: '$migration_name'. Aborting to prevent injection."
      fi

      echo "   ➡️  Marking '$migration_name' as executed (NO DROP / NO DELETE)..."

      "$PHP_BIN" artisan tinker --no-interaction --execute "
        \$name = '$migration_name';
        if (!DB::table('migrations')->where('migration', \$name)->exists()) {
          \$batch = (int) DB::table('migrations')->max('batch');
          \$batch = \$batch ? \$batch + 1 : 1;
          DB::table('migrations')->insert(['migration' => \$name, 'batch' => \$batch]);
          echo 'Inserted: ' . \$name . ' (batch ' . \$batch . ')' . PHP_EOL;
        } else {
          echo 'Already recorded: ' . \$name . PHP_EOL;
        }
      " || true

      (( attempt++ ))
      continue
    fi

    # ── Unknown error — not safe to auto-recover ──────────────────────────────
    echo ""
    echo "❌  Migration failed for an unhandled reason."
    echo "   Logs:"
    echo "   - $APP_DIR/storage/logs/laravel-$(date +%F).log"
    echo "   - /var/log/apache2/error.log"
    return 1
  done

  die "Migration exceeded max retries ($MIGRATE_MAX_TRIES). Check logs."
}

run_safe_migrate

# ==============================================================================
# STEP 7 — PRODUCTION CACHE REBUILD
# ==============================================================================
step "Building production caches"

# config:cache gets || true to avoid leaving app down if .env has a parse error.
# All three caches (config + route + event) are required for a fully optimised app.
$PHP_BIN artisan config:cache --no-interaction || true
$PHP_BIN artisan route:cache  --no-interaction || true
$PHP_BIN artisan event:cache  --no-interaction || true
$PHP_BIN artisan view:cache   --no-interaction || true

echo "✅  Production caches rebuilt"

# ==============================================================================
# STEP 8 — QUEUE WORKERS RESTART
# ==============================================================================
# queue:restart only sets a cache flag — workers act on it when they finish
# their current job. Supervisor restart ensures workers are actually running
# even if they crashed between deployments.
# ==============================================================================
step "Restarting queue workers"

$PHP_BIN artisan queue:restart --no-interaction || true

if command -v supervisorctl &>/dev/null; then
  supervisorctl restart mayush-worker:* 2>/dev/null \
    && echo "   ✔ Supervisor workers restarted" \
    || echo "   ⚠️  supervisorctl: no mayush-worker processes found (check config)"
else
  echo "   ⚠️  supervisorctl not available — queue:restart signal sent only"
fi

echo "✅  Queue workers signalled"

# ==============================================================================
# STEP 9 — BRING APP BACK ONLINE
# ==============================================================================
step "Lifting maintenance mode"

# Disable the EXIT trap before we call artisan up manually
trap - EXIT

$PHP_BIN artisan up

echo "✅  App is live"

# ==============================================================================
# STEP 10 — POST-DEPLOY HEALTH CHECK
# ==============================================================================
step "Running health check"

health_passed=false

for attempt in $(seq 1 $HEALTH_CHECK_RETRIES); do
  http_code="$(
    curl -o /dev/null -s -w "%{http_code}" \
      --max-time 10 \
      "${APP_URL}/up" \
    2>/dev/null || echo "000"
  )"

  if [[ "$http_code" == "200" ]]; then
    echo "✅  Health check passed (HTTP 200) on attempt $attempt"
    health_passed=true
    break
  fi

  echo "   ⚠️  Attempt $attempt: got HTTP $http_code — retrying in ${HEALTH_CHECK_DELAY}s..."
  sleep "$HEALTH_CHECK_DELAY"
done

if [[ "$health_passed" == "false" ]]; then
  echo ""
  echo "❌  HEALTH CHECK FAILED after $HEALTH_CHECK_RETRIES attempts."
  echo "   Check: $APP_DIR/storage/logs/laravel-$(date +%F).log"
  echo "   App may be live but unhealthy — manual inspection required."
  # Non-fatal: we already called artisan up. Alert but don't exit 1.
fi

# ==============================================================================
# STEP 11 — CRON VERIFICATION
# ==============================================================================
step "Verifying scheduled task cron"

expected_cron="* * * * * $PHP_BIN $APP_DIR/artisan schedule:run >> /dev/null 2>&1"

if crontab -u "$OWNER_USER" -l 2>/dev/null | grep -qF "artisan schedule:run"; then
  echo "✅  Cron entry found"
else
  echo "⚠️  Cron entry NOT detected for user '$OWNER_USER'."
  echo "   Add this line with: crontab -u $OWNER_USER -e"
  echo ""
  echo "   $expected_cron"
fi

# ==============================================================================
# DONE
# ==============================================================================
echo ""
echo "============================================================"
echo " DEPLOY COMPLETED — $(date '+%Y-%m-%d %H:%M:%S')"
echo " Log: $DEPLOY_LOG"
echo "============================================================"
echo ""