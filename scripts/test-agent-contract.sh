#!/usr/bin/env bash
# Disposable synthetic Git fixture only; no personal config, Docker, commits or network.
set -euo pipefail
script_dir=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)
repo_root=$(cd -- "$script_dir/.." && pwd -P)
temp_root=$(cd -- "${TMPDIR:-/tmp}" && pwd -P)
fixture=$(mktemp -d "$temp_root/scool-agent-contract.XXXXXX")
cleanup() {
    # Resolve and validate the exact temporary target before recursive deletion.
    local resolved
    resolved=$(cd -- "$fixture" && pwd -P) || return
    if [[ "$resolved" == "$temp_root"/scool-agent-contract.* && "$resolved" != "$temp_root" && ! -L "$fixture" ]]; then
        rm -rf -- "$resolved"
    fi
}
trap cleanup EXIT
files=(
    AGENTS.md README.md .gitignore docs/00_README.md
    docs/AI_WORKFLOW.md docs/WORKING_CONTEXT.md
    docs/01_PRODUCT_SCOPE.md docs/02_DOMAIN_ARCHITECTURE.md docs/03_DELIVERY_PLAN.md
    scripts/verify-agent-contract.sh scripts/test-agent-contract.sh
)
for path in "${files[@]}"; do
    mkdir -p -- "$fixture/$(dirname -- "$path")"
    cp -- "$repo_root/$path" "$fixture/$path"
done
git -C "$fixture" init --quiet
passed=0
verify() { "$BASH" "$fixture/scripts/verify-agent-contract.sh" "$@"; }
accept() { verify "$@" >/dev/null; passed=$((passed+1)); }
reject() {
    local code=0
    verify "$@" >/dev/null 2>&1 || code=$?
    [[ "$code" == 1 ]] || { printf '[FAIL] Expected contract rejection, got %s.\n' "$code" >&2; exit 1; }
    passed=$((passed+1))
}
# A teammate checkout has no private folders or notebook.
accept
reject --require-tracked
reject --unsafe

mv -- "$fixture/docs/WORKING_CONTEXT.md" "$fixture/docs/context-backup.md"
reject
mv -- "$fixture/docs/context-backup.md" "$fixture/docs/WORKING_CONTEXT.md"

printf '\n/docs/AI_WORKFLOW.md\n' >> "$fixture/.gitignore"
reject
cp -- "$repo_root/.gitignore" "$fixture/.gitignore"

sed 's@docs/AI_WORKFLOW.md@docs/nonexistent.md@g' "$repo_root/AGENTS.md" > "$fixture/AGENTS.md"
reject
cp -- "$repo_root/AGENTS.md" "$fixture/AGENTS.md"

sed 's@docs/03_DELIVERY_PLAN.md@docs/nonexistent.md@g' "$repo_root/AGENTS.md" > "$fixture/AGENTS.md"
reject
cp -- "$repo_root/AGENTS.md" "$fixture/AGENTS.md"

sed 's/Issue description template/Removed issue template/g' "$repo_root/docs/AI_WORKFLOW.md" > "$fixture/docs/AI_WORKFLOW.md"
reject
cp -- "$repo_root/docs/AI_WORKFLOW.md" "$fixture/docs/AI_WORKFLOW.md"

sed 's/Issue quality gate/Removed issue gate/g' "$repo_root/docs/03_DELIVERY_PLAN.md" > "$fixture/docs/03_DELIVERY_PLAN.md"
reject
cp -- "$repo_root/docs/03_DELIVERY_PLAN.md" "$fixture/docs/03_DELIVERY_PLAN.md"

sed 's/## Verification plan/## Removed verification plan/g' "$repo_root/docs/03_DELIVERY_PLAN.md" > "$fixture/docs/03_DELIVERY_PLAN.md"
reject
cp -- "$repo_root/docs/03_DELIVERY_PLAN.md" "$fixture/docs/03_DELIVERY_PLAN.md"

for pattern in '/.agent/' '/.agent-reference/' '/docs/04_DEVOPS_OPERATIONS.md'; do
    # Remove one exact ignore line, without treating the pattern as a regex.
    awk -v target="$pattern" '{ line=$0; sub(/\r$/, "", line); if (line != target) print $0 }' "$repo_root/.gitignore" > "$fixture/.gitignore"
    reject
done
cp -- "$repo_root/.gitignore" "$fixture/.gitignore"

# Synthetic private files may exist locally, but are never copied from this checkout.
mkdir -p -- "$fixture/.agent" "$fixture/.agent-reference"
for path in .agent/local-note.md .agent-reference/local-note.md docs/04_DEVOPS_OPERATIONS.md; do
    printf 'Synthetic private fixture only.\n' > "$fixture/$path"
done
accept
git -C "$fixture" add -- "${files[@]}"
accept --require-tracked
# Ignore rules alone must not hide an already tracked private file.
git -C "$fixture" add -f -- .agent/local-note.md
reject --require-tracked
git -C "$fixture" rm --cached --quiet -- .agent/local-note.md
printf '[PASS] %s agent-contract regression cases. Private-free checkout, ignore boundaries and index leakage tested; no agent-compliance claim.\n' "$passed"
