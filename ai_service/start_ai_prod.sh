#!/bin/bash
# ============================================================
#  Mayush AI Service — Production Manager
#  Usage: ./start_ai_prod.sh {start|stop|restart|status}
# ============================================================

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="$SCRIPT_DIR"
VENV_DIR="$APP_DIR/venv"
PID_FILE="$APP_DIR/ai_service.pid"
LOG_FILE="$APP_DIR/ai_service.log"
PORT=5001

# ── Helpers ──────────────────────────────────────────────────
log() { echo "[$(date '+%Y-%m-%d %H:%M:%S')] $*" | tee -a "$LOG_FILE"; }

is_running() {
    if [[ -f "$PID_FILE" ]]; then
        local pid
        pid=$(cat "$PID_FILE")
        if kill -0 "$pid" 2>/dev/null; then
            return 0
        fi
        # Stale PID file
        rm -f "$PID_FILE"
    fi
    return 1
}

ensure_venv() {
    if [[ ! -d "$VENV_DIR" ]]; then
        log "📦 Creating Python virtual environment..."
        python3 -m venv "$VENV_DIR"
    fi
}

install_deps() {
    local hash_file="$APP_DIR/.requirements.hash"
    local current_hash
    current_hash=$(sha256sum "$APP_DIR/requirements.txt" | awk '{print $1}')

    if [[ -f "$hash_file" ]] && [[ "$(cat "$hash_file")" == "$current_hash" ]]; then
        log "✔ Dependencies unchanged — skipping pip install"
        return 0
    fi

    log "📥 Installing Python dependencies..."
    source "$VENV_DIR/bin/activate"
    local attempt=0
    local max_attempts=3
    while (( attempt < max_attempts )); do
        ((attempt++))
        if pip install -r "$APP_DIR/requirements.txt" --quiet 2>>"$LOG_FILE"; then
            echo "$current_hash" > "$hash_file"
            log "✔ Dependencies installed (attempt $attempt)"
            deactivate
            return 0
        fi
        log "⚠ pip install failed (attempt $attempt/$max_attempts) — retrying in $((attempt * 5))s..."
        sleep $((attempt * 5))
    done

    log "❌ pip install failed after $max_attempts attempts"
    deactivate
    return 1
}

health_check() {
    local attempt=0
    local max_attempts=3
    while (( attempt < max_attempts )); do
        ((attempt++))
        sleep $((attempt * 5))
        if curl -sf "http://127.0.0.1:$PORT/" >/dev/null 2>&1; then
            log "✔ Health check passed (attempt $attempt)"
            return 0
        fi
        log "⏳ Health check attempt $attempt/$max_attempts — service not yet responding..."
    done
    log "❌ Health check failed after $max_attempts attempts"
    return 1
}

# ── Commands ─────────────────────────────────────────────────
do_start() {
    if is_running; then
        local pid
        pid=$(cat "$PID_FILE")
        log "⚡ AI Service already running (PID $pid)"
        return 0
    fi

    ensure_venv
    install_deps

    log "🚀 Starting AI Service on port $PORT..."
    source "$VENV_DIR/bin/activate"
    nohup python "$APP_DIR/app.py" >> "$LOG_FILE" 2>&1 &
    local new_pid=$!
    echo "$new_pid" > "$PID_FILE"
    deactivate

    log "🔍 Waiting for service to become healthy..."
    if health_check; then
        log "✅ AI Service started successfully (PID $new_pid, port $PORT)"
        return 0
    else
        log "❌ AI Service failed to start — check $LOG_FILE"
        # Kill the broken process
        kill "$new_pid" 2>/dev/null || true
        rm -f "$PID_FILE"
        return 1
    fi
}

do_stop() {
    if ! is_running; then
        log "ℹ AI Service is not running"
        return 0
    fi

    local pid
    pid=$(cat "$PID_FILE")
    log "🛑 Stopping AI Service (PID $pid)..."
    kill "$pid" 2>/dev/null
    sleep 2
    # Force kill if still alive
    if kill -0 "$pid" 2>/dev/null; then
        kill -9 "$pid" 2>/dev/null
        log "⚠ Force-killed PID $pid"
    fi
    rm -f "$PID_FILE"
    log "✔ AI Service stopped"
}

do_restart() {
    log "🔄 Restarting AI Service..."
    do_stop
    do_start
}

do_status() {
    if is_running; then
        local pid
        pid=$(cat "$PID_FILE")
        log "✅ AI Service is running (PID $pid, port $PORT)"
    else
        log "⛔ AI Service is NOT running"
        return 1
    fi
}

# ── Entry Point ──────────────────────────────────────────────
case "${1:-start}" in
    start)   do_start   ;;
    stop)    do_stop    ;;
    restart) do_restart ;;
    status)  do_status  ;;
    *)
        echo "Usage: $0 {start|stop|restart|status}"
        exit 1
        ;;
esac
