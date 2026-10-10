#!/usr/bin/env bash
# Disposable synthetic fixture and mocked CLIs. No real Git/Docker/data mutations.
set -Eeuo pipefail
script_dir=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)
temp_root=$(cd -- "${TMPDIR:-/tmp}" && pwd -P)
fixture=$(mktemp -d "$temp_root/scool-sync-test.XXXXXX")
cleanup() {
    local resolved
    resolved=$(cd -- "$fixture" && pwd -P) || return
    if [[ "$resolved" == "$temp_root"/scool-sync-test.* && "$resolved" != "$temp_root" && ! -L "$fixture" ]]; then
        rm -rf -- "$resolved"
    fi
}
trap cleanup EXIT
export MOCK_ROOT="$fixture/repo" TRACE="$fixture/trace"
mkdir -p -- "$MOCK_ROOT/scripts" "$MOCK_ROOT/.git" "$MOCK_ROOT/public" "$fixture/bin"
cp -- "$script_dir/sync-local.sh" "$script_dir/setup-verification.sh" "$MOCK_ROOT/scripts/"
cat > "$fixture/bin/git" <<'MOCK'
#!/usr/bin/env bash
set -eu
printf 'git %s\n' "$*" >> "$TRACE"
case "$1" in
    rev-parse)
        case "$2" in
            --show-toplevel) printf '%s\n' "$MOCK_ROOT" ;;
            --git-path) printf '%s/.git/%s\n' "$MOCK_ROOT" "$3" ;;
            HEAD) cat "$MOCK_ROOT/.git/head" ;;
            origin/main) printf 'after-sha\n' ;;
            *) exit 8 ;;
        esac ;;
    symbolic-ref)
        [[ "${MOCK_BRANCH:-main}" != detached ]] || exit 1
        if [[ -e "$MOCK_ROOT/.git/branch-changed" ]]; then printf 'feature\n'; else printf '%s\n' "${MOCK_BRANCH:-main}"; fi ;;
    status) if [[ "${MOCK_DIRTY:-false}" != false || -e "$MOCK_ROOT/.git/dirty" ]]; then printf ' M synthetic.txt\n'; fi ;;
    check-ignore) [[ "${MOCK_ENV_IGNORE:-true}" == true ]] ;;
    ls-files) [[ "${MOCK_TRACKED_ENV:-false}" != true ]] || printf '.env\n' ;;
    ls-tree) [[ "${MOCK_REMOTE_ENV:-false}" != true ]] || printf '.env\n' ;;
    hash-object) cksum "$MOCK_ROOT/.env" | awk '{print $1}' ;;
    rev-list) printf '%s\n' "${MOCK_AHEAD:-0}" ;;
    fetch) [[ "${MOCK_FAIL:-}" != fetch ]] || exit 7 ;;
    merge)
        [[ "$2" == --ff-only ]] || exit 8
        [[ "${MOCK_FAIL:-}" != merge ]] || exit 7
        printf 'after-sha\n' > "$MOCK_ROOT/.git/head"
        if [[ "${MOCK_MUTATE_ENV:-}" == merge ]]; then printf 'CHANGED=true\n' >> "$MOCK_ROOT/.env"; fi ;;
    *) exit 8 ;;
