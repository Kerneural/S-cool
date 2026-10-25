# S-cool

Private learning-community platform built with Laravel, MySQL, Blade, Alpine.js, Tailwind CSS and Vite.
The current environment is local development, not a production deployment.

## Existing installation: sync before starting work

> [!IMPORTANT]
> **After a PR is merged, sync before starting your next issue.**
> Keep your work committed, start Docker, stop Vite and use a clean `main`.
> Keep your existing `.env`/key and `APP_URL=http://127.0.0.1:8080`.
> Local mail must use `MAIL_MAILER=smtp`, `MAIL_HOST=mailpit`, `MAIL_PORT=1025`.
> This is for an existing installation, not Bootstrap.

Receive this version of the script once, from the repository root:

```bash
git switch main
git pull --ff-only origin main
bash scripts/sync-local.sh
```

**Every later update needs only:**

```bash
git switch main
bash scripts/sync-local.sh
```

The script checks fresh local URL/SMTP configuration before stopping services,
then fetches main, stops web/worker, fast-forwards main and rebuilds PHP images,
installs locked dependencies, validates the local databases, applies pending migrations,
synchronizes curated demo content and bundled media, restarts the stack and runs Verify
(including `npm run build` and tests).
Wait for **`[PASS] Local sync complete`**, then create your next issue branch from main.

A URL/SMTP preflight rejection names each incorrect setting without printing its value.
Correct only those entries in your existing `.env` (or explicit environment overrides),
then rerun sync. Pull never replaces `.env`; an old `localhost` value stays until corrected.

Sync refuses dirty/feature branches, unpublished main commits, Vite's hot file,
missing installations and stacks owned by another checkout. It never stashes/resets work,
replaces `.env`, regenerates keys, resets data or deletes volumes.
Each machine keeps its own database. Sync fills missing demo accounts and nine standard
communities (four technical learning communities and five additional samples), then fills
bundled community/course covers through `LocalDemoSyncSeeder`. Existing accounts keep
their passwords, verification and roles. Existing community content, access states and
custom covers are retained; a slug owned by another creator stops sync for review.
This does not copy another developer's DB or recreate EUR/QA fixtures or the legacy
Demo Free/Paid Learning communities. Bootstrap's seed/repair path remains separate.
The demo step runs as `www-data` before the web/worker restart so new private image
files belong to the HTTP service user. No remote image downloads are required.
An interrupted demo step may leave completed additions; correct the reported failure
and rerun sync. Do not reset the DB. Changes to the curated catalog or bundled assets
must be reviewed in Git; existing edited records are not force-upgraded on each sync.
Review migrations before merging: sync applies them, but is not a backup or automatic rollback.
On failure the stack may stay stopped/partially updated. Fix the reported step and rerun;
do not rerun Bootstrap or delete data. An interrupted run may leave an empty
`.git/scool-sync.lock` directory: remove it only after confirming no sync is still running.

## First-time setup

Start Docker and run in Bash (Git Bash on Windows; WSL requires Docker integration).
Git, Node.js/npm and curl are required; host PHP, Composer and MySQL are not.
Node 22 is verified. On WSL, use Node/npm installed inside WSL.

```bash
git clone --branch main https://github.com/Kerneural/S-cool.git scool
cd scool
bash scripts/verify-fresh-setup.sh --mode bootstrap
```

Setup checks the shared repository contract, creates `.env`/key, installs locked dependencies,
builds assets, migrates/seeds, starts five services and verifies the environment.
Wait for `[PASS] bootstrap on this host`. No extra installation command or Vite dev server is needed.

