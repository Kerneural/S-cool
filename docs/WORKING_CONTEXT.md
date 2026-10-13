# S-cool - working context

A checkpoint is historical context, not current Linear/Git/runtime evidence. Verify HEAD, status and source before resuming. Preserve separate issue sections.

## Shared execution workflow checkpoint (2026-10-10)

- Authorized scope: relocate durable execution lessons to `docs/AI_WORKFLOW.md`, section **Execution efficiency**, and correct entrypoint routing. No application, runtime, dependency, data or publication change is authorized.
- Starting checkout: `local-sync-safety`, HEAD `1bc6077571273918cdd8ad4d30b48ce12f601876`; the existing edits in `AGENTS.md`, `docs/AI_WORKFLOW.md` and this file are retained/reorganized. All other issue sections are preserved.
- Diff: workflow owns the detailed observed failures, recovery guidance, bounded execution loop, verification selection/reuse and receiving-agent checklist. `AGENTS.md` requires that workflow section; this context stores only the change checkpoint. Routine checkpoint maintenance cannot silently rewrite the shared contract.
- Historical evidence from the initial documentation edit: `git diff --check` PASS and `bash scripts/verify-agent-contract.sh --require-tracked` PASS for 11 files through the approved execution route. The sandboxed Git Bash startup exited `-1073741502` without a usable diagnostic; the same denied route was not blindly retried. These results predate relocation and are not fresh verification of the final diff.
- Verification of the relocation: `git diff --check` PASS; direct routing checks confirmed mandatory reading from `AGENTS.md`/workflow, all four durable subsections in the workflow, no obsolete context routing or duplicate rules, and unchanged remaining issue checkpoints against HEAD. `bash scripts/verify-agent-contract.sh --require-tracked` PASS for 11 shared files via the approved route, without retrying the known failed sandbox startup. Structural checks cannot prove agent compliance; no application suite, runtime operation, index refresh, staging or publication was performed. Only this evidence note changes after those checks.
- Next: review the bounded documentation diff. Commit/push/PR/merge are not authorized; teammates receive the shared rules only after authorized publication and pull.

## GitNexus local navigation setup (2026-10-09)

- Requested scope: enable graph navigation for this checkout before resuming PR #19 review. That audit, fixes, GitHub/Linear comments and publication remain pending; no PR acceptance is implied.
- Starting branch `local-sync-safety`, HEAD `e7e612265974bcf20fbb4a6125745e6e5fbc7aef`, working tree clean. Existing GitNexus CLI `1.6.11` and MCP were reused without reinstalling/upgrading or changing global/editor configuration.
- `.gitnexusrc` enables index-only mode, no embeddings and two workers. `.gitnexusignore` excludes private configuration, runtime storage, uploaded content and dependencies; `.gitignore` excludes the generated index. The optional workflow section routes graph discovery without replacing AGENTS or canonical issue/source evidence.
- Scanner verification admitted 207 files (54 app, 3 routes, 29 tests, 50 views) and no selected private/runtime paths. Initial analysis succeeded: 1,372 nodes, 2,451 edges, 68 clusters and 56 flows, registered in MCP as `S-cool`.
- MCP overview, bounded query and `ClassroomController` context succeeded. Source was indexed on this branch, not PR #19. Dynamic Laravel dispatch and UI behavior still require actual source/diff and runtime checks.
- Agent contract `--require-tracked`: 11 shared files PASS; application/package files and AGENTS unchanged. No Docker/database operations, embeddings, wiki generation, graph publication or Git/Linear writes.
- A documentation edit made the first index stale; refresh after the setup/checkpoint edits before handoff. Windows sandbox denied a Node/NVM realpath during status; the read-only check worked outside the sandbox. Other hosts remain unverified.
- Modified files are limited to `.gitignore`, `.gitnexusrc`, `.gitnexusignore`, `docs/AI_WORKFLOW.md` and this checkpoint. No authorization to commit/push these setup changes. Next: confirm fresh status and MCP access, then refresh on the PR revision when the PR #19 audit resumes.

## Local team sync hardening (2026-10-08)

- Requested outcome: one safe Bash command updates an existing teammate installation after merged changes, without replacing private configuration or resetting local data.
- Branch `local-sync-safety`, base HEAD `c1e781b4e139f60c7ec6d761c5e2507084985223` from fetched `origin/main`; initial working tree clean. PR #17 was verified merged before this work. This is a separate follow-up, not an addition to that PR.
- Changed files: `scripts/sync-local.sh`, `scripts/test-sync-local.sh`, README and this checkpoint. No application/domain changes, new dependencies or Linear mutations.
- Commit, push, PR creation and merge are not authorized for this follow-up. The edited script is not yet available to teammates through main.

### Flow and safety boundaries

- Clean main and existing ignored `.env` -> checkout/container/volume guards -> fetch and fast-forward only -> reload updated script -> locked dependencies/images -> local database and URL/SMTP guards -> pending forward migrations -> stack readiness -> integrated Verify -> unchanged environment/revision/branch checks.
- Web and worker stop before code/dependency/schema updates. The restarted worker loads the updated code. Verify builds assets and checks runtime, queue, style and guarded MySQL tests.
- Refuses dirty/feature/detached branches, unpublished main commits, active Git operations, Vite hot files, missing installations, foreign checkout ownership and container-name collisions. An owned Git lock prevents concurrent sync operations in the same checkout.
- No automatic stash/reset, `.env` overwrite, key regeneration, seed, development-data reset, volume deletion, queue flush or shared inbox deletion. Each machine retains its own database contents.
- Forward migrations change schema and may change data by design: review them before merging. Sync is fail-fast, not atomic, a backup or automatic rollback. Failure after runtime changes may leave services stopped or partially updated; resolve the reported step and rerun.

### Verification and remaining evidence

- Bash syntax and `git diff --check`: PASS.
- Mocked sync regression: 37 PASS, including rejection guards, failure ordering, configuration/source tampering and successful running/stopped-stack pipelines. Fixtures use synthetic configuration and mocked CLIs, not real upgrades.
- Existing setup regression: 19 PASS. Shared agent contract with `--require-tracked`: 11 shared files PASS; this structural check does not prove agent compliance or publication.
- Real feature-branch invocation correctly stopped before runtime writes. Read-only Compose validation, runtime database guard and canonical URL/local SMTP guard passed; all five existing services remained running/healthy.
- No full live sync, migrations, configuration reload or dependency/runtime update was performed on this unpublished feature branch. Full application tests were not rerun for this Bash/documentation-only change. Independent-host, Linux/WSL and CI execution remain unverified.
- Next: review and publish a focused follow-up PR when authorized. Teammates receive the script once with a fast-forward pull, then use `git switch main` followed by `bash scripts/sync-local.sh` for later updates; only the final sync PASS marker confirms a completed run.

### GitHub #20 - container identity regression follow-up (2026-10-10)