esac
MOCK
cat > "$fixture/bin/docker" <<'MOCK'
#!/usr/bin/env bash
set -eu
printf 'docker %s\n' "$*" >> "$TRACE"
args="$*"
mock_container_id() {
    local prefix
    case "$1" in
        app) prefix=111111111111 ;;
        nginx) prefix=222222222222 ;;
        mysql) prefix=333333333333 ;;
        mailpit) prefix=444444444444 ;;
        queue) prefix=555555555555 ;;
        *) exit 8 ;;
    esac
    printf '%s%052d' "$prefix" "${2:-0}"
}
case "$1" in
    info) [[ "${MOCK_FAIL:-}" != engine ]] || exit 7; printf 'mock-engine\n' ;;
    volume) [[ "${MOCK_FAIL:-}" != volume ]] || exit 7; printf 'scool_scool_mysql_data\n' ;;
    inspect)
        if [[ "${MOCK_FOREIGN_OWNER:-false}" == true ]]; then printf '%s/other\n' "$MOCK_ROOT"; else printf '%s\n' "$MOCK_ROOT"; fi ;;
    ps)
        [[ "${MOCK_FAIL:-}" != name-inventory ]] || exit 7
        [[ "${MOCK_STACK:-up}" != down ]] || exit 0
        last="${!#}"
        service="${last#name=^scool_}"; service="${service%\$}"
        suffix=0
        # A foreign container deliberately shares the first 12 characters.
        [[ "${MOCK_NAME_CONFLICT:-false}" != true ]] || suffix=1
        id=$(mock_container_id "$service" "$suffix")
        if [[ " $args " == *' --no-trunc '* ]]; then printf '%s\n' "$id"; else printf '%s\n' "${id:0:12}"; fi ;;
    compose)
        case "$args" in
            *' config --quiet') [[ "${MOCK_FAIL:-}" != compose ]] || exit 7 ;;
            *' ps --all -q '*)
                [[ "${MOCK_FAIL:-}" != service-inventory ]] || exit 7
                [[ "${MOCK_STACK:-up}" != down ]] || exit 0
                service="${!#}"
                [[ "${MOCK_MISSING_SERVICE:-}" != "$service" ]] || exit 0
                mock_container_id "$service"; printf '\n'
                if [[ "${MOCK_DUPLICATE_SERVICE:-}" == "$service" ]]; then mock_container_id "$service" 1; printf '\n'; fi ;;
            *' stop '*) [[ "${MOCK_FAIL:-}" != stop ]] || exit 7 ;;
            *' build app queue') [[ "${MOCK_FAIL:-}" != build ]] || exit 7 ;;
            *' up -d --wait --wait-timeout 120 mysql') [[ "${MOCK_FAIL:-}" != mysql ]] || exit 7 ;;
            *' up -d --wait --wait-timeout 120') [[ "${MOCK_FAIL:-}" != health ]] || exit 7 ;;
            *' composer install '*) [[ "${MOCK_FAIL:-}" != composer ]] || exit 7 ;;
            *' php artisan config:clear') [[ "${MOCK_FAIL:-}" != cache ]] || exit 7 ;;
            *' php scripts/verify-runtime.php config') [[ "${MOCK_FAIL:-}" != database ]] || exit 7 ;;
            *' php -r '*) [[ "${MOCK_FAIL:-}" != origin ]] || exit 7 ;;
            *' php artisan migrate --no-interaction') [[ "${MOCK_FAIL:-}" != migrate ]] || exit 7 ;;
            *) exit 8 ;;
        esac ;;
    *) exit 8 ;;
esac
MOCK
cat > "$fixture/bin/npm" <<'MOCK'
#!/usr/bin/env bash
set -eu
printf 'npm %s\n' "$*" >> "$TRACE"
[[ "$*" == ci ]] || exit 8
[[ "${MOCK_FAIL:-}" != npm ]] || exit 7
if [[ "${MOCK_MUTATE_ENV:-}" == npm ]]; then printf 'CHANGED=true\n' >> "$MOCK_ROOT/.env"; fi
case "${MOCK_LATE_CHANGE:-}" in
    dirty) printf changed > "$MOCK_ROOT/.git/dirty" ;;
    branch) printf changed > "$MOCK_ROOT/.git/branch-changed" ;;
    revision) printf 'unexpected-sha\n' > "$MOCK_ROOT/.git/head" ;;
esac
MOCK
for tool in node curl; do
    printf '#!/usr/bin/env bash\nexit 0\n' > "$fixture/bin/$tool"
