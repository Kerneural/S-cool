#!/usr/bin/env bash
# One run proves one host, not two independent machines. Bash 3.2+.
set -euo pipefail
script_dir=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)
repo_root=$(cd -- "$script_dir/.." && pwd -P)
# shellcheck source=setup-verification.sh
source "$script_dir/setup-verification.sh"
trap 'code=$?; printf "[FAIL] Setup stopped (line %s, exit %s). No automatic reset or volume deletion.\n" "$LINENO" "$code" >&2; exit "$code"' ERR

mode=verify
project=scool
while (($#)); do
    case "$1" in
        --mode|--project)
            (($# >= 2)) || { fail "Missing value for $1"; exit 2; }
            if [[ "$1" == --mode ]]; then mode=$2; else project=$2; fi
            shift 2 ;;
        --help)
            printf 'Usage: bash scripts/verify-fresh-setup.sh [--mode bootstrap|verify] [--project scool|scool-eur20-NAME]\n'
            exit 0 ;;
        *) fail 'Unknown option. See --help.'; exit 2 ;;
    esac
done
[[ "$mode" == bootstrap || "$mode" == verify ]] || { fail 'Mode must be bootstrap or verify.'; exit 2; }
[[ "$project" == scool || "$project" =~ ^scool-eur20-[a-z0-9][a-z0-9-]*$ ]] || { fail 'Unexpected local project name.'; exit 2; }
cd -- "$repo_root"
started=$SECONDS
if [[ "$mode" == bootstrap ]]; then assert_fresh_checkout "$repo_root"; fi
for tool in git docker node npm curl; do
    command -v "$tool" >/dev/null || { fail "Missing prerequisite: $tool"; exit 1; }
done
# Docker Desktop: keep container paths such as /var/www/html from MSYS rewriting.
# Host bind paths are still passed explicitly; cygpath converts them when needed.
docker_root=$repo_root
if command -v cygpath >/dev/null 2>&1; then
    docker_root=$(cygpath -am "$repo_root")
    export MSYS_NO_PATHCONV=1
fi
compose=(docker compose --project-directory "$docker_root" -f "$docker_root/docker-compose.yml" -p "$project")
web_port=8080
if [[ "$project" != scool ]]; then
    # Same runtime contract, isolated names/volume/loopback ports for local clean drills.
    compose+=(-f "$docker_root/scripts/fixtures/compose.fresh.yml")
    web_port=18080
fi
run 'Docker engine' docker info --format '{{.ServerVersion}}'
run 'Docker Compose' docker compose version --short
run 'Node' node --version
run 'npm' npm --version
run 'Compose contract' "${compose[@]}" config --quiet

if [[ "$mode" == bootstrap ]]; then
    existing=$(run 'Existing containers' docker ps -aq --filter "label=com.docker.compose.project=$project")
    names_prefix=scool
    [[ "$project" == scool ]] || names_prefix=$project
    named=$(run 'Container name conflicts' docker ps -aq --filter "name=^${names_prefix}_")
    volumes=$(run 'Existing database volume' docker volume ls -q --filter "name=^${project}_scool_mysql_data$")
    [[ -z "$existing$named$volumes" ]] || { fail 'Bootstrap refuses an existing stack/volume. Nothing was deleted.'; exit 1; }
    # Noclobber protects an env file created concurrently; never overwrite an existing key.
    (set -o noclobber; while IFS= read -r line || [[ -n "$line" ]]; do printf '%s\n' "$line"; done < .env.example > .env)
    run 'PHP image build' "${compose[@]}" build app queue
    run 'Composer install' "${compose[@]}" run --rm --no-deps app composer install --no-interaction --prefer-dist
    run 'Application key' "${compose[@]}" run --rm --no-deps app php artisan key:generate --no-interaction
    run 'Frontend lockfile install' npm ci
else
    [[ -f .env && -f vendor/autoload.php && -d node_modules ]] || { fail 'Verify needs an installed checkout; see README.'; exit 1; }
fi
[[ ! -e public/hot ]] || { fail 'Stop Vite and remove its stale hot file before built-asset verification.'; exit 1; }
run 'Production asset build' npm run build
[[ -f public/build/manifest.json ]] || { fail 'Vite manifest is missing.'; exit 1; }

if [[ "$mode" == bootstrap ]]; then
    run 'MySQL readiness' "${compose[@]}" up -d --wait --wait-timeout 120 mysql
    run 'Empty local database guard' "${compose[@]}" run --rm --no-deps app php scripts/verify-runtime.php empty
    run 'Migration' "${compose[@]}" run --rm --no-deps app php artisan migrate --no-interaction
    run 'Seed and rerun preservation' "${compose[@]}" run --rm --no-deps app php scripts/verify-runtime.php seed
    run 'Stack readiness' "${compose[@]}" up -d --wait --wait-timeout 120
fi
app_id=''
for service in app nginx mysql mailpit queue; do
    id=$(run 'Service inventory' "${compose[@]}" ps --all -q "$service")
    id=${id//$'\r'/}
    assert_single_id "$service" "$id"
    state=$(run 'Service health' docker inspect --format '{{.State.Status}}|{{if .State.Health}}{{.State.Health.Status}}{{end}}' "$id")
    assert_service_state "$service" "${state//$'\r'/}"
    [[ "$service" != app ]] || app_id=$id
done
owner=$(run 'Checkout ownership' docker inspect --format '{{ index .Config.Labels "com.docker.compose.project.working_dir" }}' "$app_id")
assert_checkout_owner "$repo_root" "$owner"
# Capture HEAD headers privately: /dev/null is not portable to native Windows curl.
http_probe=$(run 'HTTP health' curl --head --fail --silent --show-error --max-time 15 -w $'\n%{http_code}' "http://127.0.0.1:$web_port/up")
status=${http_probe##*$'\n'}
[[ "${status//$'\r'/}" == 200 ]] || { fail 'HTTP /up did not return 200.'; exit 1; }
run 'Real daemon queue smoke' "${compose[@]}" exec -T app php scripts/verify-runtime.php queue
run 'Composer manifest/lock validation' "${compose[@]}" exec -T app composer validate --strict
run 'Style and full tests' "${compose[@]}" exec -T app composer verify
revision=$(run 'Source revision' git rev-parse HEAD)
dirty=$(run 'Working tree status' git status --porcelain)
dirty_state=false
[[ -z "$dirty" ]] || dirty_state=true
printf '[PASS] %s on this host; HEAD=%s; dirty=%s; elapsed=%ss; project=%s.\n' "$mode" "$revision" "$dirty_state" "$((SECONDS-started))" "$project"
printf 'Record host alias, OS/tool versions and results in EUR-20/EUR-5. Two independent machines require separate evidence.\n'