- [Application](http://127.0.0.1:8080/login): register a new account.
- [Mailpit](http://127.0.0.1:8025): inspect local test mail; it does not deliver to real inboxes.
- MySQL: `127.0.0.1:3306`; the application connects internally using hostname `mysql`.

The canonical local application origin is `APP_URL=http://127.0.0.1:8080`, matching the IPv4 Compose port binding and HTTP probes. Use this host for login, browsing and generated email links. Session cookies are host-scoped, not port-scoped; do not mix `localhost` and `127.0.0.1`.
An explicit IPv4 address avoids hostname resolution and IPv6 address selection at this entrypoint; it is not a measured PHP-performance improvement. Inside Docker, keep `DB_HOST=mysql` and `MAIL_HOST=mailpit`: container loopback is not another service.

Bootstrap requires a fresh checkout without `.env`, dependencies or an existing S-cool stack/volume.
Ports 8080, 3306, 8025 and 1025 must be free.
On failure, investigate the failed step; do not delete `.env` or volumes to bypass guards.

## Local demo accounts

Bootstrap seeds these accounts automatically. Open [Login](http://127.0.0.1:8080/login)
and use any email below with password `password`.

| Display name | Email |
|---|---|
| Local Developer | `devops-demo@scool.local` |
| Demo Creator | `creator@scool.local` |
| Demo Member | `member@scool.local` |
| Demo Platform Admin | `admin@scool.local` |
| Test User | `test@example.com` |

These are synthetic local-only credentials, never for staging or production.
Persona names do not grant Creator/Member/Admin permissions. Re-running the seed
preserves existing accounts and passwords; it does not reset them to `password`.
You can also register a new account.

Fresh Bootstrap also creates two deterministic private communities owned by
`creator@scool.local`: `demo-free-learning` and `demo-paid-learning`. Both include
a post, published course/lessons and a scheduled event. The demo member is active
only in the free community and starts with one of two lessons complete. The paid
community costs 100,000 VND and still requires a real SePay Test Mode webhook to
activate an invited user. Re-seeding adds missing demo records but never resets
existing accounts, memberships or edited records.

## M5 local demo rehearsal

Run the automated gate first. On an existing installation, add any missing demo
records before Verify; neither command resets the development database.

```bash
docker compose -p scool exec -T app php artisan db:seed
bash scripts/verify-fresh-setup.sh --mode verify
```

Then rehearse this browser flow on `http://127.0.0.1:8080`:

1. Sign in as Demo Creator and confirm both seeded communities appear on Dashboard.
2. Create one temporary community and confirm its Feed, Classroom and Calendar empty
   states. Create a temporary post and confirm validation rejects an empty post.
3. Invite `test@example.com`, open the unique Mailpit link, sign in as that user and
   accept. A typed invitation URL without its token must not grant access.
4. Sign in as Demo Member, open the free course, toggle the remaining lesson and
   confirm progress survives a refresh. Direct paid-community URLs must return 404.
5. Invite `devops-demo@scool.local` to the paid community and complete the documented
   SePay Test Mode flow with the exact amount/reference. Confirm access is granted
   only after the authenticated webhook; cancel or wrong-amount attempts grant none.
6. As Creator, suspend and reactivate Demo Member with reasons; verify access is
   removed and restored. As Demo Platform Admin, suspend and reactivate a community
   with reasons; verify both creator and member access follow the platform state.

For acceptance evidence, record the exact Git SHA, Verify result, browser/role used,
SePay Test Mode delivery result and any unverified step. Never include credentials,
tokens, cookies, raw webhook payloads or personal data. Automated negative tests are
security evidence, but they do not replace the provider-backed paid-flow rehearsal or
fresh Bootstrap evidence from another machine.

## Platform Admin local review

After applying pending migrations, a verified Platform Admin can open
`/admin/communities`. Fresh Bootstrap grants the new `admin@scool.local` demo
account this role; an existing account keeps its current role when seeding/syncing.
Never promote an existing account automatically just because its email matches.

If upgrading an existing local installation, a trusted operator may explicitly
grant the role to the intended demo account. Open Tinker, inspect the selected
record first, and only then run the last line. This is a local test action, not
production provisioning; it does not change the password or email verification.

```bash
docker compose -p scool exec app php artisan tinker
```

```php
$user = \App\Models\User::where('email', 'admin@scool.local')->firstOrFail();
$user->only(['id', 'email', 'is_platform_admin']);
$user->forceFill(['is_platform_admin' => true])->save();
```

Admin actions require a reason and retain audit records. Accounts with platform
audit history cannot delete themselves; community content is not editable through
the platform role. The retention upgrade is a new migration, so existing audit
rows survive a normal forward migration; no reset or reseed is required.

## M2 private-community flow

The dashboard and **All** list every ACTIVE community's name, description and cover
for signed-in users, including new accounts without memberships (open [Dashboard](http://127.0.0.1:8080/dashboard)).
**Owned** lists your created communities; **Joined** lists your effective memberships.
Discovery does not grant content access: non-members see **Invitation required**,
while authorized users can open the community. The workspace switcher still lists
only communities you can access. Create a private community,
open **Invitations**, and invite an email. Check [Mailpit](http://127.0.0.1:8025), sign in at `http://127.0.0.1:8080/login` with that email,
verify it if needed, then reopen the email link and click **Accept invitation**.
Use the host configured in `APP_URL` (local default `http://127.0.0.1:8080`) for both login and invitation links so the browser sends the same session cookie. If upgrading from the earlier `localhost` default, change only `APP_URL` in your existing `.env`, then run `docker compose -p scool exec -T app php artisan config:clear` and `docker compose -p scool restart queue`. Do not replace `.env` or regenerate the key. Sign in again on `127.0.0.1`; previously delivered email links retain their old host, so revoke/reissue pending invitations if needed.
The link is single-use and expires after seven days. Members can view but not edit
settings, covers or invitations. Upload covers from **Edit Settings** (JPEG/PNG/WebP,
2 MB, maximum 4096 pixels per side). Creators can select FREE/PAID and set one whole-VND price.

### Paid access — SePay Test Mode only

Enable Test Mode in SePay and configure a separate test account and API Key webhook.
Set `SEPAY_TEST_MODE=true`, `SEPAY_ACCOUNT_NUMBER`, `SEPAY_BANK_NAME` and
`SEPAY_WEBHOOK_TOKEN` in the local ignored `.env`; never commit credentials.
Reload config with `docker compose -p scool exec -T app php artisan optimize:clear`.
Configure a temporary HTTPS tunnel to expose **only POST `/webhooks/sepay`**.
Do not expose the full app, Mailpit or MySQL. No tunnel is started by setup scripts.

Creator sets PAID and a price → sends an invitation → matching verified invitee
starts Paid Checkout → simulate an **incoming** transaction in SePay Test Mode,
using the displayed reference and exact amount. Do not scan a real-bank QR or send real money.
After login/email verification, reopen the original community invitation email link.
The verification email and a typed `/invitations/{id}` URL do not carry the invitation
token. The token is removed from browser history and kept only in the current page;
refresh/back navigation requires reopening the original link. Missing-token buttons
remain disabled, including when JavaScript cannot start.
Only an authenticated matching webhook activates membership; refreshing/status URLs cannot.
Cancel stops the pending attempt without access; reopen the original valid invitation to retry
with a new attempt. Old attempts keep their original price after a Creator changes pricing.
Changing FREE/PAID does not retroactively grant/revoke existing memberships.
The `SEPAY_SANDBOX` database provider label is retained for compatibility; the
active integration is bank webhooks in Test Mode, **not** Payment Gateway `ORDER_PAID`.

### Course purchases and manual 30-day renewal

Included courses require current community access. ONE_TIME courses additionally
require the matching retained course entitlement. Before purchase, only the
published title/price preview is delivered; outline, lesson content, embeds,
progress and lesson search remain locked. Course payments never activate membership.

Creators select **One-time lifetime access** or **30 UTC days** in access settings.
Existing memberships remain lifetime after the forward upgrade; changing pricing
never retroactively converts them. New term admission snapshots its 30-day duration
and whole-VND price. Access stops exactly at expiry, including when queue/scheduler
processes are offline. No automatic debit or grace period is provided.

Open **My Access** from the account menu to inspect your own expiry and start an
eligible manual Test Mode renewal, including after expiry. Verified renewal extends
from the later of payment time/current expiry by exactly 30 UTC days, once per
attempt. Revoked states cannot be restored through renewal. Pending attempts retain
their original price/term. Existing term members keep their term if billing changes;
new renewals use the current paid price, and FREE communities do not offer paid renewal.
Use the SePay simulator only; never send real funds.

The forward migration `2026_10_13_170001_add_fixed_term_membership_access.php`
adds these fields without resetting data. Apply it through the normal authorized
upgrade below and restart the queue so workers load the updated access predicates.
No new scheduler or dependency is required.

After pulling new migrations into an existing installation, run the forward upgrade
below, then the normal Verify command. Bootstrap already applies them on fresh setup.

```bash
docker compose -p scool exec -T app php artisan migrate --no-interaction
docker compose -p scool restart queue
npm run build
```

## Review and fix a teammate's PR

> [!IMPORTANT]
> **Check out PR branch → fix/test → commit/push → latest CI → comments/review → merge → sync main → Linear Done.**
> Push fixes to the existing PR's source branch; do not create a duplicate PR.
> Run each command separately and stop on failure. Never reset or force-push to bypass a problem.

### 1. Get the PR code

Copy the source branch from GitHub. Example: `eur-14-platform-admin`; replace it
everywhere below with the actual branch. This example assumes a same-repository PR
and permission to push to its branch; coordinate with the author otherwise.

```bash
git status                         # Start clean; preserve unfinished work first
git fetch origin                   # Download commits; does not change local code
git switch --track origin/eur-14-platform-admin # First local checkout only
# If the branch already exists, use instead: git switch eur-14-platform-admin
git pull --ff-only origin eur-14-platform-admin
git rev-parse HEAD                  # Compare with the PR's latest commit
git diff origin/main...HEAD         # Inspect the PR changes
```

### 2. Fix and test on that branch

Read the Linear outcome/AC, inspect the diff and make only scoped corrections.
Switching branches changes code, **not** installed dependencies, database schema or
running workers. Prepare the runtime using [Daily operations](#daily-operations):
install changed lockfiles, rebuild affected images and apply reviewed forward
migrations as needed. Do not use Bootstrap, database resets or volume deletion.
Verify does not install dependencies or migrate the development database.

```bash
bash scripts/verify-fresh-setup.sh --mode verify
```

Stop Vite before Verify. Manually test the AC on this exact branch, including
validation and denied-access cases; passing backend tests alone does not prove UI
behavior. Record results/gaps in the issue section of `docs/WORKING_CONTEXT.md`.
Never run `sync-local.sh` to preview an open PR: it requires clean `main`.

### 3. Commit and update the same PR

Replace the example file paths and commit message with the actual correction.

```bash
git diff
git diff --check
git status
git add path/to/changed-file path/to/changed-test # Explicit paths; include checkpoint
git diff --cached                  # Check exactly what will be committed
git commit -m "fix: describe the correction (EUR-XX)"
git push origin HEAD:eur-14-platform-admin
git rev-parse HEAD                  # Record the new PR head
```

If new remote commits arrive or Git refuses fast-forward/push, inspect and
integrate deliberately, then rerun affected checks. Do not overwrite teammate
changes. For a new feature branch without a PR, create one after pushing instead.

### 4. Comment, review and merge

Wait for CI on the **new revision**, not an older green run. Add this evidence to
the existing GitHub PR and Linear issue; replace placeholders with observed facts:

```text
Revision: [PR head SHA]; CI tested merge-ref: [SHA / run link]
Correction: [scope and reason]
Automated checks: [commands and actual results]
Manual checks: [scenarios and actual results]
Remaining gaps: [none, or explicit blockers]
PR: [URL]
```

Keep Linear **In Review** until accepted. Merge on GitHub only after manual
acceptance, current-revision CI and required approvals; never bypass a blocked
gate. Integrating main or changing the PR again requires re-verification.

### 5. Return to main and close the handoff

After GitHub confirms **Merged**, ensure the PR branch is clean:

```bash
git status
git switch main
git pull --ff-only origin main
bash scripts/sync-local.sh
```

Wait for `[PASS] Local sync complete` and check the post-merge main CI. Comment
on Linear with the merged PR, accepted evidence and sync outcome; then mark the
accepted issue **Done**. Merge and local sync are separate results; report failures
instead of claiming success. Other members use the same main/sync sequence with
their unfinished work safely preserved first. Sync shares code/schema/runtime,
not each machine's database data.

## Daily operations

Run from the repository root. Always use project `scool` to keep the namespace independent of the folder name.

```bash
docker compose -p scool up -d --wait                    # Start the configured environment
docker compose -p scool ps                             # Service status
docker compose -p scool logs --tail=30 queue            # Worker process logs
docker compose -p scool exec -T app php artisan queue:failed
docker compose -p scool restart queue                  # After changing jobs/config
docker compose -p scool exec -T app php artisan db:seed # Add missing local demo users
docker compose -p scool down                           # Stop; preserve MySQL data
```

Frontend: use `npm run dev` for hot reload. Stop Vite and run `npm run build` to test built assets.
Application/job logs are in `storage/logs/laravel.log`, separate from worker process logs.

## Verification

```bash
docker compose -p scool exec -T app composer test       # Full test suite
docker compose -p scool exec -T app composer pint:test  # Code style
docker compose -p scool exec -T app composer verify     # Style + tests
bash scripts/verify-fresh-setup.sh --mode verify        # Build + runtime/queue + style/tests
bash scripts/test-setup-verification.sh                # Setup guard regressions
```

Verify requires the running stack with Vite stopped; it does not migrate/seed the development DB.
If `public/hot` remains, confirm Vite has stopped before removing that file.

## Consolidated manual acceptance checklist (F-01–F-18)

This is an execution script, not a PASS report. Record actual observations and pending
steps in the active F-19 checkpoint. Use the canonical `http://127.0.0.1:8080` origin
throughout; keep Creator, Member and Platform Admin in separate browser sessions.
Use the Local demo accounts above, whose existing passwords and display names may
have changed. Fresh fixtures enroll Demo Member only in Demo Free Learning and start
with one of two lessons complete. Inspect current fixtures before assuming access,
progress, totals or a commission rate. Apply only authorized forward upgrades.

Use unique run IDs and synthetic recipients such as `referral-<run-id>@example.test`;
never clear shared mail, reseed/reset development data or remove the shared Demo Member.
State-changing negative cases use dedicated synthetic participants/communities. Inspect
only the matching Mailpit message and keep invitation tokens, cookies, payment credentials
and raw webhook payloads out of evidence. No real transfer or production provider run.

### 1. Workspace, directory, About, feed and search (F-01–F-04, F-06)

1. As Creator, open `/communities`, then the free and paid communities in two tabs.
   Exercise switcher, Back and Reload; each header, URL and result must stay tenant-correct.
2. Open `/communities/demo-free-learning/directory`, a member profile and the community
   About page. Verify name/bio/avatar, hidden email/billing fields, escaped rules, cover and role.
3. Create a uniquely named category/post in an authorized synthetic fixture. Filter,
   search and pin/unpin; verify ordering, empty/no-result states and another tenant's
   exclusion. Search must withhold drafts/locked lesson content from an ordinary member.
4. Use an outsider session on saved directory/About/feed/search URLs: expect private
   denial without names, bodies or counts. Repeat relevant controls at a narrow viewport.

### 2. Classroom progress and calendar (existing learning baseline, F-07)

1. As Member, open `/communities/demo-free-learning/classroom`, then an included
   published lesson. Complete an unfinished lesson; verify persisted per-user progress
   after reload/login. 100% means all currently accessible published lessons completed.
2. An effective member without a paid-course entitlement may see its safe locked teaser
   and purchase control (HTTP 200); the outline/body/video and direct lesson URL remain
   inaccessible. An outsider, wrong tenant or expired/revoked member gets private denial.
3. Open Events, switch month/list and previous/next month, and check the displayed IANA
   timezone. Verify overlapping events and cancelled-event meeting-link privacy.

### 3. Engagement, notifications, onboarding and ranks (F-05, F-08–F-10)

1. Like/unlike and save/unsave a fixture post; duplicate submit/reload must not duplicate
   likes or points. Saved posts are self-only and remain tenant-scoped.
2. Perform the selected onboarding actions and refresh/login; completed tasks persist.
   Video acknowledgment is not proof of having watched a video.
3. Trigger a permitted reply notice with distinct actors; inspect Notifications, mark it
   read and reload. Ordinary members cannot read another user's notice or hidden content.
4. Compare Leaderboard/level totals to persisted eligible contributions. Revocation and
   content removal must follow the selected points/access contract, without invented ranks.

### 4. Asynchronous messaging (F-18)

1. As Member, open the free community Directory, open the owning Creator's profile and
   click Message. Send once; the latest page shows chronological messages and timestamps.
2. In Creator's separate session, open `/communities/demo-free-learning/messages`.
   Unread badges are in the inbox; the workspace Messages tab has no unread badge.
   Open the thread, reply, then refresh Member's session to see reply/read state.
3. With more than 30 incoming fixture messages, only the displayed page becomes read;
   older pages remain unread until opened. The inbox paginates and totals eligible pages.
4. Change the URL's tenant and use a nonparticipant/Platform Admin session: expect 404
   with no bodies/previews/counts. Enroll the same pair in a separate authorized fixture
   if needed to prove distinct conversations; an inaccessible tenant's 404 also passes.
5. Retry the same POST/client send ID: exactly one message. Submit blank/oversize/invalid
   input: visible errors, preserved valid draft/retry ID and no new message or server error.

### 5. Referral request and simulated ledger (F-17)

1. As Creator, set a positive rate, such as 15%, on `/communities/demo-free-learning/edit`;
   reject invalid rates without mutation. Use this tenant because the fresh Demo Member
   has effective access here; a paid-tenant request needs its own eligible membership.
2. As Member, open `/account/referrals`, select that eligible community and submit the
   unique synthetic recipient. Creator opens `/communities/demo-free-learning/referrals`
   and approves. Expect Approved/queued, or visible conflict feedback without an orphan invite.
3. Check only that recipient's Mailpit delivery; queued is not delivered. Approval alone
   earns no commission. The ledger stays Simulated/Unpaid and retains safe own history.
4. Provider-backed Test Mode admission/course success for an eligible first-touch referrer
   within the selected 30-day window must create the exact integer commission once.
   Renewal, self-referral, rejoin/replaced attribution and cutoff replay earn no new commission.

### 6. Admission, course purchase, expiry and renewal (F-12, F-15, F-16)

1. Creator invites a unique verified synthetic student to a paid fixture. In that student's
   session, open its own Mailpit invitation, accept and follow the checkout control.
   Verify exact immutable amount/purpose/reference and visible SePay Test Mode instructions.
2. Use the existing SePay Test Mode setup for a genuine provider-backed success. Confirm
   persisted payment/access and safe receipt/history; a local fake callback or rendered
   QR alone does not pass the provider gate. Test authentication/amount/purpose/destination,
   duplicate/delayed-state and rollback boundaries on guarded `scool_test`; never publish tokens.
3. Buy a selected paid course from its locked teaser. Confirm only that course entitlement
   is granted and retry creates no duplicate payment/entitlement; another course stays locked.
4. In a dedicated TERM-membership fixture, check exact UTC expiry and `/account/memberships`.
   Successful renewal extends the selected term once and does not revive a revoked/inactive
   membership. Existing lifetime membership stays lifetime; retained receipts grant no access.
5. Creator's member-management URL is `/communities/{slug}/members`. On dedicated synthetic
   users exercise SUSPENDED/REMOVED/LEFT; then saved URLs/refresh/new writes must deny every
   internal module. Test tenant suspension separately and preserve financial/content history.

### 7. Creator overview/orders/reports and platform boundaries (F-11–F-13, admin baseline)

1. As Creator, open `/communities/demo-free-learning/manage`,
   `/communities/demo-free-learning/orders` and `/communities/demo-free-learning/reports` separately.
   Compare totals/filters with known persisted fixtures; payments are labelled Test Mode.
2. Member reports a fixture post/comment; Creator dismisses or resolves it with a reason.
   Duplicate submission/resolution is idempotent; other tenants/members cannot read the queue.
3. As Platform Admin, open `/admin/communities`. Suspend/reactivate a dedicated community
   with a reason; verify retained audit entries and both Creator/Member access changes.
   Ordinary Creator gets no global admin rights; Platform Admin gets no private-content bypass.

### 8. Profile, preferences and help (F-02, F-14)

1. Edit Profile, bio/avatar and optional notice preferences; verify validation, private
   avatar access and persistence after login. Invalid identity/role fields cannot grant rights.
2. Disable optional reply/course notices: future optional delivery stops, existing notices
   remain and required membership/report notices still work. Re-enable without backfill.
3. Open Help as guest/member/Creator. Follow the role's available controls and confirm the
   explicit Test Mode/no-real-transfer copy; saved unauthorized links remain denied.

For each scenario record role/tenant, fixture/run ID, expected and observed results,
PASS/FAIL/PENDING, exact candidate revision/dirty diff and sanitized artifact reference.
Include reload/back, invalid/empty/unauthorized and narrow-screen results; do not infer
full acceptance from a menu or a single screenshot. Visual redesign remains separate.
F-19 also requires current-revision CI/security/review, verified forward-upgrade retention
and the existing fresh-setup procedure on a second physical machine. Keep these gates
pending until their evidence exists.

## CI and merge gate

`.github/workflows/ci.yml` runs on PRs targeting `main`, pushes to `main`, and manual dispatch.
The required GitHub Actions check is **`CI/ Runner Preflight`**; normal merges need it green
and the existing PR approval. Administrator bypass remains available: never use it to
merge a failed check. Branch updates do not require automatic rebasing; rerun integration
checks after changes that affect the tested base.

The runner checks out the PR merge-ref (not just its head), installs locked dependencies,
builds Vite assets and the shared PHP image, then runs dependency audits, redacted Gitleaks,
Trivy, Semgrep, Pint, Larastan/PHPStan and the full MySQL `scool_test` suite with live Mailpit.
Skipped or empty test suites fail. Larastan starts at level 0 over `app` and `routes` to add
a small baseline without blanket ignores; it does not prove type correctness or cover every
security risk. Semgrep currently checks only `eval` and `unserialize`, not all vulnerabilities.

For local triage, use the Verification commands above, plus:

```bash
npm ci && npm run build
npm audit --audit-level=high
docker compose -p scool exec -T app composer validate --strict
docker compose -p scool exec -T app composer audit --locked
docker compose -p scool exec -T -e APP_ENV=testing -e DB_DATABASE=scool_test app vendor/bin/phpstan analyse --configuration=phpstan.neon --memory-limit=512M --no-progress
```

Open the first failed Actions step, reproduce its command, fix the cause and rerun CI.
Do not suppress assertions, skipped integrations, scanner findings or exit codes to get green.
Record both the PR head and the actual tested merge-ref SHA; after merge, confirm the `main`
push run too. A green run is not proof of enforcement: use a separate harmless failing PR,
record the required check red and normal-merge blocking, restore the original code, then
record green. Never merge the experiment PR.

Only npm/Composer downloads and PHP image layers are cached, not `.env`, database data or
the application dependency directories. Buildx cache misses build normally; `pull: true`
checks base images but cached package-install layers do not refresh packages automatically.
The image is loaded into the runner, not pushed to a registry. Successful-run artifacts
contain only JUnit, the tested SHA and built manifest/assets, with seven-day retention;
inspect/sanitize reports before sharing. Failure logs remain in Actions. Cleanup stops only
runner services without deleting volumes. This is CI, not production deployment/CD.

The PHP image build and CI also run `php /usr/local/bin/scool-verify-php-security.php`.
The supported 8.2 runtime must be at least 8.2.35 (CVE-2026-109618/CVE-2026-109619),
with `session.upload_progress.enabled=Off` and exception arguments hidden. Bootstrap
and local sync build with `--pull`; existing containers need recreation to receive
the updated image. Verify checks the running app and worker too. A clean Composer
audit or Alpine package scan alone does not establish that PHP itself is patched.

## Safety

- Tests use `scool_test`, separate from development `scool`. The test DB is created on initial volume setup; older volumes missing it need separate investigation.
- Do not use `down -v`, `migrate:fresh`, `queue:clear` or `queue:flush` during normal operations.
- Demo seed preserves existing users. Persona names do not grant Creator/Member/Admin permissions. Never use sample credentials outside local development.
- Do not share `.env`, keys or sensitive data. Seed/reset/build does not make the local configuration production-ready.

## Working with AI

Open the repository root and start a new session with:
`start EUR-XX - Read AGENTS.md and report readiness before editing.`

Shared rules: [AI_WORKFLOW.md](docs/AI_WORKFLOW.md). They are IDE-neutral; personal agent settings are not required or distributed.
Agents must not commit/push/merge or mark Done without explicit authorization.

Project documentation: [docs/00_README.md](docs/00_README.md).
Setup evidence and remaining acceptance gaps: [docs/WORKING_CONTEXT.md](docs/WORKING_CONTEXT.md) and EUR-20 in Linear.