done
cat > "$MOCK_ROOT/scripts/verify-fresh-setup.sh" <<'MOCK'
#!/usr/bin/env bash
set -eu
printf 'verify %s\n' "$*" >> "$TRACE"
[[ "$*" == '--mode verify' ]] || exit 8
[[ "${MOCK_FAIL:-}" != verify ]] || exit 7
printf '[PASS] mocked Verify\n'
MOCK
chmod +x "$fixture/bin/"*
export PATH="$fixture/bin:$PATH"
passed=0
reset_case() {
    : > "$TRACE"
    printf 'APP_ENV=local\nAPP_KEY=synthetic-fixture-only\nAPP_URL=http://127.0.0.1:8080\n' > "$MOCK_ROOT/.env"
    printf 'before-sha\n' > "$MOCK_ROOT/.git/head"
    rm -f -- "$MOCK_ROOT/public/hot" "$MOCK_ROOT/.git/MERGE_HEAD" "$MOCK_ROOT/.git/dirty" "$MOCK_ROOT/.git/branch-changed"
    rmdir -- "$MOCK_ROOT/.git/scool-sync.lock" 2>/dev/null || :
}
reject() {
    local label=$1 forbidden=$2 code=0
    shift 2
    env "$@" "$BASH" "$MOCK_ROOT/scripts/sync-local.sh" > "$fixture/output" 2>&1 || code=$?
    [[ "$code" != 0 ]] || { printf '[FAIL] %s unexpectedly passed.\n' "$label"; exit 1; }
    [[ -z "$forbidden" || "$(< "$TRACE")" != *"$forbidden"* ]] || { printf '[FAIL] %s reached a forbidden later step.\n' "$label"; exit 1; }
    passed=$((passed+1))
}
reject_case() { reset_case; reject "$@"; }
# Reproduce real Docker formatting before other rejection cases can mask it.
reset_case
if ! "$BASH" "$MOCK_ROOT/scripts/sync-local.sh" > "$fixture/output" 2>&1; then
    printf '[FAIL] Owned stack with full Compose IDs and default short Docker IDs was rejected.\n'
    printf '%s\n' "$(< "$fixture/output")"
    exit 1
fi
[[ "$(< "$fixture/output")" == *'[PASS] Local sync complete; HEAD=after-sha;'* ]] || exit 1
for service in app nginx mysql mailpit queue; do
    grep -Fq -- "docker ps -aq --no-trunc --filter name=^scool_${service}\$" "$TRACE" || { printf '[FAIL] Full container identity was not requested for %s.\n' "$service"; exit 1; }
