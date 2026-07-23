#!/usr/bin/env bash

set -Eeuo pipefail
umask 027

COMMAND="${1:-help}"
APP_DIR="${APP_DIR:-/home/mayushdesign/public_html}"
APP_USER="${APP_USER:-mayushdesign}"
PHP_BIN="${PHP_BIN:-/usr/bin/php8.2}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"
REVERB_PORT="${REVERB_PORT:-8080}"
SUPERVISOR_PROGRAM="${SUPERVISOR_PROGRAM:-mayush-reverb}"
SUPERVISOR_CONFIG="${SUPERVISOR_CONFIG:-/etc/supervisor/conf.d/mayush-reverb.conf}"
HORIZON_PROGRAM="${HORIZON_PROGRAM:-mayush-horizon}"
HORIZON_SUPERVISOR_CONFIG="${HORIZON_SUPERVISOR_CONFIG:-/etc/supervisor/conf.d/mayush-horizon.conf}"
SCHEDULER_CONFIG="${SCHEDULER_CONFIG:-/etc/cron.d/mayush-scheduler}"
BACKUP_DIR="${BACKUP_DIR:-/home/${APP_USER}/db-backups}"
BACKUP_DATABASE="${BACKUP_DATABASE:-true}"
BUILD_ASSETS="${BUILD_ASSETS:-false}"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SUPERVISOR_TEMPLATE="${SCRIPT_DIR}/supervisor-reverb.conf.template"
HORIZON_SUPERVISOR_TEMPLATE="${SCRIPT_DIR}/supervisor-horizon.conf.template"
SCHEDULER_TEMPLATE="${SCRIPT_DIR}/scheduler.cron.template"
ENV_FILE="${APP_DIR}/.env"

log() {
    printf '[notifications-v2] %s\n' "$*"
}

fail() {
    printf '[notifications-v2] ERROR: %s\n' "$*" >&2
    exit 1
}

require_command() {
    command -v "$1" >/dev/null 2>&1 || fail "Required command is missing: $1"
}