- Contract: [GitHub issue #20](https://github.com/Kerneural/S-cool/issues/20) and explicit approval to fix container identity comparison and its regression tests. An owned existing stack must not be rejected because Compose returns 64-character IDs while `docker ps -q` returns 12-character IDs. True name collisions, foreign checkout ownership and inventory failures must still stop before runtime/database changes.
- Starting checkout: `local-sync-safety`, HEAD `e7e612265974bcf20fbb4a6125745e6e5fbc7aef`. Preserve existing `.gitignore`, `AGENTS.md`, `docs/AI_WORKFLOW.md`, the GitNexus checkpoint in this file and untracked `.gitnexusignore`/`.gitnexusrc` edits. No branch switch, dependency addition, UI change or environment rewrite is planned.
- Bounded plan: request full Docker IDs using `--no-trunc`; preserve exact equality, checkout ownership and all existing sync safety gates. Make mocked IDs realistic, include an owned-stack success regression and a foreign ID sharing the same 12-character prefix, and cover missing/duplicate/failing inventories. Record evidence here only; do not create a parallel plan.
- Regression-first evidence: `bash scripts/test-sync-local.sh` with the new mock/test and original sync script exited 1: owned-stack success was rejected with the exact issue #20 message at line 76. The test distinguishes the defect; the old 37-case mock returned identical fake ID formats and missed it.
- Verification plan: Bash syntax, local-sync regressions, shared setup regressions, structural agent contract, diff check and read-only ID comparison for all five existing services. GitNexus has no Bash execution flow for these scripts; use source/test evidence, not a graph safety claim.
- Post-fix verification: separate `bash -n` checks for both scripts PASS; a completed `bash -x scripts/test-sync-local.sh` rerun exited 0 with 42 mocked regression cases PASS. An earlier post-fix run was interrupted and is not counted as passing evidence. `bash scripts/test-setup-verification.sh`: 19 PASS; `bash scripts/verify-agent-contract.sh`: 11 shared files PASS; `git diff --check`: PASS. Structural checks do not prove agent compliance or publication.
- Read-only Docker comparison: all five existing `scool` services returned matching 64-character IDs from Compose and `docker ps -aq --no-trunc`. No live sync or application suite was run; application/domain/UI code is unchanged. Source review confirms exact ID equality and checkout ownership are retained, with no automatic cleanup/adoption or weakened data guards.
- Pending: publication and independent execution on the reporting host. No development database mutation, container stop/recreation, `.env` rewrite, commit/push/PR, external comment or issue closure occurred. This bounded fix does not guarantee every local setup can be repaired automatically or complete an end-to-end upgrade on another host.

### GitHub #25 - early local configuration guard (2026-10-10)

- Contract: [GitHub issue #25](https://github.com/Kerneural/S-cool/issues/25), the supplied local configuration (`APP_URL=http://localhost:8080`, with the three mail fields matching), and authorization to prevent recurrence. A mismatched local URL/SMTP configuration must stop before web/worker stop, source update, dependency installation or migrations, identify each offending field without exposing its value, and leave private `.env`/keys unchanged.
- Start: clean `local-sync-safety`, HEAD `f766fbaeb10094801a92b4dde219c3c706067675`. Preserve all unrelated issue checkpoints. No commit/push/PR, external comment, issue closure or live sync is authorized.
- Bounded plan: reuse one URL/SMTP check before the first stop and after dependency/config reload; bypass the persisted config cache only in the diagnostic process so it reads current environment/config without deleting cache or changing `.env`. Keep exact IPv4 origin, ownership/volume guards, late database checks and fail-fast behavior. Add a regression proving preflight rejects before stop and retain a late check for changes during sync. Clarify the four local settings and targeted correction in the existing README sync section.
- Verification plan: discriminating mocked red/green regression; separate Bash syntax checks; completed local-sync and setup regressions; structural contract and diff checks; read-only PHP behavior probes when available for matching/mismatched settings and safe diagnostic output. GitNexus Bash coverage is unavailable; no graph-safety claim, application suite, development migration/reset, container stop or volume deletion is planned.
- Red/green: the new preflight regression against the original sync script exited 1 with `local URL/SMTP preflight reached a forbidden later step`. The final `bash scripts/test-sync-local.sh` run exited 0 with 44 mocked cases PASS, including early rejection before stop, fresh-config invocation, late rejection before migration, unchanged `.env`, ownership/collision guards and running/stopped-stack pipelines. Separate Bash syntax checks PASS for both scripts. Related setup regression: 19 PASS; tracked structural agent contract: 11 files PASS, not proof of compliance/publication. Only README/checkpoint notes changed after the final script/runtime probes.
- PHP runtime probes: 5 PASS using ephemeral `docker compose -p scool run --rm --no-deps -T` containers on the verified checkout with process-local synthetic overrides. Matching settings exit 0; `localhost`, wrong mailer, wrong SMTP host and wrong port each exit 1 with the correct field name and no configured value. Each probe confirms configuration was not loaded from cache; the `.env` checksum is unchanged. No mail, DB query/migration, persistent cache deletion, existing-service stop/start or full live sync is claimed.
- Probe correction: an initial `exec` could not run because the existing app service was stopped. The first one-off cache strategy (`APP_CONFIG_CACHE=/dev/null`) failed because Laravel's loader uses `file_exists`, not only `is_file`; mocks had not detected this. The final override `/dev/null/scool-sync-config.php` cannot exist under the Linux null device, so both checks are false. The corrected exact PHP snippet passed all 5 probes; earlier mock success is not final runtime proof.
- Final source/diff whitespace review PASS. The exact origin/SMTP predicate, data/ownership guards, post-update checks and lack of automatic environment edits remain intact. Trade-off: diagnostic one-off PHP containers add startup time but do not stop existing services; early checks are repeated after script reload, and the late check protects the changed runtime before migrations. No full application suite, CI, independent-host successful sync, publication or issue closure is claimed. After authorized publication, existing clones must fast-forward once to receive the corrected preflight; an old script cannot protect its first update retrospectively.

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

## EUR-21 - Community schema and creator ownership audit (2026-10-07)

### Contract and current revision

- AC source: live [EUR-21](https://linear.app/eurusdevsec/issue/EUR-21/create-community-schema-and-creator-ownership), read 2026-10-07. Status was In Progress; parent EUR-6, M2. Required: migration/model/factory, ownership constraint, private default and ownership tests.
- Related scope: EUR-22 owns private routes/dashboard/protected media; EUR-23 owns membership/invitation schema; EUR-24 owns accept/revoke flow; EUR-25 owns the complete isolation suite.
- Branch renamed to `eur-21-community-baseline`; HEAD remains `3b007b61c4a8bcf07a637c114e9db3c44b5e6711`. Its inherited origin/main upstream was removed. No remote branch was renamed/deleted or pushed; unrelated historical branches remain unchanged.
- Initial diff included model/relationship, migration/factory, policy, controller/routes, four views/navigation, feature tests and this checkpoint. Existing UI overlaps EUR-22 and was preserved, not treated as completion of that issue.
- Initial audit authorization covered scoped fixes and branch renaming only. On 2026-10-07, explicit approval was subsequently received to commit, push, create an EUR-21 PR and post a Linear evidence comment. Merge, issue state changes and Done remain unauthorized.

### Findings and repairs

1. Creator deletion cascaded to communities, contrary to archive/retention rules. Use a non-null restrictive creator FK. Profile deletion locks the user and rejects ownership of any community before logout/deletion; users without communities retain the original deletion flow. Update the profile copy. Ownership transfer/account anonymization is not implemented.
2. Slug `create` collided with the static creation route. Normalize ASCII slugs before validation, reject the reserved slug (including uppercase variants), and validate malformed payloads before database queries.
3. Validator-only uniqueness could race and return HTTP 500. Keep the database UNIQUE constraint and translate the losing duplicate insert into validation feedback. The regression simulates an insert between validator read and request write, not a true parallel load benchmark.
4. Private route denials returned 403 while absent resources returned 404. Use policy `denyAsNotFound` for unauthorized/inactive view/update requests and verify that private metadata is absent. This does not prove timing-equivalence or eliminate global slug-availability inference.
5. The owned-community list was unbounded. Paginate 12 per page with deterministic ordering, keep owner scoping and omit dashboard links for inactive communities.
6. Explicit private/access/state defaults and lifecycle constraints were missing from the schema. Add PRIVATE-only visibility, FREE/PAID access-mode and ACTIVE/SUSPENDED/ARCHIVED state enums, with defaults in the schema/model. Exclude ownership/visibility/access/state from mass assignment; request fields cannot override them. The current UI creates FREE communities only; paid configuration/activation remains later work.
7. The old policy offered hard-delete permission without an implemented archive action. Hard-delete permission is denied; an explicit archive state transition is separate future work.

### Verification and safety

- Test guard in `tests/TestCase.php::setUpTraits()` was checked before RefreshDatabase. All feature tests use isolated MySQL scool_test; no development reset, queue flush or shared Mailpit deletion.
- Original target tests passed but added regressions exposed 11 failures (18 passed). These included reserved/Unicode slug handling, duplicate race HTTP 500, 403 enumeration, pagination and ownership-deletion behavior.
- Initial corrected target run: 29 tests / 163 assertions PASS.
- Expanded recheck: CommunityBaselineTest + ProfileTest, 38 tests / 203 assertions PASS, including model guarding, DB public-visibility rejection and malformed input.
- One full-suite attempt exposed duplicated newly added test declarations; those were removed and PHP syntax rechecked. Another full suite exposed logout-after-delete recreating a remembered user. Framework source confirmed remember-token rotation saves the model; logout now occurs before deletion inside the locked transaction. Existing non-owner account-deletion tests now pass without weakening assertions.
- Local development migration was Pending before audit; after the local runtime configuration guard passed, only `2026_10_07_004207_create_communities_table.php` was applied. It created the new table without changing existing users or resetting any data. Do not rewrite this migration after application/publication; subsequent schema changes require forward migrations.
- Final complete Verify PASS: `bash scripts/verify-fresh-setup.sh --mode verify`, exit 0 in 46 seconds on HEAD `3b007b61c4a8bcf07a637c114e9db3c44b5e6711` plus the current dirty audit diff. Composer validate, Pint 63 files, 85 tests / 397 assertions, live Mailpit SMTP and production Vite build (59 modules) passed. No skipped integration was counted as success.
- Final queue marker `EUR20-smoke-f5f91fac-42fd-4703-baa5-3ee0ddd7ea05`: pending=0, failed=0, handler=1. This is local evidence, not CI, publication or a second independent host.

### Handoff and limits

- Flow: authenticated request -> validate normalized input -> derive owner via createdCommunities relationship -> constrained private/FREE/ACTIVE record -> creator-only policy and Blade rendering. Update accepts name/description only.
- Trade-offs: restrictive ownership blocks account deletion until retention is resolved; MySQL enums require migrations for new values; globally unique custom slugs expose availability to authenticated creation attempts. No package, lockfile, stack or payment-provider change.
- Changed boundaries include ProfileController/profile deletion copy because the new creator FK would otherwise destroy tenant data. Architecture section 9 records the schema/retention decisions. AGENTS/AI_WORKFLOW records neutral future branch naming.
- Member access, invitation lifecycle, protected cover/media, paid access configuration, full tenant isolation and manual cross-browser/mobile UI checks remain unverified or out of this issue. Creator-only UI scaffolding does not complete EUR-22 or M2.
- Previously recorded dependency-audit findings and EUR-20 independent-host evidence remain open; this audit does not implement EUR-26 or complete M1.
- Next: publish the approved EUR-21 branch and attach its exact commit/PR evidence to Linear, without changing status. Independent review/required checks and acceptance still gate merge and downstream readiness; an open PR is not a merged dependency.

### Publication preflight (2026-10-07)

- Re-read live EUR-21 AC/status, source and diff. Linear still reports In Progress; no status update is authorized. Fetched origin/main is `3b007b61c4a8bcf07a637c114e9db3c44b5e6711`, matching the current parent HEAD. No existing open PR was returned for this repository at preflight.
- Re-ran `bash scripts/verify-fresh-setup.sh --mode verify`: exit 0 in 50 seconds; 85 tests / 397 assertions, Pint 63 files, Composer validation, live Mailpit integration, Vite production build and structural agent-contract checks PASS.
- Queue marker `EUR20-smoke-3f60fbcc-b830-49b2-b6a1-55b2c57b248e`: pending=0, failed=0, handler=1. Tests used guarded scool_test; no development migration/reset, shared inbox deletion, queue flush or volume deletion was performed in this preflight.
- Publication allowlist: the EUR-21 model/controller/policy, User relationship, migration/factory, four community views/navigation, Profile deletion safeguard/copy, CommunityBaselineTest, neutral branch rules, architecture note and this checkpoint. EUR-22/EUR-23 sections below are planning handoffs only, not implementation or AC completion.
- `git diff --check` and a scoped high-confidence secret-pattern scan of the 19 publication files passed. Personal directories/notebook remain ignored and absent from the index; this is not a full repository-history secret audit.
- Results above were obtained on the parent HEAD plus the pending reviewed diff. The final immutable commit/PR and remote check evidence must be recorded in Linear/GitHub after creation. No CI success, second independent host, full M2 isolation, owner acceptance or merge is implied.

## EUR-23 - historical implementation handoff (2026-10-07)

Historical planning only. The current implementation and receiving-agent handoff are in the M2 run section at the end of this file.

### Contract and readiness

- Live AC: [EUR-23](https://linear.app/eurusdevsec/issue/EUR-23/create-invitation-and-membership-schema-and-states), read 2026-10-07. Backlog; parent EUR-7; M2; blocked by EUR-21 and blocks EUR-24. This handoff does not change Linear metadata or certify readiness.
- AC: invitation/membership migrations and factories; defined membership/invitation states; database constraints preventing duplicate memberships; passing state-transition tests.
- Verified checkout: `eur-21-community-baseline`, HEAD `3b007b61c4a8bcf07a637c114e9db3c44b5e6711`, with uncommitted EUR-21 implementation/audit changes. Preserve all of them. No EUR-23 source currently exists. Publish/review the EUR-21 baseline before creating a separate `eur-23-membership-states` branch; do not switch branches carrying its dirty diff or combine issues in one PR.
- Recommended implementation order: EUR-23 before EUR-22, because the latter needs real membership state to satisfy member access AC. This is a technical sequencing recommendation, not a newly added Linear dependency or permission to start multiple implementation issues at once.

### Outcome and scope

Create the domain foundation for invitation-based free/paid access, without exposing join, acceptance, moderation or payment endpoints.

- Add `CommunityInvitation` / `community_invitations` and `CommunityMembership` / `community_memberships`, factories, community/user relationships, and small explicit state guards/transitions appropriate to architecture sections 6-9.
- Membership states: `PENDING_PAYMENT`, `ACTIVE`, `SUSPENDED`, `REMOVED`, `LEFT`. Enforce a real UNIQUE `(community_id, user_id)` constraint, foreign keys and useful lookup indexes. Never use an access-granting default or duplicate rows to represent rejoining.
- Invitation states: `PENDING`, `ACCEPTED`, `REVOKED`, `EXPIRED`; normalized email, community and inviter references, a unique token hash, expiry and acceptance/revocation timestamps. Raw random tokens must not be persisted, serialized into model output, recorded in this checkpoint or logged. Check the clock when deciding validity; do not rely on a scheduler updating EXPIRED.
- Terminal invitation states cannot be reopened; resend means a new invitation/token. Expired pending records cannot be accepted. Follow the documented membership transition graph; retry/failure does not activate paid membership. REMOVED/LEFT do not permit automatic reactivation.
- Before implementation, report the proposed field/default/FK/delete rules and transition API. Preserve retention semantics and assess their effect on ProfileController deletion; do not silently cascade tenant records, weaken guards or invent a full history subsystem. Escalate unresolved cross-module decisions.
- No new dependencies; use forward migrations, not edits to the applied communities migration. HTTP invite creation/accept/revoke and transactional membership activation belong to EUR-24. Verified payment activation belongs to billing. State primitives are not proof of either workflow.

### Verification and handoff

- Use guarded MySQL `scool_test`; test actual UNIQUE/FK/state constraints, not only validator rules. Cover same user in two communities, duplicate pair rejection, factories/relations, invalid states and unauthorized attribute injection where relevant.
- Test allowed/forbidden transitions and terminal invitation behavior, including expiry boundaries, repeated calls, normalized email and token-hash handling. Record clock semantics and unimplemented transactional workflow checks honestly.
- Run focused tests, then `bash scripts/verify-fresh-setup.sh --mode verify` with Vite stopped and the stack running. Verify does not apply new migrations to development; report a separate guarded migration step if local manual testing needs it. Never reset development data or edit an applied migration to recover a failure.
- Return the exact branch/HEAD/diff, AC mapping, schema/transition decisions, commands/results and limits in this section. Do not commit, push, create/merge a PR or change Linear state. Publication and independent audit remain separate actions.

## EUR-22 - historical implementation handoff (2026-10-07)

Historical planning only. The current implementation and receiving-agent handoff are in the M2 run section at the end of this file.

### Contract and readiness

- Live AC: [EUR-22](https://linear.app/eurusdevsec/issue/EUR-22/implement-private-community-routes-and-dashboard), read 2026-10-07. Backlog; parent EUR-6; M2; blocked by EUR-21 and blocks EUR-24.
- AC: scoped routes/dashboard; guessed slug/ID must not expose metadata; protected cover image; passing negative authorization tests.
- The verified EUR-21 dirty baseline already has CommunityController, CommunityPolicy, slug routes, owner-filtered pagination, navigation and four Blade views. Reuse this work; do not recreate it or treat it as completed EUR-22. Its current policy/list are creator-only and there is no protected cover implementation.
- Start on `eur-22-private-dashboard` from an accepted EUR-21 baseline plus the reviewed EUR-23 membership contract. If either baseline is unpublished/unavailable, report the blocker rather than making fake membership checks or contaminating the EUR-21 diff. Record the actual revision at start.

### Outcome and scope

An authenticated user can find and open only the private communities they own or actively belong to, including protected cover delivery. No public discovery or self-join.

- Complete the paginated My Communities list and community workspace using Blade/Tailwind/Alpine/Vite. Preserve existing route names where possible. Propose any `/dashboard` navigation change first; do not invent Feed/Classroom/Calendar CRUD in this issue. Keep clear empty, validation and denied states; do not copy React or the mentor demo backend.
- List only permitted communities; creator management may show their own inactive status without opening internal content. ACTIVE members can view an ACTIVE community. Non-owner membership of any other state grants no internal access. Creator access is derived from ownership; fixture display names and a selected client/session community are not roles.
- Split policy read and management permissions: opening view/media to ACTIVE members must NOT open edit/update/upload permissions. Updates remain creator-only on ACTIVE communities. Deny missing, unauthorized and inactive internal resources without exposing metadata; retain 404 privacy behavior and no blanket Platform Admin bypass.
- Resolve tenant context from `/communities/{community:slug}/...`, authorize every request, and scope any nested lookup to the authorized parent. Do not add a global mutable current-tenant shortcut or accept ownership/tenant/state/path from request fields.
- Add cover upload/replacement and an authorized delivery route. Real MIME validation: JPEG/PNG/WebP only, maximum 2 MB, generated server filename, escaped UI. Store under private storage, never a public disk/symlink or public asset path. Derive the served path from the authorized community record, never caller-supplied filesystem input.
- Use a forward migration for a nullable cover reference. Handle failed writes/DB updates without corrupting the existing cover; clean replaced files only after DB success. Prevent cross-community path access and stale authorization through caching. Use private/no-store delivery and no MIME sniffing; do not treat an unauthenticated signed link as equivalent to current membership authorization.
- Keep invitation/payment workflows, access-mode pricing configuration, membership-management endpoints and complete EUR-25 isolation scope separate. Do not relax existing EUR-21 tests to make the expanded view policy pass; extend them with real member fixtures and creator-only write assertions.

### Verification and handoff

- Tests: visitor redirect without metadata; owner access; ACTIVE member read but denied edit/update/upload; outsider/other creator/cross-tenant member 404; missing/guessed slug/ID; every non-active membership state; suspended/archived community; paginated list and no other tenant names/content.
- Cover tests: correct authorized response, no cover fallback, denied direct route, rejected oversized/invalid/SVG payloads, private storage, generated path, replacement/failure preservation and no publicly reachable original. Use isolated fake disks and synthetic records, never delete real uploaded files.
- Verify state changes take effect on subsequent requests and selected-community switches do not carry permission/data across two tabs or direct URLs. Keep UI/manual checks distinct from automated evidence.
- Run focused tests, full local Verify and manual desktop/mobile navigation/upload checks. Report exact results/revision; passing previous EUR-21 tests is not current evidence and is not CI.
- Update only this issue checkpoint with changed files, flow, policy/storage trade-offs, AC evidence and unverified cases. Stop for audit; no autonomous commit/push/PR/merge, Linear Done or new dependencies.

## M2 implementation run - EUR-23 / EUR-22 / EUR-24 / EUR-25 (2026-10-07)

- Current request authorizes implementing the remaining M2 slices, fixing findings and reporting results. It does not authorize publication, merge or Linear state changes. The older single-issue handoffs above remain historical.
- Live AC read for EUR-6/7 and EUR-22 through EUR-25; their Linear review/acceptance gates remain open. Start: clean `eur-23-m2-private-core`, HEAD `c50a8169e029254b6bfd18396f7cdd0c70955fca` (merged EUR-21). No existing EUR-23 source or unrelated dirty edits.
- Plan: forward membership/invitation/cover migrations; guarded models/factories/state primitives; server policies and paginated permitted-community dashboard; private cover upload/delivery; creator invitation create/revoke and verified-email acceptance; ID-only email job; complete boundary tests. No new packages or M3 features.
- Retention: restrictive foreign keys preserve membership/invitation records; extend profile deletion feedback rather than silently cascading community records. Read rights never grant creator writes.
- Email delivery: queue only invitation ID; generate the random token inside the worker, store only its hash and send via SMTP. Raw tokens are confined to the recipient's email and the in-memory acceptance form; they are not persisted in job payloads/models, flashed to sessions, documented or logged. Successful delivery is recorded; failures leave a retryable invitation without granting access.
- Paid access remains fail-closed until billing exists. Acceptance requires verified email and a live FREE invitation. Replayed tokens cannot restore or duplicate membership. LEFT rejoin needs a new invitation; SUSPENDED/REMOVED cannot be reactivated through acceptance.
- Verification uses guarded scool_test only, fake private disks and unique Mailpit recipients. Current results and remaining gates are recorded below. No development reset, volume deletion, shared inbox cleanup or queue flush.

### Receiving-agent handoff - finish verification, do not rebuild (2026-10-07)

The latest request stops implementation here and transfers the remaining work for a later independent audit. Preserve the existing diff; EUR-22 through EUR-25 already have implementation. Do not start M3 or replace the stack/UI framework.

**Current checkout**

- Branch: `eur-23-m2-private-core`; current HEAD: `ccd05376bc69894b04fd08207b3de9295b3954b1`; all M2 implementation remains uncommitted, including untracked files.
- HEAD changed during this run from `c50a8169e029254b6bfd18396f7cdd0c70955fca` to `ccd0537` (`update md file`). That intervening commit changes only `docs/00_PROJECT_OVERVIEW.md` and `docs/03_DELIVERY_PLAN.md`; preserve it. It was not created by this implementation run.
- Re-read root AGENTS/AI_WORKFLOW, this active section, Git status/diff and live Linear AC before editing. Older "not started" statements above describe historical snapshots, not current source.

**Implemented AC mapping**

| Issue  | Current implementation                                                                                                                        | Main evidence                                                                           |
| ------ | --------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------- |
| EUR-23 | Membership/invitation forward migrations, guarded models, factories/relations, restrictive FK/unique/enum constraints, locked state primitive | `MembershipInvitationSchemaTest`                                                        |
| EUR-22 | Paginated owned/ACTIVE-joined dashboard, separate creator writes/member reads, private cover upload/replacement/delivery                      | `PrivateCommunityAccessTest`, real desktop/mobile UI                                    |
| EUR-24 | Creator create/revoke, seven-day email-bound invitation, verified-email single-use free acceptance, ID-only SMTP job                          | `CommunityInvitationFlowTest`, real MySQL queue/Mailpit integration, browser acceptance |
| EUR-25 | Community policy, accessible query scope, nested invitation scoped binding, transaction locks, cross-tenant/state/replay/race tests           | All four new M2 test classes; real browser 404 isolation                                |

**Diff inventory**

- New: `app/Actions/Communities/{CreateInvitation,AcceptInvitation,RevokeInvitation}.php`; invitation/cover controllers; membership/invitation models and factories; `SendCommunityInvitation`; `CommunityInvitationMail`; three `2026_10_07_02000*` migrations; invitation management/acceptance/mail views; four M2 feature-test classes and `tests/Fixtures/invitation-process.php`.
- Modified: Community/User models, CommunityPolicy, CommunityController, ProfileController, routes, filesystem config, bootstrap, community/profile Blade views, FrontendScaffoldTest, README, architecture and this checkpoint. The frontend regression now checks the real My Communities dashboard instead of the removed starter greeting.
- No new dependency or lockfile change; no React, public discovery, shared invite links, membership-management UI, M3 feature CRUD or payment activation.

**Flow and decisions to retain**

- Creator creates private FREE community -> invitation queued by ID -> worker generates token, stores SHA-256 hash and sends SMTP -> verified matching email explicitly POSTs acceptance -> membership ACTIVE and invitation ACCEPTED commit together -> dashboard/workspace access.
- Other tenants, non-ACTIVE memberships and inactive communities cannot open internal routes. ACTIVE members cannot edit settings, upload covers or create/revoke invitations. Ownership comes from server records; selected session tenant and persona names are not permission grants.
- Invitation fragment keeps raw token out of access logs; Alpine removes it from the address bar after reading it. Validation does not flash `token`. Bootstrap enables `zend.exception_ignore_args` because the inspected PHP default was off; mail failures suppress sensitive chained exceptions. Do not undo these protections.
- Cover disk is `storage/app/community-media`, outside both public and the default signed-local-storage root. JPEG/PNG/WebP only, max 2 MB and 4096 pixels per side. Generated tenant-scoped paths, authorized no-store/nosniff response, rollback cleanup and safe old-file replacement are implemented.
- SMTP inside the locked delivery transaction is a bounded trade-off, not exactly-once delivery/outbox. A commit failure after sending can cause a replacement email on retry. Terminal records are never reopened. Pending delivery enqueue failures need explicit retry/revoke-and-reissue; do not silently grant access.
- PAID remains fail-closed until Billing. LEFT can rejoin only with a new invitation; SUSPENDED/REMOVED/PENDING_PAYMENT cannot bypass controls via acceptance. Member-management endpoints are a later issue.

**Verified evidence**

- Latest `bash scripts/verify-fresh-setup.sh --mode verify`: exit 0, 113 seconds, HEAD `ccd05376bc69894b04fd08207b3de9295b3954b1` plus dirty M2 diff; **117 tests / 786 assertions PASS**, Pint 82 files PASS, Composer validation and Vite production build (59 modules) PASS. No skipped integration was counted as success. This is local evidence, not CI or a published revision.
- Real default-daemon queue probe: `EUR20-smoke-a0de1797-f89d-4358-832d-1596bbcf8b70`: pending=0, failed=0, handler=1.
- Setup guard regressions: `bash scripts/test-setup-verification.sh` -> 19 PASS.
- Agent contract verification: `bash scripts/verify-agent-contract.sh` -> 11 shared files PASS.
- Test concurrency and teardown fixes in `InvitationProcessIntegrationTest`:
    - Replaced `DatabaseMigrations` with `RefreshDatabase` and empty `$connectionsToTransact = []` so fixtures commit and remain visible to child processes without per-test schema rollback. This is a test-isolation choice, not proof of a Laravel migration bug: the inspected `DatabaseMigrations` teardown also resets `RefreshDatabaseState::$migrated`. The initially added broad table cleanup was unsafe and is superseded by the scoped cleanup recorded in the audit below.
    - Added stdout buffer flushing (`@ob_flush(); flush();`) in `tests/Fixtures/invitation-process.php` after `echo "READY\n"`.
    - The initial readiness fix checked cumulative stdout before calling `waitUntil(...)`; this narrowed but did not eliminate the window in which output could be drained before callback registration. It is superseded by bounded cumulative-output polling recorded below.
- Negative tests include duplicate/FK/enum constraints, stale state, wrong/unverified email, expired/revoked/replayed tokens, paid fail-closed/no token flashing, cross-tenant nested IDs, revoked access, throttling, atomic acceptance rollback, cover MIME/size/dimension rejection, DB/storage failure preservation and cross-tenant cleanup protection.
- Local configuration guard passed; only the three pending forward migrations were applied to development. Existing users/data were not reset. Worker was restarted, then gracefully reloaded after the bootstrap security change.
- Browser checks passed: create private community; default worker delivery; member login, email-fragment removal and explicit acceptance; creator cover upload with a loaded 1280x720 JPEG; ACTIVE member cover view; member creator-settings URL 404; second unjoined tenant URL 404; member dashboard omits that second tenant; mobile 390x844 navigation opens and no horizontal overflow. Temporary viewport override was reset.
- Two synthetic local communities remain for inspection: `m2-private-demo-20261007` (demo member accepted) and `m2-other-tenant-20261007` (not joined by that member). Their cover is a generated synthetic UI screenshot, not a personal upload. No existing account/password was overwritten; no inbox was deleted.
- Token-free screenshot artifacts are local-only under `C:/Users/ACER/.codex/visualizations/2026/09/23/01a0cbfd-4dd5-7251-97aa-e2bea2a13152/` (`m2-private-core-member.jpg`, `m2-private-core-mobile.jpg`). They are not repository/publication prerequisites.

**Completed bounded work & audit status**

1. Reconciled full dirty/untracked diff against Linear EUR-22/23/24/25 and parent EUR-6/7 AC. Verified security, authorization, failure paths, and tenant isolation; no M3 scope or new packages added.
2. README origin clarified: harmonized application and login references to `http://localhost:8080` (matching `APP_URL`). Explicitly documented browser cookie isolation between `localhost` and `127.0.0.1` so Mailpit invitation link acceptance retains active login sessions.
3. `CreateInvitation` and `AcceptInvitation` normalize email using `strtolower(trim($email))`. ASCII trimming and case-insensitive matching are tested. This is not an enforced ASCII-only validation contract or evidence of internationalized-email support; Unicode/IDN normalization remains unverified and no identity-contract expansion was made in this audit.
4. Full verification rerun with Vite stopped: Pint 82 files PASS, 117 tests / 786 assertions PASS, Vite production build PASS, real queue smoke PASS, 19 setup guard checks PASS.
5. All changed/untracked files preserved and prepared for independent audit. No autonomous commit, push, PR creation/merge, or Linear status change performed.

Independent reviewer confirmation, required CI/publication and formal milestone acceptance remain open. M1's deferred EUR-26 CI work and independent-host setup evidence are not completed by this local M2 run. Do not mark M2 Done from the test count alone.

### Independent audit and bounded fixes (2026-10-07)

**Readiness and authority**

- Outcome: audit the existing EUR-22/23/24/25 diff and repair confirmed findings; preserve implementation rather than rebuilding it. No publication or Linear mutation is authorized in this audit.
- Current branch/HEAD: `eur-23-m2-private-core` / `ccd05376bc69894b04fd08207b3de9295b3954b1`, plus the uncommitted M2 files. The intervening overview/delivery commit and unrelated local files are preserved.
- Live Linear AC and relations were read for EUR-22/23/24/25 and parents EUR-6/7. At this check EUR-23 was In Progress, EUR-22/24/25 Backlog, EUR-6 Todo and EUR-7 Backlog. These are observed metadata, not acceptance decisions or status updates.
- Review covered scoped queries/policies, invitation create/delivery/accept/revoke, private cover storage/delivery, retention, actual MySQL constraints and test isolation. No new dependencies, payment activation or M3 features were added.

**Confirmed findings and repairs**

1. Process-test teardown deleted entire tables after opting out of connection transactions. Replace it with cleanup of owned community/user IDs and UUID queue names, on a captured connection guarded by configured and actual MySQL `scool_test`. Stop tracked child processes before cleanup; always invoke parent teardown. A regression verifies unrelated memberships, invitations, communities, users and queue jobs survive this cleanup. This does not make parallel full-suite runs against one shared test database safe.
2. The output check before `waitUntil` still left a readiness race. Use bounded polling of cumulative stdout with process liveness/timeout checks, retaining child stdout flushing. Regressions cover already-buffered and late output; the real two-process acceptance test still requires exactly one success and one rejection.
3. README used origin-scoped cookie wording and linked Dashboard to `/communities`. Correct the cookie host boundary, use `/dashboard`, and instruct users to log in on the configured `APP_URL` host. This changes documentation, not the private local environment.
4. Earlier checkpoint text overstated the migration root cause and ASCII-only email enforcement. Correct those claims. Add regressions for trimmed/mixed-case ASCII duplicate invitations and fresh locked user email/verification after a stale authenticated model. Internationalized-email normalization remains unverified rather than advertised as supported or rejected.

**Verification and remaining gates**

- Focused command: `docker compose -p scool exec -T app php artisan test --compact --filter='InvitationProcessIntegrationTest|CommunityInvitationFlowTest'` -> 18 tests / 296 assertions PASS, 37 seconds. Scoped Pint fixes applied only to the two edited test files.
- Setup guard regression command: `bash scripts/test-setup-verification.sh` -> 19 PASS; no Docker/data changes from these synthetic fixtures.
- Full command: `bash scripts/verify-fresh-setup.sh --mode verify` -> exit 0, 120 seconds on the HEAD above plus dirty M2 source; **121 tests / 820 assertions PASS** (test duration 91.81 seconds), Pint 82 files, Composer validation, Vite production build 59 modules, runtime/HTTP and the 11-file shared agent contract PASS. No integration skip was counted as success. Documentation-only evidence edits followed this run; `git diff --check` passed. This is local verification, not CI or fresh-bootstrap/independent-host proof.
- Default-daemon queue marker `EUR20-smoke-9f79e6fd-4bec-4a4b-94f5-66e6e32bc11c`: pending=0, failed=0, handler=1. Previously recorded 117-test results and browser checks are historical; browser behavior was not manually re-run in this follow-up audit.
- No development reset/migration, queue flush, shared inbox deletion or volume deletion was performed in this audit. Tests retain the pre-RefreshDatabase guard, fake cover disks and unique Mailpit recipients.
- An unrelated untracked local script contains a hardcoded external credential. It was not executed, edited or staged and is excluded from the M2 publication allowlist. Owner credential revocation/rotation is required; no repository-history secret-cleanliness claim is made.
- Paid access stays fail-closed. SMTP delivery remains at-least-once, not an outbox/exactly-once guarantee. Deferred dependency-security findings, EUR-26 CI and independent-host setup evidence remain open.
- Next action: review the bounded fixes and evidence, then request publication separately if appropriate. Merge/reviewer confirmation and current AC still gate issue/milestone acceptance and the M3 baseline.

### M2 publication preflight (2026-10-07)

- Explicit current authorization covers commit, push, a new PR and evidence comments. Merge, issue status changes and Done are not authorized.
- Branch: `eur-23-m2-private-core`; pre-commit HEAD: `ccd05376bc69894b04fd08207b3de9295b3954b1`. Fetched `origin/main` is `e9000b06c4c8414f58538dcb43daad9cc6b7341e`, with an identical source tree at preflight. PR #8 merged only the overview/delivery documentation; the M2 implementation still requires a new PR.
- Live EUR-22/23/24/25 and parent EUR-6/7 contracts were re-read. Publication covers their existing private-core implementation; review/acceptance gates remain open.
- Publication allowlist: 38 M2 source, migration, view, test and shared-documentation files. No private configuration, environment file, credential script, generated asset or lockfile is included. The unrelated local script was deleted by its owner and its absence was verified; deletion does not prove external credential revocation, which remains unverified.
- Final `bash scripts/verify-fresh-setup.sh --mode verify`: exit 0 in 113 seconds on the pre-commit HEAD plus this reviewed diff. **121 tests / 820 assertions PASS** (85.01 seconds); Pint 82 files, Composer validation, Vite production build 59 modules, runtime/HTTP and the 11-file shared contract PASS.
- Default-daemon queue marker `EUR20-smoke-7f8d8fb0-1294-4ce2-bccc-92c3f6455f88`: pending=0, failed=0, handler=1. Tests used guarded MySQL scool_test and UUID mail recipients; no development reset/migration, volume deletion, inbox deletion or queue flush occurred.
- `git diff --check` and a scoped high-confidence secret-pattern scan of the allowlist passed. These are bounded checks, not a full history/secret audit. Only this evidence checkpoint changed after the test run.
- No CI workflow exists in the current checkout; local success is not a CI result. Deferred EUR-26, dependency-security decisions and independent-host setup proof remain open. The source commit and PR evidence will be linked in Linear; acceptance still requires review and merge.

## Local IPv4 URL consistency correction (2026-10-07)

- Contract: restore the agreed local browser/email origin `http://127.0.0.1:8080`. The earlier README change followed the stale `.env.example` localhost default instead of correcting it; those historical localhost recommendations are superseded by this section.
- Start: clean `eur-23-m2-private-core`, HEAD `215c9919a777506616dcd33764462bc8988cafa4`. This is a bounded runtime/documentation correction, not a new Linear issue or M2 status change.
- Shared scope: `.env.example`, application/SMTP-domain URL fallbacks, README, a default-template regression and the real invitation-worker URL assertion. Compose IPv4 bindings and existing HTTP probes already match; no changes are needed there.
- Explicit follow-up authorization covers only APP_URL in the private local `.env`, config cache clear and queue restart. A patch attempt reported a missing old line; reinspection found APP_URL already at the target value. The pre/post fingerprint of all other environment entries is identical. No overwrite/replacement was performed to resolve the mismatch; preserve all other keys/settings/data. The private file remains ignored and must not be staged.
- Browser/login/email links must use one host; cookies from localhost do not authenticate the IPv4 host. Previously delivered invitation links retain their previous host. Reopen/login on the canonical host and revoke/reissue pending invitations where needed; never rewrite persisted tokens or grant access during migration.
- Keep `DB_HOST=mysql` and `MAIL_HOST=mailpit`. Service-to-service traffic uses Compose names; 127.0.0.1 inside an app container is not the MySQL/Mailpit service.
- Rationale: a literal IPv4 entrypoint avoids hostname/address-family selection and matches the configured loopback listener. No benchmark in this correction establishes DNS/IPv6 as the cause of PHP slowness or proves a speed-up. APP_URL supplies generated absolute URLs; it does not choose the PHP-FPM or database transport.
- Verification plan: confirm private config preservation, reload/restarted worker settings, built-asset HTTP reachability, scoped tests and full local Verify. No database/volume reset, publication or Done authorization.

### Correction verification

- Private APP_URL is confirmed as `http://127.0.0.1:8080`; the pre/post fingerprint of every other environment entry matches. The private file is ignored and absent from the index; no key regeneration or environment replacement.
- `php artisan config:clear` and `docker compose -p scool restart queue` completed. Sanitized fresh boot checks in app/queue both report the IPv4 app URL, DB host mysql and SMTP host mailpit. The IPv4 login endpoint returns HTTP 200.
- Scoped Pint PASS (four files); `php artisan test --compact --filter='DockerEnvironmentTest|InvitationProcessIntegrationTest'` PASS: 8 tests / 48 assertions, 25.73 seconds, including real SMTP delivery with the IPv4 invitation origin asserted without exposing the token in failure output.
- Setup guard regressions: 19 PASS. Shared agent structural contract: 11 files PASS. Active template, URL fallbacks and README contain no old localhost:8080 origin. Historical checkpoint statements are retained but superseded above.
- Full `bash scripts/verify-fresh-setup.sh --mode verify`: exit 0 in 139 seconds on HEAD `215c9919a777506616dcd33764462bc8988cafa4` plus this dirty correction; **122 tests / 824 assertions PASS**, test duration 93.30 seconds, Pint 82 files, Composer validation, Vite build 59 modules, runtime/HTTP and live queue/Mailpit PASS. Queue marker `EUR20-smoke-90d2819c-7600-4b67-8004-975c1542ea95`: pending=0, failed=0, handler=1. Only this evidence checkpoint was edited after the run; final diff whitespace check passed.
- No staging, commit, push, PR, merge or Linear mutation was performed. Seven shared files remain modified; `.env` stays private. No development DB reset/migration, queue flush, inbox deletion or volume deletion. These checks are not a performance benchmark, CI result or production-readiness claim.

### Acceptance clarification (2026-10-07)

- The local browser/email origin is confirmed as `http://127.0.0.1:8080`. Preserve the existing IPv4 correction; internal DB/SMTP hosts remain `mysql` and `mailpit`.
- User-reported manual evidence: the community cover is now visible after the upload. This confirms the reported display symptom is resolved; it does not independently reverify member/non-member authorization.
- Cover upload and metadata updates are separate forms: `Upload cover` persists the image; `Save Changes` persists only the name/description. Selecting a file alone is not a completed upload.
- This confirmation does not authorize commit, push, PR, merge or Linear Done. Publish the reviewed IPv4 correction and reconcile M2 acceptance metadata only after explicit authorization. No tests were rerun for this checkpoint-only clarification.

### IPv4 publication preflight (2026-10-07)

- Subsequent explicit authorization covers commit, push and a focused PR for the seven-file IPv4 correction. Merge and Linear status changes remain unauthorized.
- Fetched `origin/main` is `c229e74331ada9b40f7a134d25093dc4d0e3a780` (merged M2 PR #9), with a source tree identical to the previous `215c991` baseline. The preserved correction now sits on `eur-22-local-ipv4-origin`, based on that main revision; no unrelated files are included.
- Fresh scoped verification: `docker compose -p scool exec -T app php artisan test --compact --filter='DockerEnvironmentTest|InvitationProcessIntegrationTest'` PASS: 8 tests / 48 assertions, 17.17 seconds, including the worker invitation's IPv4 origin. Tests use guarded MySQL `scool_test` and unique Mailpit recipients; no shared inbox or development data was deleted.
- `bash scripts/verify-agent-contract.sh --require-tracked` PASS: 11 shared files. Diff whitespace check PASS; private `.env` remains ignored and untracked. The earlier 122-test full Verify is historical evidence for the same application/test diff, not a new full-suite or CI run.
- GitHub CLI authentication reports an invalid token. Normal Git push and the existing GitHub connector will be attempted without extracting, copying or exposing credentials; report any publication step that remains unavailable.

## EUR-8 PR #14 remediation (2026-10-08)

- Authority/outcome: remediate the posted Feed findings and publish updates to the existing PR; live Linear EUR-8 is the AC source. Required approval still gates merge and Done.
- Baseline: `11c8420410b2ba39969251a83ba0e9c858a66ca2`, isolated branch `eur-8-feed-fixes`. The original checkout and its unrelated workflow edit remain untouched.
- Changes: current access is required before author/moderation permissions; author-only comment edit route/form/action; comments paginate 20/page and feed posts 15/page with timestamp/ID ties; authors are eager-loaded and feed threads are not loaded.
- Mutations lock community -> actor membership -> post -> comment, re-read retained/live state and reauthorize inside the transaction. This coarse per-community lock favors correctness over concurrent write throughput in the bounded MVP; no package/schema changes.
- Regression evidence: isolated PHP 8.2.34/PHPUnit 11.5.56, guarded MySQL `scool_test`; `php vendor/bin/phpunit --filter CommunityFeed --display-warnings` PASS, 23 tests/203 assertions. Includes inactive-author direct requests, edit ownership/validation, pagination, state change after preliminary authorization, and independent-connection lock-timeout probes for post/membership rows. The lock probe is not a production load/stress test.
- Quality/build: focused Pint PASS (8 files); whole-tree `pint --test` PASS (96 files); `npm run build` PASS (59 modules). No development DB reset, volume deletion or shared Mailpit inbox deletion.
- Full-suite first attempt: 145 tests/1013 assertions, three failures. Two require the missing runner `APP_NAME=S-cool`; one identifies the existing PHP exception-argument configuration (`zend.exception_ignore_args=0`). Explicit approval was obtained to add `docker/php/security.ini` to the shared image. Verify a newly built image; do not weaken the security assertion. Existing running containers are unchanged until rebuilt after merge.
- Follow-up security probe: the rebuilt image reports `zend.exception_ignore_args=1`; callable frames contain no arguments and no synthetic secret is retained. PHP still records include/require filenames. The security test now checks all callable frames and absence of the synthetic secret across the entire trace, rather than rejecting PHP's language-frame filename behavior.
- Final Feed verification on `scool-pr-review-fixed` (security.ini built into the image), synthetic key and sample `APP_NAME=S-cool`: whole-tree Pint PASS (96 files), `php vendor/bin/phpunit --display-warnings` PASS (145 tests/1028 assertions, exit 0). Agent contract PASS (11 tracked shared files); contract regressions PASS (16 cases); setup guards PASS (18 cases). Explicit approval covered publishing the missing workflow routing paragraph; the original checkout's edit remains untouched.
- Browser acceptance against an isolated loopback preview with synthetic `scool_test` data verified author comment editing and 20-plus-1 comment pagination. Narrow-screen post detail had no horizontal overflow; Feed navigation now stacks/wraps on small screens without importing Events routes into the standalone Feed branch. Shared navigation was also verified in the combined Events preview.
- Pending: PR review/approval and current-head CI. Browser acceptance is bounded, not an exhaustive device matrix; remediation publication does not establish Done.

## EUR-11 PR #15 remediation (2026-10-08)

- Authority/outcome: remediate the posted Events findings in the existing PR. Live Linear EUR-11 is the AC source; required approval still gates merge and Done.
- Baseline: `b86632a60251e8dce778e80fa5916bcb2fd62bb4`, isolated branch `eur-11-events-fixes`. Updated Feed commits are integrated without rewriting the existing PR history.
- Changes: an explicit UTC datetime cast keeps persistence/reload stable when the application timezone is not UTC. Strict local wall-time parsing rejects invalid dates, timezone gaps and ambiguous folds, including half-hour transitions; the UI requires another unambiguous time instead of silently selecting an offset.
- Meeting links accept only validated HTTP(S) URLs without userinfo; validation stops before URL parsing on non-string input. Create/update/cancel lock and reauthorize current community/event state; update rechecks cancellation after the lock, and cancel remains idempotent. No schema or dependency changes.
- Responsive changes stack/wrap Calendar headers, cards, meeting controls and Feed navigation at narrow widths. Browser acceptance against an isolated loopback preview using synthetic `scool_test` data verified author comment edit, 20-plus-1 comment pagination, event detail/timezone display, DST field errors without partial saves and no horizontal overflow at a 354px measured viewport. The normal browser viewport was restored.
- Browser limitation: the automation confirmation-dialog API timed out during synthetic cancellation; no successful browser cancellation is claimed. Automated cancellation/link-omission and race regression tests cover the server behavior. The disposable preview container was stopped without touching the development stack or volumes.
- Regression coverage: 7 additional Events tests cover create/update DST gaps/folds, strict input, credential/non-string URLs, UTC stability across application timezones, cast normalization and cancellation between preliminary authorization and locked update. Existing Events/Feed tests remain enabled.
- Verification environment: isolated rebuilt `scool-pr-review-fixed` PHP 8.2.34 image, sample application identity/key, guarded MySQL `scool_test`, shared Mailpit with unique test recipients. Whole-tree Pint PASS (105 files); full PHPUnit PASS (162 tests/1206 assertions); `npm run build` PASS (59 modules). No development database reset or shared inbox deletion.
- Evidence limits: this is bounded functional/regression verification, not production load testing or a fresh-machine setup proof. GitHub approval and current-head CI must be checked separately. Existing runtime containers need the updated image after an approved merge.

## M3 issue contract standardization (2026-10-07)

- Outcome/authority: clarify the existing EUR-8/10/9/11 descriptions and M3 exit criteria; establish one reusable issue contract for all participants. Authorization does not include application implementation, status changes, commit, push, PR or merge.
- Start: clean `eur-22-local-ipv4-origin`, HEAD `1b5f364ab3542de3ca78b716f188e89d2c016a03`. Actual M2 models, policies, routes and verification scripts were inspected; historical checkpoints are not current M3 implementation evidence.
- Canonical issue AC: live Linear EUR-8 (Feed), EUR-10 (Classroom), EUR-9 (Progress) and EUR-11 (Events). All four descriptions now specify outcome, scope, workflow/contracts, numbered AC, dependencies, security/failure cases, verification plan and Pending evidence. Re-fetch confirmed unchanged title, assignee, priority, estimate, due date, status, labels, project/milestone, attachments and relations; all remain Backlog. No sub-issues or false Feed-to-Events blockers were added.
- Confirmed product decisions: members need PUBLISHED course AND lesson; unpublish retains progress but blocks member reads/writes; MVP embeds accept only validated YouTube/Vimeo HTTPS URLs, not raw iframe/HTML; Calendar displays labelled event timezone; cancelled events remain visible but expose no member-facing meeting link. Product Scope and Domain Architecture record these decisions and bounded module/shared-navigation coordination.
- Reusable contract: the existing Issue description template and Issue quality gate in `docs/03_DELIVERY_PLAN.md` are canonical, routed from `AGENTS.md` and `docs/AI_WORKFLOW.md`. Use English/objective prose, concrete positive/negative AC, explicit decisions and evidence tied to revision/environment; metadata remains in Linear. No native Linear form template was created. Prior acceptance exceptions do not automatically waive future review/checks.
- Diff: shared entrypoint, workflow, Product Scope, Domain Architecture, Delivery Plan, this checkpoint, and the two existing agent-contract scripts. No application, dependency, runtime, environment or data changes. The draft sync-local script is outside this task and was neither changed nor executed.
- Verification: `bash scripts/verify-agent-contract.sh --require-tracked` PASS (11 shared files); `bash scripts/test-agent-contract.sh` PASS (16 disposable-fixture cases), including missing issue-template routing/gate/verification sections. `git diff --check` PASS before this checkpoint; repeat after its edit. Structural checks do not prove issue quality, agent compliance, publication or application behavior.
- Evidence limits/next step: M3 code, module tests, browser flows, integration and CI remain unverified/Pending, not completed. M3 target date remains 2026-10-06 and issue due dates 2026-10-07; description changes do not resolve the schedule risk. Review/publish these bounded shared-contract edits only after explicit authorization so other clones receive the rules. Before implementation, verify merged baseline and agree the narrow shared navigation/route names and concrete field/provider validation limits in the active issue checkpoint.

## EUR-8 - Community Feed baseline implementation (2026-10-07)

- Outcome: Implement end-to-end Community Feed (VS-04): post and comment CRUD, authorship policy, creator moderation, scoped route binding, pagination, soft deletes, and XSS protection.
- Branch: `eur-8-community-feed`, branched from HEAD `e2f55503eb5897aaf73f7055734a73c91e6876dd`.
- Implemented files:
    - Migrations: `2026_10_07_030001_create_posts_table.php`, `2026_10_07_030002_create_comments_table.php` (restrictive foreign keys, indexes, soft deletes).
    - Models: `Post`, `Comment`, updated `Community` and `User` with relationships.
    - Factories: `PostFactory`, `CommentFactory`.
    - Policies: `PostPolicy`, `CommentPolicy` (active member and creator access, author-only update, author/creator delete, denyAsNotFound privacy).
    - Controllers: `PostController`, `CommentController` (eager loading to prevent N+1, pagination of 15 per page).
    - Views: `resources/views/communities/posts/{index,show,edit}.blade.php`, updated `communities/show.blade.php`.
    - Routes: Scoped nested routes under `/communities/{community:slug}/posts/...`.
    - Feature tests: `tests/Feature/CommunityFeedTest.php` (14 tests, 53 assertions).
- Verification:
    - `docker compose -p scool exec -T app php artisan test --compact --filter=CommunityFeedTest`: 14 passed (53 assertions).
    - `docker compose -p scool exec -T app vendor/bin/pint`: 93 files PASS.
    - Full test suite: `docker compose -p scool exec -T app php artisan test --compact`: 136 passed (877 assertions).
- Status & Authority:
    - Implementation completed and verified locally on `scool_test`.
    - No commit, push, PR creation, merge, or Linear mutation performed. Awaiting review and authorization.

## EUR-11 - Community Events baseline implementation (2026-10-07)

- Outcome: Implement end-to-end Community Events (VS-07): event scheduling, update, idempotent cancellation, UTC storage with IANA timezone presentation, URL safety validation, and meeting link omission on cancelled events.
- Branch: `eur-11-community-events`, branched from `eur-8-community-feed` (`11c8420410b2ba39969251a83ba0e9c858a66ca2`).
- Implemented files:
    - Migration: `2026_10_07_040001_create_events_table.php` (restrictive foreign keys, UTC timestamps, IANA timezone string, status enum).
    - Model: `Event` with timezone conversion helpers (`localStartsAt`, `localEndsAt`) and status helpers; updated `Community` and `User` relationships.
    - Factory: `EventFactory` with scheduled and cancelled states.
    - Policy: `EventPolicy` (creator manage, active member view, denyAsNotFound privacy).
    - Controller: `EventController` (parses local wall time with IANA timezone, converts to UTC, validates end > start, enforces idempotent cancellation and rejects updating cancelled events).
    - Views: `resources/views/communities/events/{index,create,edit,show}.blade.php`, updated `communities/show.blade.php` and `communities/posts/index.blade.php` with Events navigation.
    - Routes: Scoped nested routes under `/communities/{community:slug}/events/...`.
    - Feature tests: `tests/Feature/CommunityEventTest.php` (10 tests, 67 assertions).
- Verification:
    - `docker compose -p scool exec -T app php artisan test --compact --filter=CommunityEventTest`: 10 passed (67 assertions).
    - `docker compose -p scool exec -T app vendor/bin/pint`: 99 files PASS.
    - Full test suite: `docker compose -p scool exec -T app php artisan test --compact`: 146 passed (944 assertions).
- Status & Authority:
    - Implementation completed and verified locally on `scool_test`.
    - No commit, push, PR creation, merge, or Linear mutation performed. Awaiting review and authorization.

## EUR-10 - Classroom publishing implementation (2026-10-08)

The submitted implementation and results below are historical author-reported evidence. The independent remediation checkpoint at the end of this section supersedes them for the current local diff; it does not establish publication or acceptance.

### Outcome and scope

- Outcome: Owning creator creates, edits, orders, previews drafts, and publishes courses, sections and lessons in an active private community. Active members can view courses and read lessons only when both the course and lesson are PUBLISHED. Unpublishing blocks member reads and progress mutations without deleting retained data.
- Scope:
    - Database: forward migrations, models, relations, factories for Course, CourseSection, Lesson.
    - Video Embeds: strict validation of HTTPS YouTube and Vimeo URLs only; secure server-side embed URL generation. Rejection of raw iframe, javascript/data schemes, and lookalike domains.
    - Reordering: transactional sibling reorder for courses, sections, and lessons.
    - Security/Authorization: Creator-only writes/preview; active member access predicate (`community->isActive()` && `membership->isActive()` && `course->isPublished()` && `lesson->isPublished()`). Strict server-side ancestry resolution (`community` -> `course` -> `section` -> `lesson`). Deny unauthorized or draft access with 404 (`denyAsNotFound`).
    - UI: Blade/Tailwind/Alpine views for classroom index, course outline, lesson view with responsive embed, and creator management forms/modals.
    - Tests: comprehensive feature test suite covering AC-01 through AC-08 on MySQL `scool_test`.

### Verification and results

- Branch: `eur-10-classroom-publishing`, based on `main` HEAD `e2f55503eb5897aaf73f7055734a73c91e6876dd`.
- Scoped Feature Tests: `php artisan test --filter=ClassroomPublishingTest` -> **8 tests / 106 assertions PASS** (15.18s).
    - AC-01: Creator course/section/lesson CRUD, draft preview, and persistence.
    - AC-02: Member read matrix (Course Draft + Lesson Published -> 404, Course Published + Lesson Draft -> 404, Both Published -> 200).
    - AC-03: Unpublish blocks access without data purge; republish restores access immediately.
    - AC-04: Negative authorization, outsider cross-tenant 404, ID tampering 404, and inactive community 404.
    - AC-05: Sibling reordering transactions with strict validation of foreign/duplicate IDs.
    - AC-06: Strict HTTPS YouTube/Vimeo validation, secure embed URL generation, rejection of XSS/iframe/schemes, and escaped text.
    - AC-07: Member count excludes draft lessons, zero metadata leak, and empty state rendering.
- Full Suite Verification: `php artisan test` -> **130 tests / 930 assertions PASS** (140.90s), zero failures or regressions.
- Code Style: Laravel Pint passed clean across all files (100 files checked).
- Agent Contract: `bash scripts/verify-agent-contract.sh` (PASS) and `bash scripts/test-agent-contract.sh` (16 PASS).

### Handoff contract for EUR-9 (Lesson Progress)

- Models & Relationships: `Course`, `CourseSection`, `Lesson` ready with forward migrations and factories.
- Access Predicate: Available on policies and models (`$course->isPublished() && $lesson->isPublished() && $community->isActive() && $membership->isActive()`).
- Nested Routes: Scoped under `/communities/{community:slug}/courses/{course}/lessons/{lesson}`.

### PR #17 independent audit and local remediation (2026-10-08)

- Authority/outcome: audit live EUR-10 AC and PR #17, fix bounded Classroom defects, and prepare for owner review. Commit, push, merge, external comments and Linear status changes are not authorized in this checkpoint.
- Start: clean `main`, HEAD `c8c280c38f06f1485d149b29f3f48313a06b025b`. Local branch `eur-10-classroom-audit` applies PR head `6f86a94aeb99e866f00995dd93a6887e974c56f0` without a commit. Five integration conflicts were resolved while preserving the merged Feed/Events routes, models, navigation and shared workflow contract. This local branch is based on main, not on the PR head; publishing to the existing PR requires a history-preserving integration, not a force push.
- Findings/remediation:
    - Creator UI lacked section edit and sibling reorder controls. All three reorder levels and section editing are now exposed. Native disclosure forms retain the relevant form, inputs and publication status after errors; malformed flashed arrays no longer crash the retry page.
    - Lesson validation allowed 500-character URLs while storage allowed 255. An additional forward migration extends storage to 500; existing migrations remain unchanged. Rollback refuses truncation of retained long URLs.
    - Hard-delete actions and cascade foreign keys could purge retained content. Permanent purge is explicitly excluded by EUR-10: delete actions now deny, delete controls are removed, and the forward migration changes hierarchy foreign keys to RESTRICT. Use unpublish to hide content; no progress implementation or actual progress-row retention test is claimed here.
    - Creator mutations now lock/reload community -> course -> section -> lesson and reauthorize the active owning creator inside the transaction. Reorder validates the exact locked current sibling set; invalid or stale submissions leave ordering unchanged. Coarse per-community serialization favors correctness over write throughput; hook-based state-change regressions are not independent-process concurrency or load proof.
    - URL validation rejects credential presence, invalid ports, non-string video IDs and unsupported providers; embeds remain generated HTTPS URLs with no server fetch. Empty lessons fail validation, text length is bounded, and valid plaintext `0` is preserved. No package or frontend-stack change.
    - Classroom lists paginate 12 courses, count only eligible lessons for members, and avoid eager-loading all lesson bodies on the listing page. Existing published/draft and tenant privacy rules remain enforced.
- Automated verification on the baseline HEAD plus this reviewed dirty diff:
    - `bash scripts/verify-fresh-setup.sh --mode verify`: exit 0, 139 seconds; **180 tests / 1480 assertions PASS**, test duration 110.67 seconds. This includes 8 original Classroom tests and 10 additional regressions, plus merged Feed/Events/M2 tests.
    - Whole-tree Pint: 126 files PASS; Composer validation PASS; Vite production build: 59 modules PASS; shared structural contract: 11 files PASS.
    - Real queue marker `EUR20-smoke-0d61977b-1cc8-4055-9d43-6ea566afa0a8`: pending=0, failed=0, handler=1. Live Mailpit/password-reset and invitation-worker integration passed with isolated recipients.
    - `bash scripts/test-agent-contract.sh`: 16 regression cases PASS. An initial sandboxed attempt returned no usable diagnostic; the same existing script passed with permitted access to its synthetic temporary Git fixture.
- Browser verification used synthetic users/content in guarded MySQL `scool_test` through a disposable loopback preview, not the development database. Section rename, section reorder, invalid-provider validation with retained input/status, successful retry and lesson navigation were verified. Narrow-screen lesson content showed no horizontal overflow at the measured 355px viewport; Alpine video toggle hides the iframe while keeping its provider fallback visible. Provider playback was unavailable for the synthetic video; successful playback is not claimed. Temporary viewport and preview were cleaned up without volume deletion. An initial preview environment-forwarding issue was corrected using `artisan serve --no-reload` before functional write checks.
- Privacy/safety: no development migration/reset, environment replacement, volume deletion, queue flush or shared inbox deletion. Preview fixtures remain ignored and are not publication prerequisites. Existing non-Classroom checkpoint sections are preserved.
- Pending: remote PR #17 still reports conflicts and head `6f86a94`; these local fixes have not been published. No current-head CI, independent machine, production performance or actual EUR-9 progress evidence is claimed. Review the final diff, authorize a normal history-preserving PR update, then verify the resulting immutable head before approval/merge. EUR-9 should use the accepted merged Classroom baseline; EUR-10 is not declared Done here.

### Provider fallback acceptance correction (2026-10-08)

- Owner-reported manual acceptance passed creator draft preview, member publication gating, unpublish/republish retention, invalid-provider validation, management/reordering, outsider denial and responsive controls. The provider fallback remained failing with YouTube error 153; these reports are manual evidence, not independently rerun browser proof.
- Source confirmed the fallback incorrectly linked to the iframe's `/embed/` URL with `rel=noreferrer`. YouTube documents error 153 as missing HTTP Referer or equivalent client identification: https://developers.google.com/youtube/iframe_api_reference#onError. A separate server-generated provider URL now maps validated IDs to the normal YouTube watch page or Vimeo video page. The iframe URL, provider allowlist, ancestry/publication gates and `noopener noreferrer` protection are unchanged; arbitrary submitted query parameters are not copied to the link.
- Red/green evidence: the new fallback regression first failed (1 test / 2 assertions) because the watch-page href was absent. After the bounded service/model/Blade fix, `php artisan test --compact --filter=Classroom` passed **19 tests / 296 assertions** (14.36 seconds), including five valid URL variants and rejected lookalike-host handling. Scoped Pint passed 3 files; working-tree diff whitespace check passed. All tests used guarded MySQL `scool_test`.
- The previous 180-test full Verify predates this correction; it is not current full-suite proof. No new browser playback, full-suite, CI or publication is claimed. Refresh the lesson and retry Open on provider to verify the normal watch page. Branch/HEAD remain `eur-10-classroom-audit` / `c8c280c38f06f1485d149b29f3f48313a06b025b` plus the retained dirty audit diff; no development data or configuration was changed.

### PR #17 publication preparation (2026-10-08)

- Subsequent explicit authorization covers final verification, commit and normal push to the existing PR #17 head branch. It does not authorize PR merge, external comments or Linear status changes.
- Fresh sources: Linear EUR-10 remains In Review; PR #17 remains open at `6f86a94aeb99e866f00995dd93a6887e974c56f0`; fetched main is `c8c280c38f06f1485d149b29f3f48313a06b025b`. No unexpected tracked or non-ignored work outside this audit was found.
- Owner-reported retest of Open on provider succeeded after the watch-page fix. This completes the reported manual checks; it does not replace automated tests or prove production provider availability.
- Publish only the explicit Classroom/source/test/checkpoint allowlist. Preserve private environment files, ignored fixtures and the main workflow contract. Retain the original PR commit through a normal merge and verify that integration does not change the reviewed source tree. No force push or direct main push.
- Final integrated-head Verify and publication results are pending in this preparation checkpoint. Existing PHP/data-safety contracts and the canonical IPv4 origin remain unchanged.

### Final integrated verification for PR #17 (2026-10-08)

- Reviewed implementation commit: `abc00980f8f12f2fed8981d1df7df8fd81eaa4d0`. History-preserving integration commit: `2ba10eaaec41a00d26ba55d99e4b1e3c271a2404`; both the original PR head `6f86a94` and fetched main `c8c280c` are ancestors. Exact tree equality with the reviewed implementation was verified after resolving conflicts; merged Feed/Events and the shared workflow were preserved.
- `bash scripts/verify-fresh-setup.sh --mode verify` passed on clean integration HEAD `2ba10eaaec41a00d26ba55d99e4b1e3c271a2404`: exit 0, 147 seconds; **181 tests / 1502 assertions PASS**, test duration 115.22 seconds. This supersedes the earlier pre-fallback full-suite result and includes the 19-test Classroom suite.
- Whole-tree Pint: 126 files PASS; Composer validation PASS; Vite production build: 59 modules PASS; runtime/HTTP checks PASS. Real queue marker `EUR20-smoke-2918208e-85d6-45fe-a78f-8b40b5818d6f`: pending=0, failed=0, handler=1. Live Mailpit/password-reset and invitation-worker tests passed using isolated recipients.
- Tracked shared agent contract: 11 files PASS. Working/index whitespace and bounded high-confidence source secret-pattern checks passed; no private environment, IDE/agent directory or ignored preview fixture enters the publication diff. This is not a full Git-history secret audit.
- Only this evidence checkpoint changes after the clean-head run. Verify application/test/runtime file equality with `2ba10ea` before the normal push to `eur-10-classroom-publishing`. No development migration/reset, environment replacement, volume deletion, queue flush or shared inbox deletion occurred during publication.
- Local functional evidence is ready for review. Remote-head confirmation, GitHub approval/required checks and merge remain separate; no CI success, production-readiness or EUR-9 progress completion is claimed. Linear remains In Review; no external comment or status change is authorized by this publication step.

## EUR-13 - Creator Member Management baseline implementation (2026-10-09)

- Outcome: Implement end-to-end Creator Member Management (VS-09): paginated member listing for owning creators with status metrics, domain state transitions (suspend, reactivate, remove), access blocking for non-active members, and anti-enumeration authorization.
- Branch: `eur-13-creator-member-management`, branched from `origin/main` (`38c5aba`).
- Implemented files:
  - Controller: `CommunityMemberController` handling `index`, `suspend`, `reactivate`, and `remove` with creator authorization, scope bindings, reason validation, and domain state transitions.
  - Views: `resources/views/communities/members/index.blade.php` (summary cards, member table with status badges and action forms), updated `resources/views/communities/show.blade.php` with Members navigation link.
  - Routes: Registered scoped routes under `/communities/{community:slug}/members` (`index`, `suspend`, `reactivate`, `remove`) in `routes/web.php`.
  - Feature tests: `tests/Feature/CommunityMemberManagementTest.php` (8 tests, 33 assertions covering AC-01 to AC-05).
- Verification:
  - `docker compose -p scool exec -T app php artisan test --filter=CommunityMemberManagementTest`: **8 passed (33 assertions)** in 9.34s.
  - Full test suite: `docker compose -p scool exec -T app php artisan test --compact`: **189 passed (1535 assertions)** in 134.16s.
  - Code Style: `docker compose -p scool exec -T app vendor/bin/pint --test`: **128 files PASS**.
- Status & Handoff:
  - Implementation completed and verified locally on guarded MySQL `scool_test`.
  - Changes committed under `3316423` and pushed to `origin/eur-13-creator-member-management`.
  - Pull Request link: https://github.com/Kerneural/S-cool/pull/new/eur-13-creator-member-management
  - Evidence comment prepared for Linear EUR-13 including metrics dashboard, state transition feedback, and automated test terminal proof. Awaiting review and PR merge authorization.

### PR #22 integration audit and local remediation (2026-10-12)

- Authority: fetch PR #22, audit and fix EUR-13, run verification, prepare the local UI for owner acceptance, then publish, comment, merge and complete EUR-13 after the owner explicitly accepted the browser flow.
- Provenance: original PR head `144cd55139319eb0e8e3b3c8a7469153232c0980`; fetched `origin/main` `eef5221d51f722138cd7a4d89967dda1e7ba5da3`. The branch is in an uncommitted history-preserving merge with the single `WORKING_CONTEXT.md` conflict resolved by retaining both issue histories.
- Findings corrected:
  - The submitted reason was optional, existed only for suspension and was discarded. All Creator membership state changes now require a trimmed non-blank reason and retain an immutable audit record.
  - Authorization and community activity were checked before the membership transaction. A domain action now locks and rechecks `community -> membership`, applies the existing state-machine transition, and writes the audit record atomically.
  - Automated coverage now performs an actual non-Creator mutation attempt and verifies audit creation, no audit on validation/tenant/state failure, duplicate-transition rejection and inactive-community denial.
- Scope: one forward migration creates `community_membership_actions` with restrictive foreign keys and tenant/member time indexes; the member list shows the latest action/reason and provides required reason inputs. Existing layout, payment state, invitation lifecycle and membership transition rules are preserved.
- Verification on the merged working tree:
  - Targeted EUR-13: **11 tests / 57 assertions PASS** on the final test source; the 259-test integrated run predates only these additional view assertions.
  - Scoped Pint and PHPStan: PASS; four expected member-management routes registered.
  - `scripts/verify-fresh-setup.sh --mode verify` through the installed Git Bash: production Vite build (60 modules), queue smoke, Composer validation, whole-tree Pint and **259 tests / 2078 assertions PASS** in 245 seconds. The first invocation selected the unavailable WSL alias and produced no source-test result; the corrected explicit Git Bash run is the acceptance evidence.
  - Local development database received only forward migration `2026_10_12_030001_create_community_membership_actions_table`; no reset, reseed, volume deletion, environment replacement or inbox deletion occurred.
- Owner-reported browser acceptance passed the member list, blank-reason validation, suspension and access denial, reactivation and access restoration, terminal removal, and latest audit display on the prepared local PR candidate. This is owner manual evidence; it does not replace automated tests or current-head CI.
- Publication authorization: inspect and stage the intended merge/fix set, create a history-preserving commit on the existing PR branch, push PR #22, wait for current-head required checks, post GitHub and Linear evidence, merge normally, update local `main`, and mark EUR-13 Done. Do not bypass branch protection or represent M4 as complete before the merge and tracker update succeed.

## EUR-12 - SePay Sandbox Access baseline implementation (2026-10-09)

- Outcome: Implement end-to-end SePay Sandbox paid community access (VS-08): paid invitation checkout initiation, checkout UI with QR/transfer details, IPN webhook with strict signature/amount/currency validation, idempotent replay protection, transactional membership activation, and read-only status checking.
- Branch: `eur-12-sepay-sandbox-access`, branched from `origin/main` (`38c5aba`).
- Implemented files:
  - Migrations: `2026_10_09_040001_create_payments_table.php`, `2026_10_09_040002_create_processed_webhook_events_table.php` (restrictive FKs, UNIQUE provider/event constraints, payment status enum).
  - Models: `Payment` (amount formatting, status helpers), `ProcessedWebhookEvent` (idempotency tracking), updated `Community` and `User` relationships.
  - Gateway Adapter (ADR-005): `PaymentGateway` interface, `SePayGateway` (live/sandbox transfer info, QR URL, auth token validation, IPN parsing), `FakePaymentGateway` (test doubles). Registered in `AppServiceProvider`.
  - Actions & Controllers: `InitiatePaidCheckout` (creates `PENDING_PAYMENT` membership and `Payment` attempt), `SePayWebhookController` (verifies IPN auth, enforces idempotency, pessimistic locks `Payment` row, verifies amount/VND, activates membership and accepts invitation transactionally), `PaymentController` (checkout page, read-only status endpoint).
  - Views: `resources/views/payments/checkout.blade.php` (QR code, bank details, auto-polling), `resources/views/payments/status.blade.php` (read-only state rendering), updated `resources/views/invitations/accept.blade.php`.
  - Configuration & Middleware: Added `sepay` configuration in `config/services.php`, exempted `webhooks/*` from CSRF in `bootstrap/app.php`.
  - Routes: Registered `POST /webhooks/sepay`, `POST /invitations/{id}/checkout`, `GET /payments/{ref}/checkout`, `GET /payments/{ref}/status` in `routes/web.php`.
- Verification:
  - `docker compose -p scool exec -T app php artisan test --filter=SePayPaymentTest`: **8 passed (41 assertions)** in 11.14s.
  - `docker compose -p scool exec -T app php artisan test --filter=CommunityInvitationFlowTest`: **14 passed (263 assertions)** in 32.43s (zero regressions).
  - Full test suite: `docker compose -p scool exec -T app php artisan test --compact`: **189 passed (1543 assertions)** in 124.28s.
  - Code Style: `docker compose -p scool exec -T app vendor/bin/pint --test`: **138 files PASS**.
- Status & Authority:
  - Implementation completed, verified locally on `scool_test`, and committed under `7c891c9`.
  - Pushed to `origin/eur-12-sepay-sandbox-access`. Linear EUR-12 updated with evidence comment. Awaiting review and PR merge authorization.

### PR #21 independent audit and approved remediation (2026-10-12)

- Authority: audit and fix EUR-12, including the explicitly approved Creator FREE/PAID controls and one-time VND pricing. Guide publication; do not commit, push, merge, post external comments or change Linear status. The historical baseline above is not current acceptance evidence.
- Provenance: live Linear EUR-12 AC and PR #21 head `c7b397b169bdd5090e5635fb1f03157c69762044`; review in the attached detached worktree `eur-12-payment-review/scool`. The primary checkout remains clean `main` at `e77587a6e37e0b77091a2d6f37b91026f208c8f9`. Fetched `origin/main` is `44c75c4`; GitHub reports conflicts and no check runs on the old PR head.
- Accepted contract: SePay bank-transfer webhooks in **Test Mode**, not Payment Gateway Sandbox `ORDER_PAID`. ADR-005 and README record this correction. External Test Mode account/key configuration and restricted webhook delivery remain operational acceptance requirements; an application flag alone cannot prove provider mode.
- Remediation plan and scope:
  - Preserve ownership, private visibility and lifecycle guards. Add nullable `communities.price_vnd` through a forward migration; validate Creator pricing explicitly rather than changing model mass-assignment permissions. Existing paid communities need a positive price before new checkout.
  - Snapshot the configured price into each payment attempt; reuse pending attempts without repricing. Cancel is owner-only and does not grant access; retry uses the original valid invitation and creates a new reference.
  - Require explicit Test Mode, API-key authorization, the configured destination account, incoming transfer direction, a positive integer amount and one bounded payment reference. Reject malformed arrays/fractional amounts and minimize stored payloads. Only the exact webhook path is CSRF-exempt.
  - Serialize actor -> community -> invitation -> membership -> payment. Keep terminal payments immutable; late notifications cannot recreate membership or undo suspension/removal/departure. Activation requires current verified identity, matching eligible invitation, active paid community and PENDING_PAYMENT membership. Payment, membership and invitation changes roll back together on failure.
  - Bind each provider event to one payment; acknowledge accepted duplicates with `success: true`. Remove the real-bank QR/payment instruction from Test Mode checkout; add minimal cancellation, terminal-state and bounded polling behavior without unrelated redesign.
- Verification inputs: disposable `scool-app` container on `scool_scool-network`, source bind-mounted from this review checkout; primary `vendor/` mounted read-only, review `public/build/` mounted read-only, and `.env.example` mounted as `.env` with a synthetic test key. Only guarded MySQL `scool_test` is used. Composer production-package versions/dist references match the primary lockfile; development tools come from the primary installed vendor and are not proof of an exact PR-lock installation.
- Commands: `php artisan test --compact` and `php artisan test --compact --filter=InvitationProcessIntegrationTest` in that disposable runtime; `npm ci && npm run build` in the review checkout; scoped `vendor/bin/pint --test`; `git diff --check`; `gitnexus analyze --index-only --pdg --name S-cool-pr21` followed by `detect_changes(scope: all, worktree: review checkout)`.
- Evidence:
  - New boundary regressions initially failed: 4 tests / 5 assertions, exposing membership reset, terminal payment overwrite and late activation. Successive focused runs passed 28 tests / 355 assertions and 30 tests / 384 assertions. Pricing changed an obsolete baseline assertion only under the explicitly approved Creator pricing contract; ownership/privacy assertions remain.
  - First full run: 200 passed / 1 failed, 1650 assertions, 210.94 seconds. The failure was an existing isolated invitation worker process exceeding its 25-second timeout while build/index jobs also ran. No timeout or test logic was weakened. Isolated invitation integration rerun: 4 passed / 35 assertions, 17.24 seconds. Final full suite without competing build/index jobs: **201 passed / 1660 assertions, 118.65 seconds**, exit 0. This is PR-head-plus-dirty-fixes evidence, not the integrated main tree or remote CI.
  - Exact PR frontend lock installed successfully and Vite production build passed (59 modules, 10.97 seconds). npm reported 9 dependency vulnerabilities (2 moderate, 5 high, 2 critical) on this old branch. Integrate accepted main dependency/CI changes; do not run `npm audit fix --force` or restore the stale lock over main.
  - Final `vendor/bin/pint --test` passed all 11 explicitly changed PHP files; whitespace check passed. Refreshed GitNexus index: 4081 nodes / 7110 edges; local change mapping found the expected checkout/payment/community route surface. Dynamic Laravel DI, Blade and untracked-file mapping are lower-bound coverage; source inspection and executable regressions, not graph counts, establish behavior. The final evidence-only checkpoint update was not reindexed and is not graph-backed documentation coverage.
- Safety: no development migration/reset, `.env`/key change, volume deletion, shared inbox deletion, live-money operation, HTTPS tunnel or external publication. Pricing forms and checkout controls are the only intended UI changes; visual parity/manual acceptance are not claimed.
- Pending acceptance: integrate `origin/main` without discarding accepted routes, image upload/UI, admin/tenant protections, dependency locks, CI, README or other issue checkpoints; rerun final integrated checks and required CI. Configure actual SePay Test Mode and verify provider delivery plus cancel/retry in the browser. No independent-process payment race/load proof, real-money flow, provider acceptance, Linear Done or merge readiness is claimed.
- Local handoff: an ignored `.gitnexus/eur-12-pr21-audit.patch` in the primary checkout carries this bounded diff, including new files, against `c7b397b`. Verify the fetched source branch still has that head before applying; if it changed, re-anchor instead of forcing the patch. Temporary intent-to-add entries used to generate the patch are removed without deleting files. The attached review worktree remains the preserved source of the uncommitted corrections. Do not run the main-only sync script on the feature branch.

### Primary-checkout main integration (2026-10-12)

- The audit patch was applied and manually committed as `bee67d0bd6afbfb6bd81c9555139450981da3be1` on `eur-12-sepay-sandbox-access`. The manual `git merge origin/main` uses `44c75c40359d7caef6fb7a6d89f52ca7dff716cc` and remains uncommitted. Current authority covers conflict resolution and verification, not committing, pushing, merging the GitHub PR or external status/comments.
- Resolve only the five conflicted files: Community and User retain both payment and platform-admin relationships, User retains progress/admin casts, bootstrap retains exact-path webhook CSRF exemption and admin middleware alias, routes retain payments alongside accepted progress/admin routes, and this context retains both issue histories. No entire view or instruction file is replaced; incoming main CSS/locks/CI/scripts and accepted UI are preserved byte-for-byte against the pinned main where unchanged by payment.
- Installed Composer packages match every production/development lock entry by name, version and dist reference. No Composer dependency update or development environment replacement is required. Main npm lock installation/build passed (59 modules, 5.64 seconds); npm audit reports zero vulnerabilities. `composer validate --strict --no-interaction` passed; full `vendor/bin/pint --test` passed 157 files; `vendor/bin/phpstan analyse --configuration=phpstan.neon --memory-limit=512M --no-progress` passed with no errors. Final integrated `php artisan test --compact --fail-on-skipped --fail-on-empty-test-suite` passed **246 tests / 2012 assertions**, exit 0, 201.55 seconds. These results cover HEAD `bee67d0` plus the pinned main integration and resolved source, not a committed merge head or remote CI.
- Verification runtime mounts this checkout and read-only `.env.example` over the runtime `.env`, with a synthetic test key and explicit APP_ENV=testing / DB_DATABASE=scool_test. Keep development DB, roles, secrets, volumes and Mailpit inbox intact. No development migration, tunnel or real-money payment is performed. Both incoming main checkpoint history and existing payment history were verified as unchanged sections with this additive integration checkpoint. `git diff --check` passed.
- GitNexus refreshed the integration source (4795 nodes / 8591 edges). Its registry and local metadata pin `bee67d0`; an initial MCP context response still reported an older commit, so it was not used as current evidence. Registry refresh and a subsequent context query removed that stale warning. Eloquent relationship queries return definitions without reliable runtime callers; bootstrap taint/control queries have no applicable findings/edges. No PHP taint completeness or safety from zero hits is claimed. Source comparison and executable tests establish preservation; the final evidence-only Markdown update was not reindexed.
- Next: manually stage only the five resolved conflicts and finish the merge commit. The index still reports five unmerged paths until that explicit staging step, although the working files contain no conflict markers. Push to the existing source branch only after the final diff is understood; required CI (including remaining security scanners), browser/provider acceptance and normal review remain separate merge/Done gates. The audit did not stage files, commit, push, post external comments or mutate Linear. EUR-12 was freshly read as In Review; its external description/evidence still needs the approved Test Mode/pricing contract update when publication is authorized.

### Pushed-head local UI and provider readiness audit (2026-10-12)

- Revision: clean `eur-12-sepay-sandbox-access` at `cafbd2a1a71568ed45d6c90ef274b18826fd5dba`, matching PR #21. Live EUR-12 remains In Review. Its Linear description still uses the historical Sandbox wording; the approved bank-webhook Test Mode contract above governs this audit. No remote comment/status/publication change is made.
- Read-only runtime preflight found all three payment schema additions missing and Test Mode disabled, with no configured destination account or webhook key. After explicit approval, `docker compose -p scool exec -T app php artisan migrate --no-interaction` with only the three payment migration paths applied `create_payments_table`, `create_processed_webhook_events_table` and `add_community_price_vnd` successfully in batch 7. A subsequent migration-status check confirms those changes; no reset, reseed, environment/key change or unrelated migration occurred.
- Browser evidence on `http://127.0.0.1:8080`: existing local demo Creator can open the community form and select FREE/PAID. Submitting PAID with an empty price returns the required-price validation message and preserves entered name/slug/mode; no community was created. Cancel returns to the unchanged community list. Captured browser warnings/errors were empty. Pricing controls were inspected in desktop and narrow layouts, but this is not full checkout/mobile acceptance. Sanitized screenshots are local ignored artifacts under `.gitnexus/eur-12-pr21-pricing-{validation,desktop}.jpg`, not tracked deliverables.
- Local endpoint probe: empty unauthenticated `POST /webhooks/sepay` returns HTTP 401. The first shell attempt could not reach localhost inside the sandbox (curl exit 7); the approved network-access diagnostic returned 401. This establishes rejection only, not authentic provider delivery or successful activation.
- Source finding: `resources/views/payments/status.blade.php` pending state still asks users to complete a bank transfer without a Test Mode/no-real-money notice. This contradicts the checkout safety wording and should be corrected before acceptance; no source fix was authorized by this audit-only request.
- Acceptance blocker: actual SePay Test Mode account/key configuration and a restricted temporary HTTPS webhook endpoint remain absent. No provider transaction, authenticated delivery, polling redirect, live cancel/retry, duplicate-delivery behavior or end-to-end entitlement activation was tested in this session. Prior automated-suite results are not substituted for these missing browser/provider checks. No tunnel or real transfer was initiated. GitHub exposes successful `CI/ Runner Preflight` for this head, not proof of all integrated/security gates; PR remains blocked.
- Next: configure the separate SePay Test Mode account and API-key webhook privately, reload local configuration, authorize an endpoint exposing only `POST /webhooks/sepay`, and run the exact-price/reference simulated incoming transaction with browser plus provider-delivery evidence. Never paste keys into chat or expose the full development application. Retain In Review until remaining acceptance gates pass.

### Invitation UX remediation and actual Test Mode acceptance (2026-10-12)

- Authority: audit and fix PR #21/EUR-12, including the reported inactive Paid Checkout control. Preserve the existing local invitation/status remediation and README edits found at session start. Branch/head remains `eur-12-sepay-sandbox-access` / `cafbd2a1a71568ed45d6c90ef274b18826fd5dba`; base/merge-base is `44c75c40359d7caef6fb7a6d89f52ca7dff716cc`. Live EUR-12 remains In Review. No commit, push, merge or external comment/status change is performed.
- Root cause: invitation proof is a URL fragment, not part of `/invitations/{id}` or the account-verification email. The original Alpine implementation consumes/removes that fragment and holds it only in the current page. Reload/authentication/verification navigation cannot recover it. Do not bypass email/token/expiry checks or persist raw tokens in URLs, history, local/session storage to conceal the missing-proof state.
- Bounded final diff: the existing `invitationAcceptance` helper and Alpine registration validate exactly one lowercase 64-hex fragment token; invitation controls default disabled even before JavaScript starts and explain missing-token recovery, login/verification and free versus paid actions. The standard paid-acceptance error now points to the available checkout, rather than claiming it is unimplemented. Payment status explains Test Mode/no real transfer and how to retry. README documents reopening the original invitation. Added a CI step running `node --test tests/js/*.test.mjs`; no dependency, schema, authentication contract or unrelated design change is introduced. New helper and JS test files are untracked and must be included in eventual publication.
- Verification: built current assets with `npm run build` (60 modules, 2.74s); exact CI JS command passes 2 tests. Targeted payment/invitation suite passes 22 tests / 172 assertions. Final integrated `docker compose -p scool exec -T -e APP_ENV=testing -e DB_DATABASE=scool_test app php artisan test --compact --fail-on-skipped --fail-on-empty-test-suite` passes **248 tests / 2026 assertions**, 154.48s, exit 0. TestCase verifies scool_test before RefreshDatabase. Pint passes the two changed PHP files; PHPStan passes with no errors; `git diff --check` passes.
- Discriminating regression: the new missing-token HTTP test fails against the original HEAD invitation view in a disposable runtime (1 failed / 2 assertions). Source was never reverted. After removing the temporary baseline artifact and clearing compiled views, the final invitation/status regressions pass 2 tests / 14 assertions. Compiled views were cleared after the baseline probe; no environment/key/database reset occurred. The full-suite source is unchanged by that probe.
- Browser acceptance uses a new uniquely addressed synthetic local account and PAID community, leaving previous communities/accounts unchanged. SMTP was checked as local Mailpit before any notification. Registered the invitee, received/used its signed verification email, reopened the original community invitation, and confirmed enabled Paid Checkout opens the exact 10,000 VND screen. Bare invitation URL visibly disables both buttons with recovery instructions. Mobile invitation inspection at 390px showed no horizontal overflow; missing proof generated no console error. Do not claim automatic resumption after verification: reopening the original link is the deliberately retained contract.
- Actual provider acceptance: the existing authenticated SePay dashboard was visibly in Test Mode. Used its simulator for three incoming transactions on the configured test account: cancelled attempt A receives HTTP 409 and cannot activate; wrong-amount attempt B (9,000 versus 10,000 VND) receives 422 and the checkout polls to failure; fresh attempt C at exact price/reference receives 200, membership activates and browser automatically redirects to the community. Replayed the same successful provider transaction once via its delivery-history selection; a new delivery log returns 200 for the same transaction, not a second simulated transfer. Read-only database probes show retained attempts `[CANCELLED, FAILED, SUCCEEDED]`, exactly one ACTIVE membership, one processed event for the successful payment, and ACCEPTED invitation before/after replay. Community remains accessible after browser refresh. A different demo identity receives 404 on both checkout and status URLs.
- Tunnel checks: the supplied existing public endpoint rejects empty unauthenticated POST with 401; public `/login` returns 404 and no login form. These probes are not a complete method/path exposure audit. No tunnel was created, broadened or stopped, and no provider keys, authentication settings or Live resources were changed. SePay logs showing 409/422 are intentional negative cases. No real money was transferred. The synthetic account/community/attempts and three simulated transactions are retained for evidence; no records, volumes or shared inbox were deleted.
- Evidence artifacts remain private/ignored: `.gitnexus/pr21-missing-token.jpg`, `pr21-member-access.jpg` and `pr21-sepay-delivery.jpg`. No invitation tokens, webhook keys or bank-account details are recorded in this checkpoint/screenshots. Only the scoped Mailpit messages were opened; unrelated messages were not changed.
- Graph coverage: refreshed current source with GitNexus index-only/PDG (4835 nodes / 8658 edges), verified CLI current runner/schema-4 receipt and current content; registry pins cafbd2a. An initial stale MCP warning was reconciled through registry refresh before relying on subsequent context/impact. Direct PaymentController/routes dependents were inspected; callback/DI/Blade and untracked-diff mapping remain lower-bound. Empty taint/control results are not security proof. The final evidence-only checkpoint is not graph-backed documentation coverage. A delegated read-only lens was unavailable, so the scoped source/contract/security review was completed inline.
- Remaining publication gates: include all bounded source/tests plus CI/README/checkpoint files in an explicit staging allowlist, obtain applicable commit/push authority, run required CI on that published final head, and satisfy independent review/approval before merge/Done. The previously green cafbd2a CI was inspected beyond its misleading `Runner Preflight` job name and includes tests, builds and security scans; it does not cover these uncommitted corrections. Production payment, payout/refund, independent-host verification and competing provider-request/load tests remain outside this acceptance evidence.

### Publication authorization and owner acceptance (2026-10-12)

- The owner confirms manual PAID checkout, a successful transaction simulated through SePay Test Mode, community access and ACTIVE membership. Additional negative provider cases rely on the completed audit evidence above; no claim of independent owner repetition is made.
- Explicit current authorization covers the remaining commit/push, normal PR #21 merge and EUR-12 completion workflow. Existing application changes match the tested diff; JS tests and whitespace checks were reconfirmed before staging. No new application behavior, credentials or development data changes are included.
- GitHub currently reports PR #21 OPEN, head `cafbd2a1a71568ed45d6c90ef274b18826fd5dba`, review decision REVIEW_REQUIRED and merge state BLOCKED, without an approving review. Publish the corrections and verify required CI on their new head; keep EUR-12 In Review until normal review and merge requirements are satisfied. Do not bypass branch protection or represent an unmerged PR as Done.

## EUR-9 - Lesson progress implementation (2026-10-08)

Historical implementation checkpoint below: reported before integration with accepted main. The sequential request tests did not independently prove competing writes or authorization changes during a waiting mutation; UI markup alone did not prove mobile behavior. Current audit evidence is recorded separately below.

### Outcome and scope

- Outcome: An active member marks an authorized published lesson complete or incomplete and sees the same personal completion state after refresh/re-login, without altering other members' progress or creating duplicate records.
- Scope:
    - Database: forward migration `2026_10_08_040001_create_lesson_progresses_table.php` with unique index `(user_id, lesson_id)`, model `LessonProgress` (`$table = 'lesson_progresses'`), factory `LessonProgressFactory`.
    - Mutation & Idempotency: endpoint `POST /communities/{community:slug}/courses/{course}/lessons/{lesson}/progress` (`communities.lessons.progress.update`) validates explicit boolean `completed`, resolves actor strictly via `$request->user()`, atomic update/upsert handling concurrent writes without duplicate-key error.
    - Security/Authorization: `LessonPolicy::updateProgress` enforces multi-tenant boundary, strict ancestry, active community, active membership, published course, and published lesson. Denies non-active memberships (`PENDING_PAYMENT`, `SUSPENDED`, `REMOVED`, `LEFT`) and draft states with 404 (`denyAsNotFound`). Creator role alone does not grant progress tracking without active membership context.
    - Retention & Restore: unpublishing retains rows in DB but excludes them from access and calculations; republishing immediately restores member progress.
    - Calculation: `Course::publishedLessonsCount()`, `Course::completedLessonsCountFor($user)`, and `Course::progressPercentageFor($user)` with safe zero division (0/0 = 0%).
    - UI: responsive completion toggle buttons (min 44x44px touch target) in header and bottom navigation of lesson view, curriculum sidebar status checkmarks, personal progress bar on course outline, and progress percentage on classroom course cards.
    - Tests: comprehensive feature test suite `tests/Feature/LessonProgressTest.php` covering AC-01 through AC-07 on MySQL `scool_test`.

### Verification and results

- Branch: `eur-9-lesson-progress`, branched from `eur-10-classroom-publishing` (HEAD `6f86a94aeb99e866f00995dd93a6887e974c56f0`).
- Scoped Feature Tests: `docker compose -p scool exec -T app php artisan test --filter=LessonProgressTest` -> **11 tests / 55 assertions PASS** (13.90s).
    - AC-01: Explicit mark complete/incomplete, timestamp persistence, fresh session/re-login persistence.
    - AC-02: Idempotent repeat requests, atomic writes, JSON response format.
    - AC-03: Member progress isolation, spoofed user_id in payload safely ignored.
    - AC-04: Negative authorization matrix: non-members and non-active membership statuses (`PENDING_PAYMENT`, `SUSPENDED`, `REMOVED`, `LEFT`) denied with 404. Creator without active membership denied with 404; active member creator allowed.
    - AC-05: Unpublish retains database rows; calculation excludes draft; republishing restores retained progress.
    - AC-06: Published-only lesson calculation; zero-division safety on 0 published lessons.
    - AC-07: Lesson show and course show UI controls, mobile accessibility touch target, sidebar checkmark rendering.
- Full Suite Verification: `docker compose -p scool exec -T app php artisan test` -> **141 tests / 985 assertions PASS** (148.12s), zero failures or regressions across all test suites.
- Code Style: `docker compose -p scool exec -T app vendor/bin/pint --test` -> **PASS** (105 files checked, 0 style issues).
- Agent Contract: `bash scripts/verify-agent-contract.sh` -> **PASS** (11 shared files).
- Preserved Working Tree: `scripts/sync-local.sh` preserved unstaged in working directory.

### Integrated audit and verification (2026-10-09)

- Contract: live Linear EUR-9 AC-01 through AC-07. Authorized: bounded fixes, a normal commit/push to the existing PR #19 branch after tests pass, and GitHub/Linear comments. Merge and Done are not authorized.
- Pinned review: original PR head `156fe15768ebfe6773d3a0766bcac6ead7520d29`, main `38c5aba3b2e51e3b83eb12c7e68eb7bf472f99ac`, merge-base `6f86a94aeb99e866f00995dd93a6887e974c56f0`. Work was isolated from the original checkout and its five unrelated local GitNexus setup edits.
- Integration: resolved eight conflicts by retaining accepted Classroom management/reorder forms, provider watch-page fallback, retention controls, paginated cards, Feed/Events routes and shared workflow. The publication keeps the original PR and main as merge parents; no history rewriting is needed. The final PR-only delta against main is limited to progress source/UI/schema/tests and this checkpoint.
- Findings fixed:
    - The original endpoint authorized route-bound state before an unlocked write. `LessonProgressMutation` now reloads/locks community -> course -> section -> lesson -> membership, validates ancestry and current access inside the transaction, and writes only the authenticated user's explicit completion state.
    - Repeated complete requests previously changed `completed_at`. Replays now preserve completion/update timestamps; incomplete clears completion time. The unique user/lesson constraint remains the final database guard.
    - Progress counters now use one personal published-only aggregate for the outline/JSON response and bounded subquery counts for paginated cards. Existing management controls and lesson navigation remain available.
    - Added independent PHP-process regressions for competing first writes and committed lesson/course unpublish, community archive and membership suspension while a request waits on the community lock. Added CSRF enforcement, validation feedback, actual unique-constraint rejection, stale-snapshot denial, retention/republish, personal summaries and query-bound regressions.
- Trade-off: reuse the existing coarse per-community creator lock order for correctness and simple serialization. This is not throughput/load proof and does not promise arrival ordering for opposing concurrent completion values. No package, stack, schema-retention or platform-admin contract change; admin authorization integration remains a future cross-module check once that role exists.
- Verification:
    - Scoped `docker exec scool-eur20-pr19_app php artisan test --filter=LessonProgress`: **25 tests / 190 assertions PASS**, 36.35 seconds.
    - `bash scripts/verify-fresh-setup.sh --mode verify --project scool-eur20-pr19`: **206 tests / 1692 assertions PASS**, test duration 197.94 seconds, integrated Verify 229 seconds, exit 0. Whole-tree Pint: 135 files PASS; Composer manifest/lock validation, Vite production build (59 modules), five-service health and HTTP `/up` PASS.
    - Real daemon queue marker `EUR20-smoke-7406ec56-5f05-474c-82dc-f147fcf78121`: pending=0, failed=0, handler=1. Mailpit password-reset and invitation process integration passed with isolated recipients.
    - Tracked shared contract: 11 files PASS; existing agent-contract regression script: 16 cases PASS. Whitespace and bounded high-confidence source secret-pattern checks passed; these are not full history secret scanning or agent-compliance proof.
    - The full run used original HEAD plus the integrated dirty candidate, captured as Git tree `96ef26085ceb0ea58f2cddb4f4b932142bf2a616`. Only this evidence checkpoint changes after the run; publication must verify all other tracked paths are identical to that tested tree.
- An initial full run failed due to an incorrect membership helper assumption and a guarded test-fixture assignment. Both were corrected using the existing status/transition contract; the scoped and full passing runs above supersede that failed attempt.
- Graph limits: GitNexus indexed the integrated working candidate, not a clean original PR graph. Its pending-merge change map includes incoming main changes; source diff against pinned main determines actual PR scope. Dynamic PHP call resolution can undercount callers; PHP PDG/taint coverage was skipped because it is unsupported by the installed analyzer. Raw source and MySQL/runtime tests supply the independent checks; graph risk labels are not an approval verdict.
- Dependency baseline: unchanged npm lockfile reports nine development/build advisories (2 moderate, 5 high, 2 critical); `npm audit --omit=dev` reports zero at this check. No automatic dependency upgrade or production-safety claim.
- Review environment: dedicated Compose project `scool-eur20-pr19`, application `http://127.0.0.1:18080`, Mailpit `http://127.0.0.1:18025`, separate database volume and session cookie. Its new private environment was configured for that preview only; the original checkout remains on `http://127.0.0.1:8080`. No original development migration/reset, key replacement, volume deletion, queue flush or shared inbox deletion.
- Pending: browser/manual acceptance on the published revision, GitHub approval/required checks and merge. CI, independent-machine evidence and production load are not verified here. Keep Linear In Review; this checkpoint does not complete M3.

## EUR-14 - Platform administration implementation (2026-10-10)

### Outcome and scope

- Outcome: Platform Admin suspends and reactivates communities with required reason and minimal audit logging without modifying Creator content or granting blanket access to private modules.
- Scope:
    - Database:
        - Forward migration adding `is_platform_admin` boolean flag (default `false`) on `users` table.
        - Forward migration creating `platform_admin_actions` table (`id`, `admin_id`, `community_id`, `action`, `reason`, `created_at`).
        - Model updates: `User` (`isPlatformAdmin(): bool`, guarded from mass-assignment), `PlatformAdminAction` model and relations.
        - Seeder update: `UserSeeder` persona `admin@scool.local` initialized with `is_platform_admin = true`.
    - Security & Authorization:
        - Middleware `EnsureUserIsPlatformAdmin` (`platform-admin`) protecting all `/admin/*` routes.
        - Deny-by-default (403 Forbidden / 404 Not Found) for visitors, members, and creators.
        - Least privilege: Platform Admin role does not grant content edit permissions or blanket policy bypass for Creator actions.
        - Anti-privilege escalation: `is_platform_admin` excluded from mass-assignment in `User::$fillable`.
    - Controllers & Routing:
        - Dedicated namespace `App\Http\Controllers\Admin\CommunityController`.
        - Routes under prefix `/admin`, middleware `['auth', 'verified', 'platform-admin']`.
        - `GET /admin/communities`: Minimal listing with pagination and status filters.
        - `POST /admin/communities/{community}/suspend`: Validates non-empty reason, transitions state from `ACTIVE` to `SUSPENDED`, records audit log in transaction.
        - `POST /admin/communities/{community}/reactivate`: Validates non-empty reason, transitions state from `SUSPENDED` to `ACTIVE`, records audit log in transaction.
        - Terminal state protection: rejects mutations on `ARCHIVED` communities.
    - UI:
        - Independent new view `resources/views/admin/communities/index.blade.php`.
        - Existing Creator and Member views remain 100% untouched.
        - Conditional Admin link in shared navbar visible strictly when `Auth::user()->isPlatformAdmin()`.
    - Tests:
        - Comprehensive feature test suite `tests/Feature/PlatformAdministrationTest.php` on MySQL `scool_test`.

### Verification evidence

- Feature test execution: `docker compose -p scool exec -T app php artisan test --filter=PlatformAdministrationTest` -> 11 passed, 64 assertions, 0 failures.
    - Covers schema columns/tables, access denial for unauthenticated/unverified/members/creators, platform admin listing and filtering, community suspension with mandatory reason and immutable audit log, reason validation, already suspended/archived terminal checks, denial of member/creator access across modules when suspended, community reactivation with audit log, reactivation validation, least privilege (no blanket bypass for creator actions), and anti-privilege escalation via register/profile.
- Full regression suite execution: `docker compose -p scool exec -T app php artisan test` -> 217 passed, 1,756 assertions, 0 failures. Zero regressions across all modules.
- Code style check: `docker compose -p scool exec -T app vendor/bin/pint` -> PASS (style issues formatted).
- Agent contract check: `bash scripts/verify-agent-contract.sh` -> PASS (11 shared files verified; routing and privacy boundaries valid).
- Local development database: migrations applied and seeded (`admin@scool.local` with `is_platform_admin = true`).

### Audit checkpoint - partial, acceptance blocked (2026-10-12)

- Contract: live Linear EUR-14 AC; PR #28 head `36a47e914f7f6087fe7bfed1754a576aacedfc04`, merge-base `de69f8892a4b3d40f7550bceb2e7c9c50333b6f9`. The original checkout remains clean on `eur-26-ci-baseline` at `cffab26a3711323924692873352e4791b0b34b14`; review changes are isolated in the attached `eur-14-admin-review` worktree, currently detached at the PR head.
- Confirmed P1: audit foreign keys cascade on account deletion. A platform admin with an action but no ownership/membership can use the existing profile-delete endpoint and erase the action history. New `test_profile_deletion_cannot_erase_admin_audit_history` fails against the original implementation. Proposed correction awaits approval: a forward migration changing audit FKs to RESTRICT, plus the existing profile retention guard denying deletion of users with audit records and returning a validation error. Do not change the original already-applied migration or reset data.
- Fixed P2 locally: admin listing eagerly loaded all action history for each community although only the newest record is displayed. `latestPlatformAdminAction` uses `latestOfMany()` and only that relation/admin is eager-loaded. The view uses that relation without any layout/control redesign. The new bounded-loading test failed before this correction and passes afterward.
- Tests also verify fresh seed grants only the new demo admin, reruns preserve existing role/account data, unverified admin denial, invalid reasons/terminal states produce no audit record, repeated suspension does not duplicate audit, and mass assignment cannot grant admin. Removed a duplicate PHP array key from the existing escalation test. Fresh seeding is not defective: seed execution is trusted/unguarded. An existing demo account remains non-admin intentionally; promotion requires an explicit trusted operator action, not automatic escalation by matching an email.
- Runtime verification used a temporary `scool-app` container on `scool_scool-network`, PR worktree source, read-only dependencies/build assets from the unchanged CI checkout, and read-only `.env.example` mounted as the container's `.env` with explicit testing environment/key and `DB_DATABASE=scool_test`. The pre-RefreshDatabase safety guard was retained. No development DB migration/reset, local credential/key replacement, inbox deletion or stack restart was performed. This is not exact-lock CI, another host's proof or manual/browser evidence.
- Initial three new tests: 2 failed (retention and bounded loading), 1 passed (seed behavior), 11 assertions. After the bounded-loading correction, `php artisan test --filter='PlatformAdministrationTest::(?!test_profile_deletion_cannot_erase_admin_audit_history)' --fail-on-skipped`: 17 passed, 124 assertions, exit 0, 20.32s. The named retention test is explicitly excluded and remains red/unfixed; this is NOT a full passing admin suite. Scoped Pint passed for the three PHP files changed so far.
- GitNexus index-only graph was refreshed for this exact worktree/head under alias `S-cool-pr28`. Impact queries identify `routes/web.php`; Eloquent/Blade dynamic relations have missing callers and controller impact is a lower bound. Source/diff and real tests, not empty graph edges, establish the findings. No PHP PDG/taint coverage is claimed.
- Pending: approve the retention shared-contract correction, implement it with negative deletion/forward-upgrade tests, run final complete tests and required CI, publish only with current commit/push authorization, then manual/browser acceptance and independent approval. No PR #28 merge, publication or Linear Done is authorized/completed by this checkpoint. PR #29 separately has successful CI but still requires independent approval before normal merge and a main push run before EUR-26 Done.

### Approved retention correction and publication - in progress (2026-10-12)

- Current authority: implement RESTRICT plus profile deletion retention guard; commit/push the tested changes to the existing PR #28 source branch `eur-14-platform-admin`. No PR #28 merge or EUR-14 Done authorization. PR #29 will be merged by the owner; its latest observed state is OPEN/REVIEW_REQUIRED, so EUR-26 remains unaccepted pending merge and main CI.
- Added forward migration `2026_10_12_010001_preserve_platform_admin_audit_records.php` for both audit foreign keys, preserving the original already-applied migration and records. The profile deletion guard now checks `platformAdminActions` before logout/deletion and returns the existing `userDeletion` validation bag. This also protects retained actions after role revocation. Database-level RESTRICT remains the backstop for direct deletion.
- Targeted command `php artisan test --filter='PlatformAdministrationTest|ProfileTest|DatabaseBaselineTest' --fail-on-skipped --fail-on-empty-test-suite` passed: 31 tests, 222 assertions, exit 0, 22.10s. The previously red retention test is included and passes. New migration test simulates the old CASCADE constraints with an existing action, applies the forward correction, verifies unchanged attributes and MySQL error 1451 for direct admin/community deletion. TestCase guarded scool_test before migrations; MySQL DDL commits invalidate RefreshDatabase's transaction and the framework resets its migration state before the next test.
- All 112 packages in the PR #28 Composer lock match the borrowed installed vendor versions/distribution references; the CI checkout has additional analysis tooling. No dependency update is included. Full read-only Pint passed (142 PHP files). Full suite and level-0 Larastan verification are running; do not infer success until each session completes.
- README now documents fresh versus existing demo admin roles and an explicit trusted local operator action. No automatic promotion, password reset, live database change or account deletion was performed. Visual parity/manual UI acceptance remains pending; only the admin view's latest-record binding changes, not its layout or controls.

### Final local verification before publication (2026-10-12)

- Full command on the final application/migration/test source: `php artisan test --fail-on-skipped --fail-on-empty-test-suite` passed, 225 tests / 1836 assertions, exit 0, 205.89s. No retention exclusion remains. Live Mailpit SMTP, tenant/privacy/authorization, invitation/progress concurrency, seed/database guards and original profile deletion behavior passed alongside the new admin regressions.
- Runtime command prefix: `docker run --rm --network scool_scool-network` with this worktree mounted at `/var/www/html`, borrowed checked-in-lock-compatible vendor and existing built assets mounted read-only, `.env.example` mounted read-only as `.env`, explicit `APP_ENV=testing`, a synthetic test-only key and `DB_DATABASE=scool_test`. Every test connection is guarded before migration; the development database scool and application stack were not upgraded. Empty worktree mount-point placeholders were removed after completed runs; no credentials/config were stored or deleted.
- Read-only full Pint: 142 files, exit 0. `vendor/bin/phpstan analyse --configuration=phpstan.neon --memory-limit=512M --no-progress`, using the unchanged EUR-26 level-0 config/analysis tooling mounted read-only, passed with no errors (exit 0). The config/tooling is not added to PR #28. `bash scripts/verify-agent-contract.sh --require-tracked` and `git diff --check` passed. Existing built assets were reused; a new frontend build/visual parity is not claimed.
- GitNexus was refreshed for the final dirty source before publication gating; no stale index or missing dynamic Laravel edges are used as safety proof. Local results qualify this correction for commit/push, not for merge/Done. Await current-head GitHub checks, complete CI availability after PR #29, owner manual UI acceptance and independent review. Do not equate the old preflight-only workflow with EUR-26's full security/test gates.

### Main integration and malformed-filter re-audit (2026-10-12)

- Current authority: audit/fix EUR-14, commit and push tested updates to the existing PR #28 branch, then prepare the primary local checkout for owner browser testing. No PR merge or Linear Done until acceptance. Original PR head `0a05a7d88efd37179755edb0d33ba27116a28414`; integrated main `1d461258125462d4576c24a67befae2aec027637` contains merged CI PR #29. The primary checkout was clean on main `c8c280c38f06f1485d149b29f3f48313a06b025b`, 48 commits behind remote; its prior sync is not verified as complete.
- Merge preparation preserves both the EUR-14 and EUR-26 checkpoint sections. Only WORKING_CONTEXT conflicted. Workflow, lockfiles, CSS/PostCSS configuration and sync scripts match integrated main; no UI replacement, dependency upgrade, history rewrite or CI gate weakening.
- Confirmed P2: `GET /admin/communities?status[]=ACTIVE` rendered an array through Blade and returned 500 (`htmlspecialchars` TypeError). A new regression failed before the correction. The listing now accepts only scalar ACTIVE/SUSPENDED/ARCHIVED filters and otherwise uses the unfiltered list. Regression covers array/nested-array/unknown/empty values without creating audit records; green result: 1 test / 17 assertions, exit 0.
- Final verification used this integrated dirty candidate in an ephemeral scool-app container, its own exact-lock installed vendor and newly built assets, read-only .env.example, synthetic testing key and guarded MySQL scool_test. Full `php artisan test --fail-on-skipped --fail-on-empty-test-suite`: 226 tests / 1853 assertions PASS, 175.61 seconds, exit 0; includes live Mailpit SMTP and all retention/authorization/progress regressions. No development DB reset or shared inbox deletion.
- `npm ci` reported zero vulnerabilities; `npm run build` passed (59 modules). Full `vendor/bin/pint --test`: 143 files PASS. `vendor/bin/phpstan analyse --configuration=phpstan.neon --memory-limit=512M --no-progress`: no errors. `bash scripts/verify-agent-contract.sh --require-tracked`: 11 files PASS. Graph refresh uses alias S-cool-pr28 and typed schema-4 analyzer receipts; Eloquent/framework callers are lower-bound. PDG indexing was requested, but no PHP taint/dependence coverage or safety from zero findings is claimed; source and real regressions remain authoritative.
- Pending: final staged publication gate and push, current-head full GitHub CI, approved primary-checkout runtime preparation, then owner manual testing and normal review/merge. sync-local.sh remains main-only; use it after acceptance/merge, not to preview an open PR. Do not infer visual acceptance, another host's success or CI success from these local results.

### Modal backdrop correction (2026-10-12)

- Contract: reported EUR-14 Suspend/Cancel/Confirm defect; authorized minimal shared modal fix and regression verification. Branch `eur-14-platform-admin`, base HEAD `4e8cdc24885f5c6b8471daad96fe6bc41deee16b`; initially clean. No publication, merge or Done performed for this correction.
- Browser diagnosis: the rendered panel had `position: static`, `transform: none`, `z-index: auto`. Hit-testing the Confirm center returned the backdrop DIV, not the button. The backdrop's close handler intercepted panel clicks; backend PASS did not establish UI acceptance.
- Diff: add `relative z-10` to the existing shared modal panel, preserving dimensions, appearance, transitions and handlers. Consumers are admin Suspend/Reactivate and Profile account deletion. Add a rendered-markup regression to FrontendScaffoldTest; record the authorized in-scope correction policy in AI_WORKFLOW. No dependencies, authorization contracts, data or account credentials changed.
- Red/green: `docker compose -p scool exec -T -e APP_ENV=testing -e DB_DATABASE=scool_test app php artisan test --filter=test_alpine_modal_component_renders_in_profile_view` failed before the fix (1 failed / 6 assertions). After the fix, `--filter='FrontendScaffoldTest|PlatformAdministrationTest'` passed: 24 tests / 194 assertions, 16.22 seconds, exit 0. Guarded MySQL scool_test only; no development reset. The markup assertion pins explicit stacking classes, not browser behavior.
- Verification: `npm run build` passed (59 modules); targeted Pint passed (1 file); `git diff --check` passed. Browser hit-testing now returns both Cancel and Confirm buttons, panel position relative / z-index 10. Cancel closes Suspend; empty-reason Confirm keeps it open with required-field feedback. Profile deletion modal Cancel is also pointer-accessible; no deletion was submitted. No development community was suspended/reactivated by these probes.
- Handoff: local uses the corrected component and rebuilt assets; final screenshot captured after panel opacity reached 1. Owner can continue valid-reason Suspend/Reactivate tests. Changes remain uncommitted; prior remote CI success is for base 4e8cdc2, not this dirty correction. Graph results are not evidence for CSS stacking or pointer behavior; source, browser hit-testing and tests are authoritative. Full-suite/remote CI reruns and final browser acceptance remain publication gates.

## EUR-26 - PHP runtime build cache (2026-10-12)

- Contract: current Linear EUR-26 CI baseline plus the approved request to speed the PHP image build using persistent layer cache. Branch `eur-26-ci-baseline`; base HEAD `f84bd8143bd6080e6750163b0e1d0a03a520492c`; checkout was clean before editing.
- Bounded plan/diff: replace only the plain PHP image build in `.github/workflows/ci.yml` with verified full-SHA-pinned Docker Buildx/build actions and GitHub Actions cache, scope `scool-php`. Keep Dockerfile, image tag `scool-php:ci`, PHP smoke check and all existing test/security gates unchanged. No parallel extension compilation or registry publishing.
- Flow: restore matching layers, build missing layers, load the image into the runner's Docker daemon, then reuse it for the existing smoke check, Trivy and PHP commands. Export layers for subsequent eligible runs. A cache miss still performs a real build; the first run can remain slow.
- Security/trade-offs: `push: false`, unchanged token permissions, and build-record artifact upload disabled to preserve the artifact allowlist. `pull: true` checks base images, but cached package-install layers do not refresh packages automatically; Trivy remains a blocking gate. Cache availability and transfer overhead affect timing; no measured speedup is claimed.
- Local evidence on the dirty candidate: Python/PyYAML parsing and assertions passed (exit 0), checking exact cache/load/push/pull settings, pinned-action format, step order and semantic equality of every other workflow setting against HEAD. `git diff --check` passed (exit 0). Action pins were checked against official release commit pages; these are static checks, not an Actions run.
- Pending: run the published candidate on GitHub, confirm the image is available to downstream steps, then compare cold/warm runs on the same unchanged Dockerfile/base inputs and inspect `CACHED` layers. GitHub execution, cache hits, timing and full CI acceptance remain unverified. No local container/database changes, commit, push, PR or Linear status update were performed.

### Final acceptance check (2026-10-12)

- Current checkout: `eur-26-ci-baseline`, HEAD `2abee269ef18747c3bcee07669179f9d2eaaffc0`, clean before this checkpoint update. PR #29 is open; main/base is `645db4dcbcbd690da06cdb868f01ad5688b6644e`.
- Successful PR run: https://github.com/Kerneural/S-cool/actions/runs/38207259630 (attempt 1). Actual checkout/tested merge-ref SHA: `6c010aaf5ff200377b5bc6506b32ffa2292853f6`, distinct from the candidate head. GitHub job/log reads confirm all configured steps succeeded, 206 tests passed with 1692 assertions, live Mailpit SMTP, database safety guard and deterministic/idempotent seed tests passed. Skipped/empty tests are blocking flags in the test command.
- Dependency audits, Pint, level-0 Larastan/PHPStan, Semgrep fixtures/application scan, redacted Gitleaks and Trivy gates passed. Buildx cache export succeeded; no warm-run speed comparison is available. The seven-day allowlisted evidence artifact is ID `11727758258`; its archive contents were not independently inspected here.
- Read-only GitHub API checks: branch protection returns `Branch not protected`; active ruleset `24407564` and effective main rules require one PR approval but no required status checks, with an administrator always-bypass role. This does not prove CI-enforced merge blocking. Initial sandboxed CLI network calls failed; narrowly approved read-only API calls succeeded, without repeating the failing route.
- Open AC: separate controlled test/style failure PR and corrected green run; approved required-check configuration and blocked normal-merge proof; accepted PR merge plus a full-pipeline main push run; checked-in CI operations/triage and static-analysis rationale. Existing main success runs use the earlier preflight-only workflow. Historical implementation failures do not replace the controlled negative experiment.
- Acceptance evidence and gaps were posted to Linear EUR-26; issue status remains In Progress. No application/container/database change, ruleset mutation, commit, push or merge was performed. Do not mark Done while these AC remain open. Graph-backed review was not claimed: this check used bounded CI source and exact-run GitHub evidence rather than PHP symbol analysis.

### Approved acceptance closure - in progress (2026-10-12)

- Authority: enable the exact required CI check, create a separate harmless red-to-green PR without merging it, and publish README/checkpoint updates to the existing PR #29. No authorization to merge PR #29 or bypass its review/check gate.
- Plan: preserve existing main rules, add the verified GitHub Actions context, demonstrate a Pint failure by whitespace alone, capture required-check/merge-state metadata without attempting a merge, then restore the original test byte-for-byte and verify green. Publish only README and the active checkpoint on `eur-26-ci-baseline`; no workflow/application changes.
- Required gate now applies to main: ruleset `24407564`, context `CI/ Runner Preflight`, integration ID `15368`, non-strict up-to-date policy. Existing one-approval requirement and administrator always-bypass role are unchanged; no bypass was used. Read-back of effective main rules confirms the new rule.
- Experiment: PR #33 https://github.com/Kerneural/S-cool/pull/33, branch `eur-26-ci-gate-proof`, red candidate head `3cfa2a49167fd6c08eba571668456e150bd4afb6`. Starts from the full CI candidate `2abee269ef18747c3bcee07669179f9d2eaaffc0`, not preflight-only main. Only indentation in `tests/Unit/ExampleTest.php` changes; a whitespace-stripped comparison verifies unchanged PHP tokens/assertions. Initial run `38208448473` is in progress; no red/green conclusion yet.
- GitHub GraphQL identifies the experiment check as required and reports merge state BLOCKED while it is running; one approval is also missing. This is read-only metadata, not a failed merge attempt and not yet the controlled red result.
- Original successful-run artifact `11727758258` was inspected in memory via Python/ZIP/XML: exact five-path allowlist, tested SHA `6c010aaf5ff200377b5bc6506b32ffa2292853f6`, 206 JUnit cases, no failures/errors/skips or system-output elements. No ZIP extraction or sensitive raw output was published.
- Connector write access returned integration 403; the approved authenticated GitHub CLI route created the experiment successfully. Failed connector writes are not publication evidence. Local branch, environment, containers and database are unchanged.
- README now documents triggers, gates, reproducible local checks, cache/artifact boundaries, first-failure triage, level-0 Larastan rationale and narrow Semgrep coverage. Pending: controlled red/block evidence, byte-identical restoration and green; README/checkpoint publication and its current-head CI; normal approval/merge of #29 followed by full main CI. EUR-26 remains In Progress.

### Controlled failure result (2026-10-12)

- Red run: https://github.com/Kerneural/S-cool/actions/runs/38208448473, head `3cfa2a49167fd6c08eba571668456e150bd4afb6`, tested merge-ref `56eab32` (short SHA from checkout log). Job `114677043673` failed at `Check PHP formatting`: exactly one `statement_indentation` violation in `tests/Unit/ExampleTest.php`, Pint exit 1. Downstream steps were not accepted as passing.
- At that red head, read-only GitHub GraphQL returned `CI/ Runner Preflight` = COMPLETED/FAILURE, `isRequired: true`, and PR #33 `mergeStateStatus: BLOCKED`. Review was also required. No merge API call or admin bypass was used; this is required-check/blocked-state evidence, not an attempted merge or proof against administrator bypass.
- Corrective commit `4aa62c4e80af0097973e48aa8845d96b0f4a87cd` restores the original test blob `5773b0ceb771e176520d5a9c1efe5e918878318d` byte-for-byte. PHP assertions, workflow and production code are unchanged. Corrected run is pending; PR #33 must never be merged.

### Corrected green and documentation handoff (2026-10-12)

- Corrected run https://github.com/Kerneural/S-cool/actions/runs/38208850421 succeeded, head `4aa62c4e80af0097973e48aa8845d96b0f4a87cd`, tested merge-ref `96b66bb92768d8f412914a081f2adac8f0ef3e8b`. Pint and all other configured steps passed; 206 tests, 1692 assertions, live SMTP passed. Git tree `e3b047b881274bbd78ca8f70a43240fa347b5e2a` exactly equals the original candidate tree: no code/test/workflow delta survives the experiment.
- Warm Buildx log shows the dependency-install and extension-compilation layers CACHED. This confirms reuse in this run; mutable base images/cache eviction can still cause later misses, so no guaranteed duration is claimed.
- Publication scope is README and the active EUR-26 checkpoint only. Diff/whitespace checks passed. GitNexus's bounded documentation map reported only Markdown headings and no execution flows; indexed heading locations can be stale, so source diff/file allowlist is authoritative here, not a graph-backed runtime safety claim. No application symbols were changed.
- Pending after documentation publication: required CI on the updated PR #29 head, independent approval and normal merge of #29, then the full-pipeline main push run. Existing admin bypass remains a documented limitation, not an acceptance shortcut. PR #33 is evidence-only and must not be merged; EUR-26 is not Done yet.

- Manual acceptance: all prescribed EUR-14 browser scenarios passed on the local modal correction, as confirmed during acceptance. Publication and current-head CI remain pending.

## EUR-15 - Hardening and local demo (2026-10-13)

- Contract: Linear EUR-15 / M5. Deliver a reproducible local golden/failure-flow demo with deterministic data, production assets, security evidence and no known Critical/High blocker. Hosting, production payment and new product features remain out of scope.
- Branch/base: `eur-15-hardening-local-demo` from clean `main` at `d62d95eef45e7942793e3f9cf6c02d29ee428241`. No pre-existing local changes were present. Linear was In Progress when work began.
- Baseline before edits: `bash scripts/verify-fresh-setup.sh --mode verify` passed on clean base in 238 seconds: 259 tests / 2083 assertions, Pint 162 files, production Vite build (60 modules), five-service runtime, live Mailpit and queue smoke. This is one-host evidence only.
- Candidate: Bootstrap now calls a local/testing-only `DemoContentSeeder`. A fresh seed creates two private communities owned by the demo creator: one free tenant with an active demo member and one 100,000 VND paid tenant without member access. Both tenants receive deterministic Feed, published Classroom and Calendar records; the free tenant includes one completed and one incomplete lesson. Re-seeding uses stable natural keys, preserves existing accounts/state/content and fails rather than attaching a reserved demo slug owned by another user. It never creates a paid entitlement or provider event.
- Integrated regressions: `DemoReadinessTest` verifies deterministic/idempotent data, creator multi-community visibility, member Feed/Classroom/Calendar/progress access, paid-tenant non-disclosure, Platform Admin visibility, invalid community input and empty Feed/Classroom/Calendar states. Targeted `DatabaseBaselineTest|DemoReadinessTest`: 10 tests / 112 assertions PASS. The isolated EUR-15 suite: 3 tests / 55 assertions PASS.
- Final local candidate verification: Verify passed in 245 seconds with 262 tests / 2138 assertions, Pint 164 files, production Vite build (60 modules), HTTP/runtime, live Mailpit and queue smoke. PHPStan level 0 passed with no errors. `npm audit --audit-level=high` found 0 vulnerabilities; `composer audit --locked` found no advisories.
- README contains the canonical M5 rehearsal: deterministic seed, free invitation, provider-backed SePay Test Mode, progress persistence, cross-tenant 404, member suspension/reactivation and Platform Admin suspension/reactivation. It explicitly separates automated negative tests from provider/manual and independent-host evidence; no credential, token or raw provider payload is recorded.
- Local UI smoke on the dirty candidate: after one additive `php artisan db:seed`, Creator Dashboard rendered both demo tenants and the free Feed, Classroom and Calendar records. Demo Member Dashboard rendered the free tenant but not the paid tenant; Classroom showed 50% progress and a direct paid Feed URL rendered 404. Captured browser console errors were empty. No invitation, payment, progress or lifecycle mutation was submitted in this smoke.
- Security/trade-offs: all seed writes remain local/testing only and additive. The development database received only missing synthetic demo rows; no reset, migration, volume deletion, inbox deletion, queue flush, role escalation or secret change occurred. Seed reruns intentionally do not repair user-edited demo records, so an edited local dataset may need a newly created demo community/account rather than destructive reseeding.
- Pending acceptance: current-head GitHub CI after publication; full owner/browser rehearsal for invitations and member/admin transitions; fresh Bootstrap evidence from a second physical machine; team cross-review/demo; provider-backed paid flow on this candidate if the earlier EUR-12 evidence is not accepted as unchanged-input evidence. These are not claimed complete. No commit, push, PR, merge or Linear Done has been performed.

## Functional completion planning handoff (2026-10-13)

- Scope: documentation-only planning authorized for mentor-reference feature completion; functionality first, visual redesign later. No feature implementation or Linear/GitHub mutation is part of this checkpoint.
- Source snapshot: clean `main`, HEAD `2996aa6a24e4f8821d9351c0beb6460b554b8d6e` before documentation edits. Routes, core models/controllers, community policy and mutation helpers were inspected. Current source includes membership control and SePay transfer-webhook Test Mode; older architecture checkpoints claiming their absence are not current evidence.
- Canonical implementation handoff: `docs/03_DELIVERY_PLAN.md`, section 17. Product direction: `docs/01_PRODUCT_SCOPE.md`, section 20. Mentor observations: Product Scope, section 19; no new live survey/backend verification was performed.
- Local F-xx identifiers are draft plan keys, not existing Linear IDs or progress. Transfer the selected issue into Linear using the canonical template before implementation; resolve its actual dependencies/metadata. Record its technical checkpoint here when work starts. Do not execute every draft in one change.
- Evidence limits: source review supports the gap map, not new runtime/CI acceptance. Historical MVP acceptance gaps remain separate; this planning work does not close them. Commerce and messaging decisions are explicitly gated in the plan.
- Documentation verification: `git diff --check` passed; a bounded PowerShell structural check confirmed all 18 implementation drafts contain outcome/scope/contracts/AC/dependencies/security/verification/evidence and that F-19 acceptance exists. `bash scripts/verify-agent-contract.sh` passed for 11 shared files/routing/privacy (not agent compliance). The sandbox Bash invocation exited 1 without output; one approved read-only invocation completed with exit 0, rather than repeating the same failing route. No application tests, runtime writes, publication or issue-status changes occurred.

## Shared design reference handoff (2026-10-13)

- Scope: combine the accepted visual direction with the supplied design-system outline into root `design.md`; route UI work through it. Branch `f01-f02-workspace-directory`, HEAD `2996aa6a24e4f8821d9351c0beb6460b554b8d6e`; preserve the four existing completion-plan documentation edits. No feature/CSS/runtime implementation or publication is included.
- Verified baseline: Tailwind v4/PostCSS and legacy `@config` coexist; app/guest layouts and font extension still use Figtree. Teal/Be Vietnam Pro are target decisions, not applied or visually verified changes. The document distinguishes member learning/community screens from creator data-management screens.
- Optional UI/UX Pro Max upstream README was checked for Laravel/Blade/Tailwind reference support and its persistent design-generation behavior. No external skill/CLI/package was installed or executed. `design.md` remains canonical; local IDE integration is optional/private and must not create a competing design source or override approved issue/backend contracts.
- Next UI task reads `design.md` plus its issue scope, inspects current components and applies only required functional controls until a scoped visual-adoption issue is started. Representative Feed/Lesson/Creator screens gate broad styling rollout. All rendered UI/browser, font/token application and usability acceptance remain pending.
- Verification: agent-contract script passed (11 existing shared files); a separate bounded structural check passed for all 10 design sections, routing from AGENTS/workflow/docs index, unignored `design.md`, and diff whitespace. The existing structural script does not itself enforce the new design document or prove compliance. Concurrent application edits appeared during verification (profile request/user fields and a new profile migration); they were neither edited nor audited by this documentation task.

## F-01 & F-02 — Contextual Workspace, Switcher, Directory & Profiles (2026-10-13)

- Contract: F-01 (Contextual community workspace and switcher) & F-02 (Member directory and community-visible profiles) from `docs/03_DELIVERY_PLAN.md` Section 17.6. Branch `f01-f02-workspace-directory`, base HEAD `2996aa6a24e4f8821d9351c0beb6460b554b8d6e`. Pre-existing documentation edits preserved.
- Implemented F-01:
  - Contextual workspace header component `<x-community-workspace>` (`resources/views/components/community-workspace.blade.php`) integrated across Feed, Classroom, Events, Directory/Members, About/Show, and creator management views (Settings, Invitations, Manage Members).
  - Community Switcher: lists other active accessible communities for the authenticated user (`accessibleTo->active`). Switching navigates to target community Feed (`route('communities.posts.index', $other)`), explicitly dropping nested item IDs. Inactive communities are excluded from the switcher.
  - Dashboard search and filtering: `CommunityController@index` supports `filter` (`all`, `owned`, `joined`) and keyword search `q` on community name, deduplicated so overlapping creator/member ownership does not produce duplicate cards. Paginated with query string preservation.
- Implemented F-02:
  - Forward migration `database/migrations/2026_10_13_040001_add_bio_and_avatar_path_to_users_table.php` adding nullable `bio` (text) and `avatar_path` (string) to `users` table. `User` model `$fillable` updated.
  - Profile customization: `ProfileUpdateRequest` validates `bio` (max 500 chars) and `avatar` (`image`, mimes: jpeg, png, webp, max 2MB). `ProfileController@update` handles upload to private disk (`'local'` under `'avatars'`), deletes obsolete avatars, and notice is added to profile form regarding cross-community visibility.
  - Directory & Profiles: `DirectoryController` implements tenant-scoped `index` (lists active members and creator with role badges, bio preview, search `q`, zero emails/private metadata disclosed), `show` (renders member profile detail within community; returns fail-closed 404 for foreign/unrelated users), and `avatar` (streams avatar from private storage with private cache control or returns default SVG avatar placeholder).
  - Routes in `routes/web.php`: registered under community scope with `withoutScopedBindings()` on `{user}` to support custom authorization of both creator and members. Added `profile.avatar` for authenticated user.
- Automated tests:
  - `tests/Feature/CommunityWorkspaceSwitcherTest.php`: 5 tests / 32 assertions PASS (AC-01 through AC-05).
  - `tests/Feature/MemberDirectoryProfileTest.php`: 5 tests / 82 assertions PASS (AC-01 through AC-05).
  - Regression and full suite: `docker compose -p scool exec -T -e APP_ENV=testing -e DB_DATABASE=scool_test app php artisan test --fail-on-skipped --fail-on-empty-test-suite` passed with 272 tests / 2,252 assertions, exit 0 in 174.90s.
  - Code style: Pint passed on 168 PHP files with zero issues (`vendor/bin/pint --test`).
  - Frontend assets: `npm run build` completed successfully (60 modules, exit 0).
  - Agent contract: `scripts/verify-agent-contract.sh` PASS (11 shared files).
- Handoff & Audit Limits:
  - Prepared for Codex audit. Commit, push, PR creation, merge, and Linear status changes remain pending explicit user authorization.

### F-01 & F-02 — Post-Audit Hardening (2026-10-13)

- Audit Findings Resolved:
  1. Avatar upload atomicity & transaction rollback: Avatar upload and user update in `ProfileController@update` wrapped in `DB::transaction()`. If commit fails, newly uploaded avatar file is immediately cleaned up (`Storage::disk('local')->delete()`), and previous avatar is preserved. Obsolete avatar is only deleted after successful transaction commit. Added `test_avatar_upload_rolls_back_and_cleans_new_file_if_database_update_fails()`.
  2. Cache & Security Delivery: Replaced 24-hour cache with strict `Cache-Control: private, no-store` and `X-Content-Type-Options: nosniff` for avatar streaming in `DirectoryController@avatar` and `ProfileController@avatar`. Validated avatar file path strictly with regex `\Aavatars/[a-zA-Z0-9_\-\.]+\.(?:jpg|jpeg|png|webp)\z`. Added `test_avatar_response_contains_strict_cache_and_security_headers_and_guards_against_traversal()`.
  3. Invalid/malformed input guards: Both `CommunityController@index` (dashboard/switcher) and `DirectoryController@index` (member directory) safely handle array parameters (`?q[]=`, `?filter[]=`) and clamp search strings to max 100 characters using `is_string()` and `mb_substr()`. Added `test_community_index_safely_handles_array_and_oversize_filter_and_search_input()` and `test_member_directory_search_safely_handles_array_and_oversize_search_input()`.

## F-03 — Community About, Rules & Functional Settings (2026-10-13)

- Contract: F-03 from `docs/03_DELIVERY_PLAN.md` Section 17.6.
- Implementation:
  - Forward migration `database/migrations/2026_10_13_050001_add_rules_and_intro_video_url_to_communities_table.php` adding nullable `rules` (text) and `intro_video_url` (string, max 500) to `communities` table.
  - Model & Service: `Community` `$fillable` updated. Accessor `intro_video_embed_url` provides safe embed URLs for YouTube and Vimeo using `VideoEmbedService`.
  - Validation: `rules` capped at 5,000 characters; `intro_video_url` validated via `VideoEmbedService::isValid()`.
  - Active member count: Strictly counts memberships with `status === 'ACTIVE'`, excluding suspended/removed members and creator. No email or private member metadata disclosed.
  - Views & Routes: Dedicated About tab and route `GET /communities/{community:slug}/about` (`communities.about`) rendering embedded video, numbered rules, community metadata and creator profile. Community edit view updated for creator rules/video configuration.
- Automated tests:
  - `tests/Feature/CommunityAboutSettingsTest.php`: 5 tests / 41 assertions PASS (AC-01 through AC-05).

## F-04 — Feed Categories, Filtering & Pinned Posts (2026-10-13)

- Contract: F-04 from `docs/03_DELIVERY_PLAN.md` Section 17.6.
- Implementation & Audit Hardening:
  - Forward migration `database/migrations/2026_10_13_060001_create_post_categories_and_add_category_and_pinned_to_posts_table.php` creating `post_categories` with unique `[community_id, slug]`; adds `category_id` FK (nullable, `nullOnDelete`) and `pinned_at` timestamp to `posts`.
  - Default categories seeded: `General`, `Announcements`, `Questions`, `Introductions` via `PostCategory::seedDefaultsFor($community)`. Seeded during migration, in `CommunityController@store` on creation, and in `DemoContentSeeder`.
  - Read-only GET feed: `PostController@index` is strictly read-only and never writes or auto-seeds categories on GET.
  - Creator-only Announcements: Only the community creator can post with the Announcements category (`isAnnouncements()`); non-creators are rejected with validation errors.
  - Creator-only Pinned Posts: `PostController@pin` and `@unpin` are creator-only and idempotent. Pinned posts sort first in the feed regardless of creation date.
  - Inactive Community Guards: `PostCategoryController@store`, `@destroy`, `PostController@pin`, and `@unpin` verify `$community->isActive()` (404) and re-check inside row-locked DB transactions (`lockForUpdate`).
  - Category Management UI: Minimal, non-intrusive collapsible creator control in `resources/views/communities/posts/index.blade.php` allows creating new categories and deleting existing categories with post uncategorization and confirmation.
- Automated tests:
  - `tests/Feature/CommunityFeedCategoriesTest.php`: 8 tests / 75 assertions PASS (AC-01 through AC-05 + audit hardening).

## F-05 — Idempotent Likes and Personal Saved Posts (2026-10-13)

- Contract: F-05 from `docs/03_DELIVERY_PLAN.md` Section 17.7.
- Implementation:
  - Forward migration `database/migrations/2026_10_13_070001_create_post_likes_and_post_bookmarks_tables.php` creating `post_likes` (`community_id`, `post_id`, `user_id`, `is_active`, unique on `[post_id, user_id]`) and `post_bookmarks` (`community_id`, `post_id`, `user_id`, unique on `[post_id, user_id]`).
  - Models: Created `PostLike` (with `scopeActive`) and `PostBookmark`. Updated `Post` with `likes()`, `activeLikes()`, `bookmarks()`, `isLikedBy()`, and `isBookmarkedBy()`. Updated `User` with `postLikes()` and `postBookmarks()`.
  - Controller: `PostReactionController` implements `like`, `unlike`, `save`, `unsave`, and `saved` (personal saved posts).
  - Idempotency & F-10 Identity: Like row preserves original identity and `created_at` timestamp across unlike/re-like cycles via `is_active` boolean flag. Repeated Like/Unlike or Save/Unsave requests are idempotent without count drift.
  - Security & Authorization: Self-like is rejected with validation error without changing counts; self-bookmark is allowed; non-members, suspended members, soft-deleted posts, and inactive/suspended/archived communities are rejected with 404. Cross-tenant post operations fail-closed with 404.
  - Views: Updated `resources/views/communities/posts/index.blade.php` and `resources/views/communities/posts/show.blade.php` with Like button (counter and active state) and Save button. Created `resources/views/communities/saved.blade.php` rendering personal bookmarks within community.
  - Scoped Eager-Loading Hardening: In `PostController@index`, `PostController@show`, and `PostReactionController@saved`, eager-loaded `likes` relation strictly loads only the active like of the authenticated viewer (`where('is_active', true)->where('user_id', $user->id)`), preventing unbounded memory growth. Total likes count continues to be accurately aggregated via `withCount(['likes as active_likes_count' => fn ($q) => $q->where('is_active', true)])`. Added test `test_eager_loaded_likes_relation_only_contains_active_like_of_current_viewer_while_active_likes_count_includes_all_likes`.
  - Automated tests: `tests/Feature/CommunityPostReactionsTest.php`: 11 tests / 101 assertions PASS (AC-01 through AC-05 + scoped eager loading).

## F-06 — Community-Scoped Search (2026-10-13)

- Contract: F-06 from `docs/03_DELIVERY_PLAN.md` Section 17.7.
- Implementation:
  - Controller: `SearchController` (`app/Http/Controllers/SearchController.php`) implements `index` handling community-scoped search across `posts`, `courses`, `lessons`, and `members`.
  - Route: Registered `GET /communities/{community:slug}/search` (`communities.search`) under community route group with `scopeBindings()`.
  - Validation & Security:
    - `type` restricted to allowlist `['posts', 'courses', 'lessons', 'members']` (defaults to `posts`); invalid type throws `ValidationException`.
    - `q` validated between 2 and 100 characters; malformed/array input safely rejected with `ValidationException`.
    - Parameterized MySQL LIKE with `addcslashes($search, '%_\\')` escaping literal SQL wildcards (`%`, `_`, `\`).
    - Fail-closed access control: requires `$community->isActive()` and active membership or creator role; suspended/removed members and outsiders receive 404.
  - Ancestry & Permission-Aware Queries:
    - Posts: scoped to active community, excludes soft-deleted posts; eager loads `author:id,name` and `category`.
    - Courses: scoped to active community; non-creators strictly see `PUBLISHED` courses only; creators can see draft courses with explicit `Draft` badge.
    - Lessons: scoped via `whereHas('courseSection.course')` to active community; non-creators strictly see `PUBLISHED` lessons within `PUBLISHED` courses; creators can see draft lessons with explicit `Draft` badge.
    - Members: scoped to active community members and creator; selects only public profile fields (`id`, `name`, `bio`, `avatar_path`), zero emails or private credentials disclosed.
    - Snippets: HTML stripped and length-capped (`Str::limit(strip_tags(...), 160)`), safely escaped in Blade.
    - Tab Counts: calculated with identical permission/status filters as the query results.
    - Query Efficiency: only the selected result type is paginated (`15` per page with query string preservation), avoiding loading full tables into memory.
  - UI Templates:
    - Search view: `resources/views/communities/search.blade.php` with workspace header, keyword input, tabbed type filters with count badges, paginated results, and idle/zero-result empty states.
    - Workspace navigation: Added `Search` tab to `resources/views/components/community-workspace.blade.php`.
- Automated tests:
  - `tests/Feature/CommunitySearchTest.php`: 5 tests / 62 assertions PASS (AC-01 through AC-05).
  - Combined F-01 through F-06 & Demo readiness suite: 46 tests / 456 assertions PASS, 0 failures.
  - Code style: Pint passed on 181 files (`vendor/bin/pint --test`).
  - Frontend assets: `npm run build` passed (60 modules, 3.42s).

## F-07 — Calendar Month and List Navigation (2026-10-13)

- Contract: F-07 from `docs/03_DELIVERY_PLAN.md` Section 17.7.
- Implementation:
  - Model: Added `startsAtInTimezone(string $timezone): Carbon` and `endsAtInTimezone(string $timezone): Carbon` to `App\Models\Event`.
  - Controller: `EventController@index` updated with:
    - Allowlisted `view=month|list` (default `month`).
    - Validated IANA `display_timezone` (default `Asia/Ho_Chi_Minh`), preserved across links and view switching.
    - Strict `month=YYYY-MM` validation (default current month in display timezone).
    - Bounded UTC range queries: converts month start/end in `display_timezone` to UTC instants and queries overlapping intervals (`starts_at <= endUtc AND ends_at >= startUtc`).
    - Grid generation: constructs a 7-column calendar matrix with leading and trailing padding days, mapping events to their displayed day in `display_timezone`.
    - Payload capping: capped at 100 events per month view with fallback notification to list view when dense.
    - Chronological list view: preserved existing pagination when no specific month is requested, and filtered by bounded month when `month` is set.
  - Views: `resources/views/communities/events/index.blade.php`:
    - Toolbar with Prev/Next month navigation, Month/List view toggle buttons, and timezone selector dropdown.
    - Display timezone indicator pill and event pills showing display time alongside original event timezone labels.
    - Cancelled events visibly labelled with `Cancelled` badges and omitting member meeting links.
  - Automated tests: `tests/Feature/CommunityEventCalendarTest.php` (4 tests / 24 assertions PASS, covering AC-01 through AC-05). Total event test suite: 28 tests / 147 assertions PASS.

## F-08 — In-App Notification Center (2026-10-13)

- Contract: F-08 from `docs/03_DELIVERY_PLAN.md` Section 17.7.
- Implementation:
  - Database Schema:
    - Forward migration `database/migrations/2026_10_13_080001_create_notifications_table.php` creating `notifications` table with UUID primary key, `notifiable` polymorphic relation, `community_id`, unique `dedupe_key`, `data` json, `read_at`, and timestamps.
    - Added `published_revision` column (unsigned int default 0) to `courses` table for publication transition tracking.
  - Models:
    - `App\Models\AppNotification`: extends `Illuminate\Notifications\DatabaseNotification`. Implements `isAvailableFor(User $viewer)` performing fresh runtime access checks (community active, recipient permitted, post/comment not deleted, course published, membership active); `summary()` for safe rendering; `targetUrl()` for deep links; `createNotification()` with unique constraint error handling for idempotency; and `unreadCountFor()` matching the visibility filter.
    - `App\Models\Community`: added `notifications()` relation.
    - `App\Models\User`: added `appNotifications()` relation.
    - `App\Models\Course`: added `published_revision` fillable, cast, and attribute.
  - Queued Jobs (with `$this->afterCommit()` transaction guards):
    - `SendPostReplyNotification`: dispatches on new comment; notifies post author if commenter is not author, with dedupe key `post_reply:comment_{id}:recipient_{author_id}`.
    - `SendMemberActivatedNotification`: dispatches on member acceptance or reactivation; notifies community creator with dedupe key `member_activated:{source_type}_{source_id}:recipient_{creator_id}`.
    - `SendCoursePublishedNotification`: fan-out job querying eligible active members and chunking in batches of 100 with dedupe key `course_published:course_{id}_rev_{revision}:recipient_{user_id}`.
  - Producer Integrations:
    - `CommentController@store`: dispatches `SendPostReplyNotification` after comment creation.
    - `AcceptInvitation@handle`: dispatches `SendMemberActivatedNotification` on invite acceptance.
    - `TransitionCommunityMembership@handle`: dispatches `SendMemberActivatedNotification` when reactivating suspended member to `ACTIVE`.
    - `SePayWebhookController`: dispatches `SendMemberActivatedNotification` on webhook payment activation.
    - `CourseController@store` & `update`: manages `published_revision` on DRAFT→PUBLISHED transitions and dispatches `SendCoursePublishedNotification`; updates while already PUBLISHED do not repeat.
  - Controller & Routes:
    - `App\Http\Controllers\NotificationController`: `index` (paginated personal inbox), `markAsRead` (enforces recipient ownership, 403 on foreign notifications), `markAllAsRead` (strictly bounded to viewer's visible inbox), and `follow` (safe deep links that mark as read and redirect to target, or display safe unavailable message without leaking private metadata).
    - Routes registered under community scoped group: `communities.notifications.index`, `communities.notifications.read`, `communities.notifications.read-all`, `communities.notifications.follow`.
  - UI Views:
    - `resources/views/communities/notifications/index.blade.php`: notification cards with type-specific icon badges, summary text, relative timestamps, unread badges, mark-as-read actions, "Mark all as read" button, and clean empty state.
    - `<x-community-workspace>`: added `Notifications` navigation tab with dynamic unread count pill badge derived from visible notifications.
  - Automated tests:
    - `tests/Feature/CommunityNotificationTest.php`: 7 tests / 59 assertions PASS (AC-01 through AC-06), including a real database queue worker probe (`Artisan::call('queue:work', ['--once' => true])`).
    - Full regression suites across F-01 through F-08: 71 tests / 820 assertions PASS.
    - Code style: Pint passed on 189 files (`vendor/bin/pint --test`).