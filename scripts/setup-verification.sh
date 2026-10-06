#!/usr/bin/env bash
# Shared checks; callers enable strict mode. No secrets or command arguments in errors.
fail() { printf '[FAIL] %s\n' "$*" >&2; return 1; }

run() {
    local label=$1 code
    shift
    if "$@"; then return 0; else
        code=$?
        printf '[FAIL] %s (exit %s).\n' "$label" "$code" >&2
        return "$code"
    fi
}

assert_fresh_checkout() {
    local root=$1 item
    for item in .env vendor node_modules public/build public/hot bootstrap/cache/config.php; do
        if [[ -e "$root/$item" || -L "$root/$item" ]]; then
            fail "Bootstrap requires a clean checkout: $item exists. Use verify; nothing was changed."
            return 1
        fi
    done
}

assert_single_id() {
    [[ -n "$2" && "$2" != *$'\n'* ]] || fail "Service $1 is missing or duplicated."
}

assert_service_state() {
    [[ "$2" == 'running|healthy' ]] || fail "Service $1 is not running and healthy."
}

normalize_host_path() {
    local path=${1//$'\r'/}
    path=${path//\\//}
    # Git Bash uses /r/... but Docker Desktop labels use R:/... .
    if command -v cygpath >/dev/null 2>&1; then
        path=$(cygpath -am "$path") || return
        path=$(printf '%s' "$path" | tr '[:upper:]' '[:lower:]')
    elif command -v wslpath >/dev/null 2>&1 && [[ "$path" =~ ^[A-Za-z]:/ ]]; then
        path=$(wslpath -u "$path") || return
    fi
    printf '%s' "${path%/}"
}

assert_checkout_owner() {
    local expected actual
    [[ -n "$2" ]] || { fail 'Stack checkout label is missing.'; return 1; }
    expected=$(normalize_host_path "$1") || return
    actual=$(normalize_host_path "$2") || return
    [[ "$expected" == "$actual" ]] || fail 'Running stack belongs to another checkout.'
}
