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
restarts the stack and runs Verify (including `npm run build` and tests).
Wait for **`[PASS] Local sync complete`**, then create your next issue branch from main.

A URL/SMTP preflight rejection names each incorrect setting without printing its value.
Correct only those entries in your existing `.env` (or explicit environment overrides),
then rerun sync. Pull never replaces `.env`; an old `localhost` value stays until corrected.

Sync refuses dirty/feature branches, unpublished main commits, Vite's hot file,
missing installations and stacks owned by another checkout. It never stashes/resets work,
replaces `.env`, regenerates keys, seeds/resets data or deletes volumes.
Each machine keeps its own data; this synchronizes code/schema/runtime, not databases.
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

The dashboard lists communities you own or actively belong to (open [Dashboard](http://127.0.0.1:8080/dashboard)). Create a private community,
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
