#!/usr/bin/env bash
# Structural checks only: no model invocation, network, Docker or application data writes.
set -euo pipefail
script_dir=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)
repo_root=$(cd -- "$script_dir/.." && pwd -P)
cd -- "$repo_root"
fail() { printf '[FAIL] Agent contract: %s\n' "$*" >&2; exit 1; }
require_tracked=false
case "${1:-}" in
    '') (($# == 0)) || fail 'Unexpected arguments.' ;;
    --require-tracked) (($# == 1)) || fail 'Unexpected arguments.'; require_tracked=true ;;
    --help) printf 'Usage: bash scripts/verify-agent-contract.sh [--require-tracked]\n'; exit 0 ;;
    *) fail 'Unknown option; see --help.' ;;
esac
command -v git >/dev/null || fail 'Git is required.'
git rev-parse --is-inside-work-tree >/dev/null 2>&1 || fail 'Run from a Git checkout.'

# This manifest intentionally excludes every private folder/notebook.
required=(
    AGENTS.md README.md .gitignore docs/00_README.md
    docs/AI_WORKFLOW.md docs/WORKING_CONTEXT.md
    docs/01_PRODUCT_SCOPE.md docs/02_DOMAIN_ARCHITECTURE.md docs/03_DELIVERY_PLAN.md
    scripts/verify-agent-contract.sh scripts/test-agent-contract.sh
)
for path in "${required[@]}"; do
    [[ -s "$path" && ! -L "$path" ]] || fail "Missing/empty/symlinked shared file: $path"
    code=0
    git check-ignore --no-index --quiet -- "$path" || code=$?
    [[ "$code" == 1 ]] || fail "Shared file ignored or ignore check failed: $path"
    if [[ "$require_tracked" == true ]]; then
        git ls-files --error-unmatch -- "$path" >/dev/null 2>&1 || fail "Not in Git index: $path"
    fi
done

has() { grep -Fq -- "$2" "$1" || fail "Required routing/gate missing from $1"; }
for source in docs/AI_WORKFLOW.md docs/WORKING_CONTEXT.md; do has AGENTS.md "$source"; done
[[ $(wc -l < AGENTS.md) -le 30 ]] || fail 'Entrypoint exceeds 30 lines; keep routing concise.'
has AGENTS.md 'Commit/push/PR/merge'
has docs/AI_WORKFLOW.md 'Publication'
has docs/AI_WORKFLOW.md 'save'
has docs/AI_WORKFLOW.md 'scool_test'
for doc in 01_PRODUCT_SCOPE 02_DOMAIN_ARCHITECTURE 03_DELIVERY_PLAN; do
    has docs/AI_WORKFLOW.md "docs/$doc.md"
done

# Check ignore rules without reading/copying private content. Files need not exist.
for path in .agent/local-note.md .agent-reference/local-note.md docs/04_DEVOPS_OPERATIONS.md; do
    git check-ignore --no-index --quiet -- "$path" || fail "Personal path must remain ignored: $path"
done
private_tracked=$(git ls-files -- .agent .agent-reference docs/04_DEVOPS_OPERATIONS.md)
[[ -z "$private_tracked" ]] || fail 'Personal configuration/reference/notebook found in Git index.'

printf '[PASS] Agent contract: %s shared files; routing/privacy gates valid; require-tracked=%s.\n' "${#required[@]}" "$require_tracked"
printf 'Structural evidence only. No private configuration is required. Agent readiness, code tests, approvals and CI remain separate.\n'
