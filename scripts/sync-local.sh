#!/usr/bin/env bash
# Existing local installation only. Never reset data, overwrite .env or regenerate keys.
set -Eeuo pipefail
script_dir=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)
repo_root=$(cd -- "$script_dir/.." && pwd -P)
# shellcheck source=setup-verification.sh
source "$script_dir/setup-verification.sh"
no_pull=false
case "${1:-}" in
    '') (($# == 0)) || exit 2 ;;
    --no-pull) (($# == 1)) || exit 2; no_pull=true ;; # Internal restart after fast-forward.
    --help) (($# == 1)) || exit 2; printf 'Usage: bash scripts/sync-local.sh\nRequires clean main, an existing local installation, Docker running and Vite stopped.\n'; exit 0 ;;
    *) printf '[FAIL] Unknown option. See --help.\n' >&2; exit 2 ;;
esac
lock_owned=false
runtime_changed=$no_pull
release_lock() {
    if [[ "$lock_owned" == true ]]; then
        rmdir -- "$lock_path"
        lock_owned=false
    fi
}
trap release_lock EXIT
on_error() {
    local code=$1 line=$2
    if [[ "$runtime_changed" == true ]]; then
        printf '[FAIL] Local sync stopped (line %s, exit %s). The stack may be stopped or partially updated. Fix the reported step and rerun; no automatic rollback/reset.\n' "$line" "$code" >&2
    else
        printf '[FAIL] Sync preflight stopped (line %s, exit %s). No containers or database were updated.\n' "$line" "$code" >&2
    fi
    exit "$code"
}
trap 'on_error "$?" "$LINENO"' ERR
trap 'exit 130' INT
trap 'exit 143' TERM
cd -- "$repo_root"
for tool in git docker node npm curl; do
    command -v "$tool" >/dev/null || { fail "Missing prerequisite: $tool"; exit 1; }
done
root=$(run 'Git root' git rev-parse --show-toplevel)
assert_checkout_owner "$repo_root" "$root"
branch=$(run 'Branch' git symbolic-ref --quiet --short HEAD)
[[ "${branch//$'\r'/}" == main ]] || { fail 'Switch to main before syncing. Feature branches are not updated.'; exit 1; }
dirty=$(run 'Working tree' git status --porcelain --untracked-files=normal)
[[ -z "$dirty" ]] || { fail 'Commit or move your local changes first. Sync never stashes or resets them.'; exit 1; }
for operation in MERGE_HEAD CHERRY_PICK_HEAD REVERT_HEAD rebase-merge rebase-apply; do
    path=$(git rev-parse --git-path "$operation")
    [[ ! -e "$path" ]] || { fail 'Finish the active Git operation before syncing.'; exit 1; }
done
[[ -f .env && ! -L .env ]] || { fail 'An existing local .env is required. Use Bootstrap only for a new installation.'; exit 1; }
git check-ignore --quiet .env || { fail '.env must stay ignored.'; exit 1; }
[[ -z "$(git ls-files -- .env)" ]] || { fail '.env must not be tracked.'; exit 1; }
[[ ! -e public/hot && ! -L public/hot ]] || { fail 'Stop Vite; remove public/hot only after confirming it is stale.'; exit 1; }
env_hash=$(git hash-object --no-filters -- .env)
assert_env_unchanged() {
    [[ -f .env && ! -L .env && "$(git hash-object --no-filters -- .env)" == "$env_hash" ]] || { fail '.env changed during sync. Inspect locally; no automatic overwrite or rollback.'; return 1; }
}
lock_path=$(git rev-parse --git-path scool-sync.lock)
mkdir -- "$lock_path" 2>/dev/null || { fail 'Another sync may be running. Inspect the empty scool-sync.lock directory before removing a stale lock.'; exit 1; }
lock_owned=true
docker_root=$repo_root
if command -v cygpath >/dev/null 2>&1; then
    docker_root=$(cygpath -am "$repo_root")
    export MSYS_NO_PATHCONV=1
fi
compose=(docker compose --project-directory "$docker_root" -f "$docker_root/docker-compose.yml" -p scool)
run 'Docker engine' docker info --format '{{.ServerVersion}}'
run 'Compose configuration' "${compose[@]}" config --quiet
volume=$(run 'Existing local database volume' docker volume inspect scool_scool_mysql_data --format '{{.Name}}')
[[ "${volume//$'\r'/}" == scool_scool_mysql_data ]] || { fail 'Existing S-cool database volume is required; no new database was created.'; exit 1; }
for service in app nginx mysql mailpit queue; do
    id=$(run 'Existing service inventory' "${compose[@]}" ps --all -q "$service")
    id=${id//$'\r'/}
    # Compose returns full IDs; Docker ps truncates them unless explicitly disabled.
    named=$(run 'Container name inventory' docker ps -aq --no-trunc --filter "name=^scool_${service}$")
    named=${named//$'\r'/}
    [[ "$named" == "$id" ]] || { fail "Container name conflict for $service; nothing was stopped."; exit 1; }
    if [[ -n "$id" ]]; then
        assert_single_id "$service" "$id"
        owner=$(run 'Existing checkout owner' docker inspect --format '{{ index .Config.Labels "com.docker.compose.project.working_dir" }}' "$id")
        assert_checkout_owner "$repo_root" "$owner"
    fi
done
if [[ "$no_pull" == false ]]; then
    run 'Fetch main' git fetch origin main
    ahead=$(git rev-list --count origin/main..HEAD)
    [[ "${ahead//$'\r'/}" == 0 ]] || { fail 'Local main has unpublished commits. Resolve them manually; no merge/reset was attempted.'; exit 1; }
    [[ -z "$(git ls-tree --name-only origin/main -- .env)" ]] || { fail 'Remote main tracks .env; refusing to overwrite private configuration.'; exit 1; }
    runtime_changed=true
    run 'Stop web and drain worker before changing code' "${compose[@]}" stop nginx queue app
    # Fetch + fast-forward merge is the safe pull equivalent, pinned to the checked ref.
    run 'Fast-forward main' git merge --ff-only origin/main
    assert_env_unchanged
    release_lock
    # Reload both script and helpers from the updated revision, without another fetch.
    exec "$BASH" "$script_dir/sync-local.sh" --no-pull
fi
[[ "$(git rev-parse HEAD)" == "$(git rev-parse origin/main)" ]] || { fail 'Sync requires main to match fetched origin/main.'; exit 1; }
expected_revision=$(git rev-parse HEAD)
run 'Stop web and worker for runtime update' "${compose[@]}" stop nginx queue app
run 'Build PHP images' "${compose[@]}" build app queue
run 'MySQL readiness' "${compose[@]}" up -d --wait --wait-timeout 120 mysql
run 'Locked PHP dependencies' "${compose[@]}" run --rm --no-deps app composer install --no-interaction --prefer-dist
run 'Locked frontend dependencies' npm ci
run 'Clear stale configuration' "${compose[@]}" run --rm --no-deps app php artisan config:clear
run 'Validate local scool and isolated scool_test' "${compose[@]}" run --rm --no-deps app php scripts/verify-runtime.php config
run 'Local URL/SMTP must use http://127.0.0.1:8080 and smtp/mailpit:1025; correct .env manually if needed' "${compose[@]}" run --rm --no-deps app php -r 'require "vendor/autoload.php"; $app = require "bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); exit(config("app.url") === "http://127.0.0.1:8080" && config("mail.default") === "smtp" && config("mail.mailers.smtp.host") === "mailpit" && (int) config("mail.mailers.smtp.port") === 1025 ? 0 : 1);'
assert_env_unchanged
run 'Apply pending forward migrations' "${compose[@]}" run --rm --no-deps app php artisan migrate --no-interaction
run 'Start updated stack' "${compose[@]}" up -d --wait --wait-timeout 120
# Verify already builds assets and checks runtime/queue, style and guarded tests.
run 'Integrated Verify' "$BASH" "$script_dir/verify-fresh-setup.sh" --mode verify
assert_env_unchanged
revision=$(git rev-parse HEAD)
branch=$(git symbolic-ref --quiet --short HEAD)
dirty=$(git status --porcelain --untracked-files=normal)
[[ "$revision" == "$expected_revision" && "${branch//$'\r'/}" == main && -z "$dirty" ]] || { fail 'Source/branch changed during sync. Review locally; no automatic reset.'; exit 1; }
release_lock
printf '[PASS] Local sync complete; HEAD=%s; project=scool; app=http://127.0.0.1:8080.\n' "${revision//$'\r'/}"
