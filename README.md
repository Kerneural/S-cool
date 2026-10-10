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

## M2 private-community flow

The dashboard lists communities you own or actively belong to (open [Dashboard](http://127.0.0.1:8080/dashboard)). Create a private community,
open **Invitations**, and invite an email. Check [Mailpit](http://127.0.0.1:8025), sign in at `http://127.0.0.1:8080/login` with that email,
verify it if needed, then reopen the email link and click **Accept invitation**.
Use the host configured in `APP_URL` (local default `http://127.0.0.1:8080`) for both login and invitation links so the browser sends the same session cookie. If upgrading from the earlier `localhost` default, change only `APP_URL` in your existing `.env`, then run `docker compose -p scool exec -T app php artisan config:clear` and `docker compose -p scool restart queue`. Do not replace `.env` or regenerate the key. Sign in again on `127.0.0.1`; previously delivered email links retain their old host, so revoke/reissue pending invitations if needed.
The link is single-use and expires after seven days. Members can view but not edit
settings, covers or invitations. Upload covers from **Edit Settings** (JPEG/PNG/WebP,
2 MB, maximum 4096 pixels per side). Paid checkout is not implemented yet.

After pulling new migrations into an existing installation, run the forward upgrade
below, then the normal Verify command. Bootstrap already applies them on fresh setup.

```bash
docker compose -p scool exec -T app php artisan migrate --no-interaction
docker compose -p scool restart queue
npm run build
```

## Review and fix a teammate's PR

> [!IMPORTANT]
> **Fix on the PR's source branch, test, then push to that same branch.**
> This updates the existing PR; do not create a second PR for the same branch.
> Record your current branch and commit or explicitly stash unfinished work first.
> Switching branches changes working files, not your local database or dependencies.

Example: PR source branch `eur-9-lesson-progress`. Replace it with the actual PR branch.

```bash
git status                         # Stop if there are uncommitted changes
git branch --show-current          # Remember your return branch
git fetch origin                   # Download remote commits; no integration yet
git switch --track origin/eur-9-lesson-progress  # First local checkout only
# If the local branch already exists, use instead:
# git switch eur-9-lesson-progress
git pull --ff-only origin eur-9-lesson-progress # Refuse divergent history
```

Confirm the branch/HEAD match the intended PR. Read its outcome, AC and diff;
make only scoped fixes. If checkout or fast-forward fails, inspect the cause;
do not reset, force-push or discard changes. If remote commits arrive during
review, integrate them deliberately and rerun affected checks before pushing.

Before running the branch, inspect dependency/configuration/migration changes.
Install changed lockfiles and apply reviewed forward migrations if needed;
never use Bootstrap, database resets or volume deletion on an existing installation.
Use the daily operations below to start the stack/restart workers. Stop Vite,
then verify the actual branch and manually test the PR's AC:

```bash
bash scripts/verify-fresh-setup.sh --mode verify
git diff                           # Review the final changes
git diff --check
git status
git add path/to/changed-file path/to/changed-test # Explicit paths only
git diff --cached                  # Confirm exactly what will be committed
git commit -m "fix: describe the reviewed correction"
git push origin HEAD:eur-9-lesson-progress
```

Add the commit hash, test results and remaining gaps to the existing PR/issue.
Merge only after acceptance and required checks. For your own new feature branch
without an existing PR, create a PR after pushing; that is a different workflow.

To return, ensure the review branch is clean, then run `git switch YOUR_PREVIOUS_BRANCH`.
Committed files return to that branch's version; database migrations, installed
dependencies and running workers do not roll back. Never run `sync-local.sh` on
a review branch: it requires clean `main`. Use a separate worktree/runtime when
incompatible changes require isolation; another complete stack is not mandatory.

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
