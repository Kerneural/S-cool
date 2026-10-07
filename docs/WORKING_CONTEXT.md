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

## EUR-23 - implementation handoff (2026-10-07; not started)

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

## EUR-22 - implementation handoff (2026-10-07; not started)

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
