#!/usr/bin/env bash
set -euo pipefail

# ------------------------------------------------------------
# MAYUSHDESIGN - SAFE DEPLOY SCRIPT (NO TABLE DROPS / NO DELETES)
# - Updates .env keys safely
# - Fixes storage/cache permissions
# - Runs migrations safely:
#     * If a migration fails because a table already exists,
#       it WILL NOT drop anything.
#     * It will mark that migration as "ran" in migrations table
#       then retry.
# - Rebuilds caches
# - Restarts queue worker
# ------------------------------------------------------------

# --- CONFIGURATION ---
APP_DIR="/home/mayushdesign/public_html"
ENV_FILE="$APP_DIR/.env"
PHP_BIN="/usr/bin/php8.2"

OWNER_USER="mayushdesign"
WEB_GROUP="www-data"

# Desired ENV values
APP_ENV_VALUE="production"
APP_URL_VALUE="https://mayushdesign.com"
ASSET_URL_VALUE="https://mayushdesign.com"
DB_USERNAME_VALUE="pma"
DB_PASSWORD_VALUE="yQnhTl2qbOqb1DqAfnBtF9gt"
QUEUE_CONNECTION_VALUE="sync"
APP_DEBUG_VALUE="false"
FORCE_HTTPS_VALUE="On"
SESSION_SECURE_COOKIE_VALUE="true"

# Optional deploy log (uncomment if you want)
# exec > >(tee -a /home/mayushdesign/deploy.log) 2>&1

cd "$APP_DIR"

if [[ ! -f "$ENV_FILE" ]]; then
  echo "❌ .env not found at $ENV_FILE"
  exit 1
fi

# Update or add KEY=VALUE in .env (keeps file structure)
set_env () {
  local key="$1"
  local value="$2"
  local esc_value
  # escape sed replacement chars
  esc_value="$(printf '%s' "$value" | sed 's/[\/&]/\\&/g')"

  if grep -qE "^${key}=" "$ENV_FILE"; then
    sed -i "s|^${key}=.*|${key}=${esc_value}|" "$ENV_FILE"
  else
    printf "\n%s=%s\n" "$key" "$value" >> "$ENV_FILE"
  fi
}

echo "⚙️  Updating .env configurations..."
set_env "APP_ENV" "$APP_ENV_VALUE"
set_env "APP_URL" "$APP_URL_VALUE"
set_env "ASSET_URL" "$ASSET_URL_VALUE"
set_env "DB_USERNAME" "$DB_USERNAME_VALUE"
set_env "DB_PASSWORD" "$DB_PASSWORD_VALUE"
set_env "QUEUE_CONNECTION" "$QUEUE_CONNECTION_VALUE"
set_env "APP_DEBUG" "$APP_DEBUG_VALUE"
set_env "FORCE_HTTPS" "$FORCE_HTTPS_VALUE"
set_env "SESSION_SECURE_COOKIE" "$SESSION_SECURE_COOKIE_VALUE"

echo "🔄 Clearing caches to load new .env..."
$PHP_BIN artisan optimize:clear --no-interaction || true

# Permissions
echo "🔐 Setting permissions..."
mkdir -p \
  storage/logs \
  storage/framework/cache \
  storage/framework/sessions \
  storage/framework/views \
  bootstrap/cache

chown -R "${OWNER_USER}:${WEB_GROUP}" storage bootstrap/cache
find storage -type d -exec chmod 775 {} \;
find storage -type f -exec chmod 664 {} \;
find bootstrap/cache -type d -exec chmod 775 {} \;
find bootstrap/cache -type f -exec chmod 664 {} \;
chmod -R ug+rwX storage bootstrap/cache

# Ensure storage symlink exists
$PHP_BIN artisan storage:link --no-interaction || true

# Ensure uploads symlink exists in root for sites running from root
if [ ! -L uploads ]; then
    ln -s public/uploads uploads
    echo "✅ Created uploads symlink"
fi

# Ensure public uploads readable
chown -R "${OWNER_USER}:${WEB_GROUP}" public/uploads || true
find public/uploads -type d -exec chmod 755 {} \; || true
find public/uploads -type f -exec chmod 644 {} \; || true

# --- SAFE MIGRATION RUNNER (NO DROPS / NO DELETES) ---
run_safe_migrate() {
  local max_tries=25
  local attempt=1

  while (( attempt <= max_tries )); do
    echo "🗄️  Migrate attempt $attempt/$max_tries"

    set +e
    out="$($PHP_BIN artisan migrate --force --no-interaction 2>&1)"
    code=$?
    set -e

    if [[ $code -eq 0 ]]; then
      echo "$out"
      echo "✅ Migrations finished."
      return 0
    fi
    echo "$out"

    # Handle: table already exists (MySQL 1050)
    if echo "$out" | grep -qE "Base table or view already exists: 1050|Table '.*' already exists"; then

      # Extract migration name from output (best-effort)
      # Example: 2022_01_01_000001_create_categories_table.php
      mig="$(echo "$out" | grep -oE '[0-9]{4}_[0-9]{2}_[0-9]{2}_[0-9]{6}_[A-Za-z0-9_]+\.php' | head -n 1)"
      mig="${mig%.php}"

      # Extract table name (safe; no backticks)
      tbl="$(echo "$out" | grep -oE "Table '[^']+'" | head -n 1 | sed "s/Table '//" | sed "s/'//")"

      echo "⚠️  Detected existing table: ${tbl:-unknown}"
      echo "➡️  Will mark migration as executed (NO DROP / NO DELETE) and retry..."

      if [[ -z "${mig:-}" ]]; then
        echo "❌ Could not detect migration name from output."
        echo "   Run manually for full context:"
        echo "   $PHP_BIN artisan migrate --force"
        return 1
      fi

      # Insert migration row if not exists (safe)
      $PHP_BIN artisan tinker --no-interaction --execute "
        \$m = '$mig';
        if (!DB::table('migrations')->where('migration', \$m)->exists()) {
          \$batch = (int) DB::table('migrations')->max('batch');
          \$batch = \$batch ? \$batch + 1 : 1;
          DB::table('migrations')->insert(['migration' => \$m, 'batch' => \$batch]);
          echo 'Inserted migration '.\$m.' in batch '.\$batch.PHP_EOL;
        } else {
          echo 'Migration '.\$m.' already recorded'.PHP_EOL;
        }
      " || true

      ((attempt++))
      continue
    fi

    echo "❌ Migration failed for another reason (not safe to auto-fix)."
    echo "➡️  Check logs:"
    echo "   - $APP_DIR/storage/logs/laravel-$(date +%F).log"
    echo "   - /var/log/apache2/error.log"
    return 1
  done

  echo "❌ Too many retries. Aborting."
  return 1
}

echo "🗄️  Running database migrations (SAFE)..."
run_safe_migrate

# Caching and Optimization
echo "🚀 Optimizing for production..."
$PHP_BIN artisan optimize:clear --no-interaction || true
$PHP_BIN artisan config:cache --no-interaction
$PHP_BIN artisan route:cache --no-interaction || true
$PHP_BIN artisan view:cache --no-interaction || true

# Restart Queue Worker
echo "🔄 Restarting queue worker..."
$PHP_BIN artisan queue:restart --no-interaction || true

echo "✅ Deployment steps completed successfully."
echo "--------------------------------------------------"
echo "💡 REMINDER: CRON (run as user mayushdesign) should be:"
echo "* * * * * /usr/bin/php8.2 /home/mayushdesign/public_html/artisan schedule:run >> /dev/null 2>&1"