env_value() {
    local key="$1"
    local line=""
    local value=""

    line="$(grep -E "^${key}=" "$ENV_FILE" | tail -n 1 || true)"
    value="${line#*=}"
    value="${value%$'\r'}"

    if [[ ${#value} -ge 2 ]]; then
        if [[ "${value:0:1}" == '"' && "${value: -1}" == '"' ]]; then
            value="${value:1:${#value}-2}"
        elif [[ "${value:0:1}" == "'" && "${value: -1}" == "'" ]]; then
            value="${value:1:${#value}-2}"
        fi
    fi

    printf '%s' "$value"
}

is_true() {
    case "${1,,}" in
        1|true|yes|on) return 0 ;;
        *) return 1 ;;
    esac
}

require_env() {
    local key="$1"
    local value
    value="$(env_value "$key")"
    [[ -n "$value" && "$value" != "CHANGE_ME" ]] || fail "${key} must be configured in ${ENV_FILE}"
}

run_app() {
    (
        cd "$APP_DIR"

        if [[ "$(id -un)" == "$APP_USER" ]]; then
            "$@"
        elif [[ "$EUID" -eq 0 ]]; then
            runuser -u "$APP_USER" -- "$@"
        elif command -v sudo >/dev/null 2>&1 && sudo -n -u "$APP_USER" true 2>/dev/null; then
            sudo -n -u "$APP_USER" -- "$@"
        else
            fail "Run as ${APP_USER}, root, or a user allowed to sudo as ${APP_USER}"
        fi
    )
}

validate_php() {
    [[ -x "$PHP_BIN" ]] || fail "PHP binary is not executable: ${PHP_BIN}"

    "$PHP_BIN" -r 'exit(version_compare(PHP_VERSION, "8.2.0", ">=") ? 0 : 1);' \
        || fail "PHP 8.2 or newer is required"

    local required_extensions=(bcmath curl dom fileinfo gd intl mbstring openssl pcntl pdo_mysql posix redis xml zip)
    local missing=()
    local extension

    for extension in "${required_extensions[@]}"; do
        "$PHP_BIN" -m | grep -Fxqi "$extension" || missing+=("$extension")
    done

    [[ ${#missing[@]} -eq 0 ]] \
        || fail "Missing PHP extensions: ${missing[*]}"
}

validate_environment() {
    [[ -d "$APP_DIR" ]] || fail "Application directory does not exist: ${APP_DIR}"
    [[ -f "$ENV_FILE" ]] || fail "Production environment file is missing: ${ENV_FILE}"
    [[ -f "${APP_DIR}/artisan" ]] || fail "Laravel artisan was not found in ${APP_DIR}"
    [[ -f "${APP_DIR}/composer.lock" ]] || fail "composer.lock was not found in ${APP_DIR}"
    id "$APP_USER" >/dev/null 2>&1 || fail "Application user does not exist: ${APP_USER}"

    require_command "$COMPOSER_BIN"
    validate_php

    [[ "$(env_value APP_ENV)" == "production" ]] || fail "APP_ENV must be production"
    [[ "$(env_value APP_DEBUG)" == "false" ]] || fail "APP_DEBUG must be false"
    [[ "$(env_value APP_URL)" == https://* ]] || fail "APP_URL must use HTTPS"
    [[ "$(env_value QUEUE_CONNECTION)" == "redis" ]] || fail "QUEUE_CONNECTION must be redis"

    require_env DB_CONNECTION
    require_env DB_DATABASE
    require_env DB_USERNAME
    require_env REDIS_HOST
    require_env NOTIFICATION_DELIVERY_WEBHOOK_SECRET

    local origins
    origins="$(env_value REVERB_ALLOWED_ORIGINS)"
    if [[ "$origins" == *"*"* ]]; then
        fail "REVERB_ALLOWED_ORIGINS must not contain a wildcard"
    fi

    if is_true "$(env_value NOTIFICATION_BROADCASTING_ENABLED)"; then
        [[ "$(env_value BROADCAST_DRIVER)" == "reverb" ]] \
            || fail "BROADCAST_DRIVER must be reverb when broadcasting is enabled"
        require_env REVERB_APP_ID
        require_env REVERB_APP_KEY
        require_env REVERB_APP_SECRET
        require_env REVERB_HOST
        require_env REVERB_ALLOWED_ORIGINS
    fi

    if is_true "$(env_value FCM_V1_ENABLED)"; then
        require_env FCM_PROJECT_ID
        require_env FCM_SERVICE_ACCOUNT_PATH

        local credentials
        credentials="$(env_value FCM_SERVICE_ACCOUNT_PATH)"
        [[ -f "$credentials" ]] || fail "FCM service-account file does not exist: ${credentials}"
        run_app test -r "$credentials" \
            || fail "FCM service-account file is not readable by ${APP_USER}"
    fi

    if is_true "$(env_value NOTIFICATION_SMS_ENABLED)"; then
        log "SMS is enabled. Confirm the selected Mayush SMS provider and credentials in the admin settings."
    fi

    if [[ "$BUILD_ASSETS" == "true" ]]; then
        require_command npm
    elif ! grep -q '"notifications"' "${APP_DIR}/public/build/storefront/manifest.json" 2>/dev/null; then
        fail "The committed storefront manifest does not contain the notification client"
    fi

    log "Environment and runtime validation passed."
}

backup_database() {
    [[ "$BACKUP_DATABASE" == "true" ]] || {
        log "Database backup skipped because BACKUP_DATABASE=${BACKUP_DATABASE}."
        return
    }

    [[ "$(env_value DB_CONNECTION)" == "mysql" ]] \
        || fail "Automatic backup currently supports DB_CONNECTION=mysql only"
    require_command mysqldump

    local db_host db_port db_name db_user db_password backup_file
    db_host="$(env_value DB_HOST)"
    db_port="$(env_value DB_PORT)"
    db_name="$(env_value DB_DATABASE)"
    db_user="$(env_value DB_USERNAME)"
    db_password="$(env_value DB_PASSWORD)"
    db_host="${db_host:-127.0.0.1}"
    db_port="${db_port:-3306}"

    install -d -m 0750 "$BACKUP_DIR"
    backup_file="${BACKUP_DIR}/${db_name}-before-notifications-v2-$(date -u +'%Y%m%d-%H%M%S').sql"

    log "Creating database backup at ${backup_file}."
    MYSQL_PWD="$db_password" mysqldump \
        --single-transaction \
        --quick \
        --routines \
        --triggers \
        -h "$db_host" \
        -P "$db_port" \
        -u "$db_user" \
        "$db_name" > "$backup_file"
    unset db_password

    [[ -s "$backup_file" ]] || fail "Database backup is empty"
    chmod 0640 "$backup_file"
}

install_system_packages() {
    [[ "$EUID" -eq 0 ]] || fail "install-system must be run as root"
    require_command apt-get

    log "Installing Ubuntu/Debian runtime packages."
    DEBIAN_FRONTEND=noninteractive apt-get update
    DEBIAN_FRONTEND=noninteractive apt-get install -y \
        composer \
        cron \
        redis-tools \
        supervisor \
        php8.2-cli \
        php8.2-bcmath \
        php8.2-curl \
        php8.2-gd \
        php8.2-intl \
        php8.2-mbstring \
        php8.2-mysql \
        php8.2-redis \
        php8.2-xml \
        php8.2-zip

    systemctl enable --now supervisor
    systemctl enable --now cron
    log "System packages installed. Redis server may be local or managed separately."
}

install_scheduler_config() {
    local existing_cron=""
    existing_cron="$(crontab -u "$APP_USER" -l 2>/dev/null || true)"

    if grep -Fq "${APP_DIR}/artisan schedule:run" <<< "$existing_cron" \
        || grep -RqsF "${APP_DIR}/artisan schedule:run" /etc/cron.d /etc/crontab 2>/dev/null; then
        log "An existing Laravel scheduler entry was found; no duplicate was installed."
        return
    fi

    [[ -f "$SCHEDULER_TEMPLATE" ]] || fail "Scheduler template is missing"

    local escaped_app escaped_php escaped_user temp_file
    escaped_app="${APP_DIR//\//\\/}"
    escaped_php="${PHP_BIN//\//\\/}"
    escaped_user="${APP_USER//\//\\/}"
    temp_file="$(mktemp)"

    sed \
        -e "s/__APP_DIR__/${escaped_app}/g" \
        -e "s/__PHP_BIN__/${escaped_php}/g" \
        -e "s/__APP_USER__/${escaped_user}/g" \
        "$SCHEDULER_TEMPLATE" > "$temp_file"

    install -m 0644 "$temp_file" "$SCHEDULER_CONFIG"
    rm -f "$temp_file"
    log "Laravel scheduler installed at ${SCHEDULER_CONFIG}."
}

install_supervisor_config() {
    [[ "$EUID" -eq 0 ]] || fail "install-supervisor must be run as root"
    [[ -f "$SUPERVISOR_TEMPLATE" ]] || fail "Supervisor template is missing"
    [[ -f "$HORIZON_SUPERVISOR_TEMPLATE" ]] || fail "Horizon Supervisor template is missing"
    id "$APP_USER" >/dev/null 2>&1 || fail "Application user does not exist: ${APP_USER}"
    require_command supervisorctl
    validate_environment
    require_env REVERB_APP_ID
    require_env REVERB_APP_KEY
    require_env REVERB_APP_SECRET
    require_env REVERB_HOST
    require_env REVERB_ALLOWED_ORIGINS

    [[ "$APP_DIR" != *$'\n'* && "$PHP_BIN" != *$'\n'* && "$APP_USER" != *$'\n'* \
        && "$REVERB_PORT" != *$'\n'* ]] \
        || fail "Supervisor variables contain invalid newlines"
    [[ "$APP_DIR" =~ ^/[A-Za-z0-9._/-]+$ ]] || fail "APP_DIR contains unsupported characters"
    [[ "$PHP_BIN" =~ ^/[A-Za-z0-9._/-]+$ ]] || fail "PHP_BIN contains unsupported characters"
    [[ "$APP_USER" =~ ^[A-Za-z_][A-Za-z0-9_-]*$ ]] || fail "APP_USER is invalid"
    [[ "$REVERB_PORT" =~ ^[0-9]+$ ]] || fail "REVERB_PORT must be numeric"

    local escaped_app escaped_php escaped_user escaped_port temp_file horizon_temp_file
    escaped_app="${APP_DIR//\//\\/}"
    escaped_php="${PHP_BIN//\//\\/}"
    escaped_user="${APP_USER//\//\\/}"
    escaped_port="${REVERB_PORT//\//\\/}"
    temp_file="$(mktemp)"
    horizon_temp_file="$(mktemp)"

    sed \
        -e "s/__APP_DIR__/${escaped_app}/g" \
        -e "s/__PHP_BIN__/${escaped_php}/g" \
        -e "s/__APP_USER__/${escaped_user}/g" \
        -e "s/__REVERB_PORT__/${escaped_port}/g" \
        "$SUPERVISOR_TEMPLATE" > "$temp_file"

    install -m 0644 "$temp_file" "$SUPERVISOR_CONFIG"

    if grep -RqsE 'artisan[[:space:]]+horizon([[:space:]]|$)' /etc/supervisor/conf.d; then
        log "An existing Horizon Supervisor configuration was found; no duplicate was installed."
    else
        sed \
            -e "s/__APP_DIR__/${escaped_app}/g" \
            -e "s/__PHP_BIN__/${escaped_php}/g" \
            -e "s/__APP_USER__/${escaped_user}/g" \
            "$HORIZON_SUPERVISOR_TEMPLATE" > "$horizon_temp_file"
        install -m 0644 "$horizon_temp_file" "$HORIZON_SUPERVISOR_CONFIG"
    fi

    supervisorctl reread
    supervisorctl update
    supervisorctl restart "$SUPERVISOR_PROGRAM"

    if supervisorctl status "$HORIZON_PROGRAM" >/dev/null 2>&1; then
        supervisorctl restart "$HORIZON_PROGRAM"
    fi

    rm -f "$temp_file" "$horizon_temp_file"
    install_scheduler_config
    log "Reverb Supervisor configuration installed. Horizon is supervised without creating duplicate workers."
}

deploy_application() {
    validate_environment
    backup_database

    log "Installing locked production PHP dependencies."
    run_app "$COMPOSER_BIN" install \
        --no-dev \
        --prefer-dist \
        --optimize-autoloader \
        --no-interaction \
        --no-progress

    run_app "$PHP_BIN" artisan optimize:clear

    if [[ "$BUILD_ASSETS" == "true" ]]; then
        log "Building storefront assets."
        run_app npm ci
        run_app npm run build:storefront
    fi

    log "Running additive database migrations."
    run_app "$PHP_BIN" artisan migrate --force

    log "Building Laravel production caches."
    run_app "$PHP_BIN" artisan config:cache
    run_app "$PHP_BIN" artisan route:cache
    run_app "$PHP_BIN" artisan view:cache

    run_app "$PHP_BIN" artisan queue:restart
    run_app "$PHP_BIN" artisan horizon:terminate
    run_app "$PHP_BIN" artisan reverb:restart || true

    if command -v supervisorctl >/dev/null 2>&1 \
        && supervisorctl status "$SUPERVISOR_PROGRAM" >/dev/null 2>&1; then
        supervisorctl restart "$SUPERVISOR_PROGRAM"
    else
        log "Reverb Supervisor program is not installed yet. Run install-supervisor as root."
    fi

    run_app "$PHP_BIN" artisan notifications:prune-inbox --dry-run
    verify_application
}

verify_application() {
    validate_environment

    run_app "$PHP_BIN" artisan migrate:status --no-ansi \
        | grep -q '2026_07_23_000001_create_notification_center_v2_tables' \
        || fail "Notification Center v2 migration is not registered"

    run_app "$PHP_BIN" artisan route:list --path=notifications --except-vendor --no-ansi \
        | grep -q 'notifications/summary' \
        || fail "Notification Center routes are unavailable"

    local horizon_status
    horizon_status="$(run_app "$PHP_BIN" artisan horizon:status --no-ansi 2>&1 || true)"
    printf '%s\n' "$horizon_status"
    grep -qi 'running' <<< "$horizon_status" || fail "Horizon is not running"

    if is_true "$(env_value NOTIFICATION_BROADCASTING_ENABLED)"; then
        command -v ss >/dev/null 2>&1 \
            || fail "The ss command is required to verify the private Reverb port"
        ss -ltn | grep -Eq "127\\.0\\.0\\.1:${REVERB_PORT}[[:space:]]" \
            || fail "Reverb is not listening on 127.0.0.1:${REVERB_PORT}"
    fi

    local scheduler_entries
    scheduler_entries="$(crontab -u "$APP_USER" -l 2>/dev/null || true)"
    if ! grep -Fq "${APP_DIR}/artisan schedule:run" <<< "$scheduler_entries" \
        && ! grep -RqsF "${APP_DIR}/artisan schedule:run" /etc/cron.d /etc/crontab 2>/dev/null; then
        fail "Laravel scheduler is not installed for ${APP_DIR}"
    fi

    log "Application verification passed."
    log "A signed-in browser can now perform the WSS/private-channel and canary-event checks."
}

usage() {
    cat <<'USAGE'
Notification Center v2 production setup

Usage:
  sudo APP_DIR=/home/mayushdesign/public_html APP_USER=mayushdesign \
    bash deploy/notifications-v2/production-setup.sh install-system

  sudo APP_DIR=/home/mayushdesign/public_html APP_USER=mayushdesign \
    bash deploy/notifications-v2/production-setup.sh install-supervisor

  APP_DIR=/home/mayushdesign/public_html APP_USER=mayushdesign \
    bash deploy/notifications-v2/production-setup.sh check

  APP_DIR=/home/mayushdesign/public_html APP_USER=mayushdesign \
    bash deploy/notifications-v2/production-setup.sh deploy

  APP_DIR=/home/mayushdesign/public_html APP_USER=mayushdesign \
    bash deploy/notifications-v2/production-setup.sh verify

Optional variables:
  PHP_BIN=/usr/bin/php8.2
  COMPOSER_BIN=composer
  REVERB_PORT=8080
  HORIZON_PROGRAM=mayush-horizon
  BACKUP_DATABASE=true
  BACKUP_DIR=/home/mayushdesign/db-backups
  BUILD_ASSETS=false
USAGE
}

case "$COMMAND" in
    install-system) install_system_packages ;;
    install-supervisor) install_supervisor_config ;;
    check) validate_environment ;;
    deploy) deploy_application ;;
    verify) verify_application ;;
    help|-h|--help) usage ;;
    *) usage; fail "Unknown command: ${COMMAND}" ;;
esac
