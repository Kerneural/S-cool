# S-cool - working context

A checkpoint is historical context, not current Linear/Git/runtime evidence. Verify HEAD, status and source before resuming. Preserve separate issue sections.

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

| Issue | Current implementation | Main evidence |
|---|---|---|
| EUR-23 | Membership/invitation forward migrations, guarded models, factories/relations, restrictive FK/unique/enum constraints, locked state primitive | `MembershipInvitationSchemaTest` |
| EUR-22 | Paginated owned/ACTIVE-joined dashboard, separate creator writes/member reads, private cover upload/replacement/delivery | `PrivateCommunityAccessTest`, real desktop/mobile UI |
| EUR-24 | Creator create/revoke, seven-day email-bound invitation, verified-email single-use free acceptance, ID-only SMTP job | `CommunityInvitationFlowTest`, real MySQL queue/Mailpit integration, browser acceptance |
| EUR-25 | Community policy, accessible query scope, nested invitation scoped binding, transaction locks, cross-tenant/state/replay/race tests | All four new M2 test classes; real browser 404 isolation |

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
  - `docker compose -p scool exec -T app php artisan test --filter=CommunityMemberManagementTest`: **8 passed (33 assertions)** in 9.96s.
  - Full test suite: `docker compose -p scool exec -T app php artisan test --compact`: **189 passed (1535 assertions)** in 134.16s.
  - Code Style: `docker compose -p scool exec -T app vendor/bin/pint --test`: **128 files PASS**.
- Status & Authority:
  - Implementation completed and verified locally on `scool_test`.
  - Pushed to `origin/eur-13-creator-member-management` with pull request prepared. Awaiting review and PR merge authorization.
