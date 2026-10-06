#!/usr/bin/env bash
set -Eeuo pipefail
script_dir=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)
# shellcheck source=setup-verification.sh
source "$script_dir/setup-verification.sh"
passed=0
expect_rejected() {
    if "$@" >/dev/null 2>&1; then fail 'Expected rejection did not occur.'; exit 1; fi
    passed=$((passed+1))
}
expect_rejected run 'Failing gate' "$BASH" -c 'exit 7'
code=0
run 'Exit propagation' "$BASH" -c 'exit 7' >/dev/null 2>&1 || code=$?
[[ "$code" == 7 ]] || { fail 'Exit code was not preserved.'; exit 1; }
passed=$((passed+1))
output=$(run 'Benign stderr' "$BASH" -c 'printf "progress\n" >&2; printf ok')
[[ "$output" == ok ]] || exit 1
passed=$((passed+1))
assert_single_id app 'abc123'; passed=$((passed+1))
expect_rejected assert_single_id queue ''
expect_rejected assert_single_id app $'first\nsecond'
assert_service_state app 'running|healthy'; passed=$((passed+1))
expect_rejected assert_service_state app 'running|unhealthy'
expect_rejected assert_service_state app 'exited|healthy'
expect_rejected assert_service_state app 'running|'
assert_checkout_owner "$script_dir" "$script_dir/"; passed=$((passed+1))
expect_rejected assert_checkout_owner "$script_dir" "$script_dir/other"
expect_rejected assert_checkout_owner "$script_dir" ''
assert_fresh_checkout "$script_dir"; passed=$((passed+1))
if [[ -e "$script_dir/../.env" ]]; then expect_rejected assert_fresh_checkout "$script_dir/.."; fi
expect_rejected "$BASH" "$script_dir/verify-fresh-setup.sh" --mode invalid
expect_rejected "$BASH" "$script_dir/verify-fresh-setup.sh" --project unsafe-name
expect_rejected "$BASH" "$script_dir/verify-fresh-setup.sh" --mode
expect_rejected "$BASH" "$script_dir/verify-fresh-setup.sh" --skip-tests
printf '[PASS] %s Bash regression checks. No Docker/data changes; not fresh-setup evidence.\n' "$passed"
