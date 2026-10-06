# S-cool - working context

A checkpoint is historical context, not current Linear/Git/runtime evidence. Verify HEAD, status and source before resuming. Preserve separate issue sections.

## EUR-20 - setup verification checkpoint (2026-10-06)

- [EUR-20](https://linear.app/eurusdevsec/issue/EUR-20/add-testbuild-commands-and-verify-fresh-setup-on-two-machines), parent EUR-5, M1. Last recorded Linear status: In Review; not refreshed for this documentation change.
- Local branch `codex/eur-20-fresh-setup`, HEAD `b9c8e5a481984da7359bb86530e3c2032c30b7e2`.
- PR [#5](https://github.com/Kerneural/S-cool/pull/5) was verified merged on 2026-10-06 into main `7038dd9af07849d6b6b1e48b1fd11f5b6c88c715`. Local checkout remains on the publication branch with unrelated dirty edits; do not automatically switch/reset it.
- Code, local verification and a same-host isolated fresh bootstrap passed. The AC requiring two independent machines remains open. Merge does not supply missing evidence.
- No current authorization to publish this follow-up, change Linear status or implement CI EUR-26.

### Implementation and flow

- Bash entrypoint `scripts/verify-fresh-setup.sh --mode bootstrap|verify`; default is verify. Shell helper: `setup-verification.sh`; regression: `test-setup-verification.sh`. PHP helper retains Laravel configuration/DB/seed/queue guards.
- Bootstrap: checkout/resource guards -> env/key/image/locked dependencies -> built assets -> MySQL readiness and actual empty local DB guard -> migrate -> seed/rerun preservation -> stack/worker readiness -> service/checkout/HTTP/queue checks -> Composer validation/style/full tests.
- Verify does not migrate/seed the development DB. Tests are guarded to `scool_test` before RefreshDatabase. The real queue smoke job only writes a unique log marker.
- Native errors, missing health/service, checkout mismatches and timeouts fail closed. No automatic resets, queue flushes or volume deletion.
- Isolated `--project scool-eur20-NAME` uses the base Compose contract plus `fixtures/compose.fresh.yml`; only names, loopback ports and volume namespace change. Compose >= 2.24.4 is needed for `!override`.
- Git Bash path normalization, MSYS container paths and native curl were verified. Composer exposes test/pint/pint:test/verify; lockfile versions were not upgraded.

### Historical runtime evidence

These results are revision-specific; they are not new evidence for later edits.

- Git Bash 5.2.26; Docker Engine 29.7.2 / Compose 5.3.1; Node 22.23.2 / npm 10.9.8; Vite 6.4.3.
- Setup guard regressions: 19 PASS. Existing-checkout bootstrap was rejected before writes because `.env` already existed.
- Local Verify: exit 0, Composer validate --strict, Pint 57 files, 52 tests / 215 assertions, live Mailpit SMTP, built assets and queue smoke passed.
- Fresh same-host drill: an ignored clone and new project/volume, initially without env/dependencies/assets. Source was HEAD plus an exact script patch, not an immutable CI revision.
- Fresh bootstrap: PHP image, 112 Composer packages, npm ci, key, empty DB guard, 3 migrations, 5 seeded personas and preserved rerun; five healthy services, HTTP and queue checks passed. Exit 0 in 239 seconds; no fixed-duration promise.
- Fresh queue marker `EUR20-smoke-df2beafc-657f-4240-9604-bc2a18c45da8`: pending=0, failed=0, handler=1.
- Fresh full tests: 52 / 215 assertions; Pint 57 files; production asset build passed.
- Script SHA256 at that run: `7A95B3CB2973432414B8EE1A349167A99CD20AAE0796FEACC69B2D654ADD6FFA`. Subsequent structural-preflight edits change that script; do not reuse this hash as current proof.
- Evidence comments: EUR-20 `4aca27fa-d9f0-464b-8782-e1684f202dc5`; EUR-5 `db013a37-76d3-4e65-8dff-80cf24bb1113`.
- Previous preflight variant also passed Verify in 57 seconds: 52 tests / 215 assertions, Pint 57, Vite 59 modules, queue marker `EUR20-smoke-def83635-f6bd-468b-8859-7a749b054331` with pending=0/failed=0/handler=1. This was a dirty follow-up, not CI or fresh-bootstrap evidence for the current variant.

### Security and acceptance limits

- Recorded full npm audit: 5 high + 2 moderate in the Tailwind dependency tree. No force upgrade was applied. Remediation or an approved exception is required before a security gate.
- EUR-26 security comment: `712baa0d-628a-4468-9948-dcc2c6ba3ce7`. An omit-dev audit of zero does not prove compiled frontend safety.
- A clean clone and isolated stack on one host are not a second machine. Obtain independent-host OS/tool/revision/initial-state/migration/seed/queue/test/build evidence.
- Git Bash was runtime-tested; Linux/WSL runtime remains unverified. The log probe is not a general exactly-once queue guarantee.
- The isolated stack was stopped without deleting its volume; development data and the shared mail inbox were preserved.

### Next action

A second machine should clone the published source, run `bash scripts/verify-fresh-setup.sh --mode bootstrap` and attach sanitized evidence to EUR-20/EUR-5. Do not automatically mark Done/M1 complete. CI work belongs to EUR-26.

## Shared contract correction (2026-10-06)

### Scope and boundaries

- Requested change: English agent-facing files, IDE-neutral shared rules and private personal configuration.
- Shared flow: root AGENTS -> AI_WORKFLOW -> issue checkpoint + current Git/Linear/source. Teammate setup reads only shared documentation and checked-in runtime code.
- Entire `.agent/` and `.agent-reference/` directories and the personal operations notebook are ignored. Seven newly generated IDE-specific adapter files were removed; the original upstream cache was not deleted or modified.
- README keeps clone -> cd -> one bootstrap command and the existing operational/test commands. No React/runtime/domain/stack changes.
- Shared structural checks require 11 public files, verify routing/privacy boundaries and reject tracked private configuration. They do not read personal configuration or enforce model behavior.
- Product/architecture/delivery content and pre-existing Nginx/learning-note edits are preserved. This is an agent-contract correction, not a translation of every existing project document.
- Allowed edits for review: AGENTS.md, .gitignore, README.md, docs/00_README.md, docs/AI_WORKFLOW.md, this checkpoint, both agent-contract scripts and the single existing setup preflight call.
- No real-repository staging, commit, push, PR creation, merge or Linear mutation is authorized. Temporary regression fixtures stage synthetic files only.

### Verification

- Corrected-version Bash syntax and structural check PASS: 11 shared files, no private-file prerequisites.
- Agent-contract regression: 12 PASS, including a private-free synthetic checkout, missing/ignored shared files, broken routing, removed privacy ignores, strict index checks and rejection of force-staged synthetic private content.
- Setup guard regressions: 19 PASS; no Docker/data changes in those regression fixtures.
- Local Verify on HEAD `b9c8e5a481984da7359bb86530e3c2032c30b7e2` plus this dirty follow-up: exit 0 in 55 seconds. Composer validate --strict, Pint 57 files, 52 tests / 215 assertions (live Mailpit SMTP included) and Vite build 59 modules PASS.
- Real queue marker `EUR20-smoke-c1ca2f3d-e51b-4fb8-884c-96d545233a78`: pending=0, failed=0, handler=1.
- Shared agent-facing files contain English/ASCII text and no IDE-specific adapters. Local Markdown links and `git diff --check` PASS.
- Entire personal folders/notebook are ignored and absent from the Git index. Upstream cache remains clean. Existing Nginx/Product/Delivery diffs are unchanged; the real Git index is empty.
- `--require-tracked` intentionally rejects the newly added, unstaged checker. Publication must include both shared checker scripts and the preflight call together.
- This is local dirty-source verification, not CI, fresh-bootstrap proof for this revision or independent-host evidence. English wording/checkpoint edits do not claim model compliance.

### Remaining limits and next action

Verify the shared entrypoint in a fresh session of each teammate's IDE agent; Markdown and structural checks cannot guarantee discovery or compliance.
Review this bounded diff before separately authorizing publication. Independent-host setup evidence and CI remain open.