done
passed=$((passed+1))
reject_case 'feature branch' 'docker ' MOCK_BRANCH=feature
reject_case 'detached HEAD' 'docker ' MOCK_BRANCH=detached
reject_case 'dirty worktree' 'docker ' MOCK_DIRTY=true
reject_case 'unignored environment' 'docker ' MOCK_ENV_IGNORE=false
reject_case 'tracked environment' 'docker ' MOCK_TRACKED_ENV=true
reset_case; rm -f -- "$MOCK_ROOT/.env"; reject 'missing environment' 'docker '
reset_case; printf stale > "$MOCK_ROOT/public/hot"; reject 'Vite hot file' 'docker '
reset_case; printf pending > "$MOCK_ROOT/.git/MERGE_HEAD"; reject 'merge in progress' 'docker '
reset_case; mkdir -- "$MOCK_ROOT/.git/scool-sync.lock"; reject 'existing lock' 'docker '
[[ -d "$MOCK_ROOT/.git/scool-sync.lock" ]] || { printf '[FAIL] Another process lock was removed.\n'; exit 1; }
reject_case 'missing engine' 'git fetch' MOCK_FAIL=engine
reject_case 'invalid Compose' 'git fetch' MOCK_FAIL=compose
reject_case 'missing volume' 'git fetch' MOCK_FAIL=volume
reject_case 'foreign checkout' 'git fetch' MOCK_FOREIGN_OWNER=true
[[ "$(< "$fixture/output")" == *'Running stack belongs to another checkout.'* ]] || exit 1
reject_case 'container name collision' 'git fetch' MOCK_NAME_CONFLICT=true
[[ "$(< "$fixture/output")" == *'Container name conflict for app; nothing was stopped.'* ]] || exit 1
reject_case 'missing Compose identity with an occupied name' 'git fetch' MOCK_MISSING_SERVICE=app
reject_case 'duplicate Compose identities' 'git fetch' MOCK_DUPLICATE_SERVICE=app
reject_case 'service inventory failure' 'git fetch' MOCK_FAIL=service-inventory
reject_case 'name inventory failure' 'git fetch' MOCK_FAIL=name-inventory
reject_case 'fetch failure' ' stop ' MOCK_FAIL=fetch
reject_case 'unpublished main commits' ' stop ' MOCK_AHEAD=1
reject_case 'remote tracks environment' ' stop ' MOCK_REMOTE_ENV=true
reject_case 'worker stop failure' 'git merge' MOCK_FAIL=stop
reject_case 'fast-forward failure' ' build ' MOCK_FAIL=merge
reject_case 'image build failure' 'composer install' MOCK_FAIL=build
reject_case 'database readiness failure' 'composer install' MOCK_FAIL=mysql
reject_case 'Composer failure' 'npm ci' MOCK_FAIL=composer
reject_case 'npm failure' 'config:clear' MOCK_FAIL=npm
reject_case 'cache failure' 'verify-runtime.php' MOCK_FAIL=cache
reject_case 'unsafe database' 'artisan migrate' MOCK_FAIL=database
reject_case 'noncanonical origin' 'artisan migrate' MOCK_FAIL=origin
reject_case 'migration failure' 'verify --mode' MOCK_FAIL=migrate
reject_case 'stack readiness failure' 'verify --mode' MOCK_FAIL=health
reject_case 'Verify failure' '' MOCK_FAIL=verify
[[ "$(< "$fixture/output")" != *'[PASS] Local sync complete'* ]] || exit 1
reject_case 'private environment changed during pull' ' build ' MOCK_MUTATE_ENV=merge
reject_case 'private environment changed during install' 'artisan migrate' MOCK_MUTATE_ENV=npm
reject_case 'source edited during sync' '' MOCK_LATE_CHANGE=dirty
reject_case 'branch switched during sync' '' MOCK_LATE_CHANGE=branch
reject_case 'revision changed during sync' '' MOCK_LATE_CHANGE=revision
reset_case
code=0
"$BASH" "$MOCK_ROOT/scripts/sync-local.sh" --skip-tests > "$fixture/output" 2>&1 || code=$?
[[ "$code" == 2 && ! -s "$TRACE" ]] || { printf '[FAIL] Unknown option did not fail before work.\n'; exit 1; }
passed=$((passed+1))
for stack in up down; do
    reset_case
    before=$(cksum "$MOCK_ROOT/.env")
    env MOCK_STACK="$stack" "$BASH" "$MOCK_ROOT/scripts/sync-local.sh" > "$fixture/output" 2>&1
    [[ "$(< "$fixture/output")" == *'[PASS] Local sync complete; HEAD=after-sha;'* ]] || exit 1
    [[ "$(cksum "$MOCK_ROOT/.env")" == "$before" && ! -d "$MOCK_ROOT/.git/scool-sync.lock" ]] || exit 1
    trace="$(< "$TRACE")"
    [[ "$trace" == *'git fetch origin main'*' stop nginx queue app'*'git merge --ff-only origin/main'*' build app queue'*'composer install'*'npm ci'*'config:clear'*'verify-runtime.php config'*'artisan migrate --no-interaction'*'verify --mode verify'* ]] || { printf '[FAIL] Incorrect sync ordering.\n'; exit 1; }
    [[ "$trace" != *'migrate:fresh'* && "$trace" != *'key:generate'* && "$trace" != *'db:seed'* && "$trace" != *' down '* && "$trace" != *'queue:flush'* ]] || exit 1
    passed=$((passed+1))
done
printf '[PASS] %s local-sync regression cases with mocked CLIs. No live upgrade, independent-host or production claim.\n' "$passed"
