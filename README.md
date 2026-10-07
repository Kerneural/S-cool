# S-cool

Private learning-community platform built with Laravel, MySQL, Blade, Alpine.js, Tailwind CSS and Vite.
The current environment is local development, not a production deployment.

## Existing installation: sync before starting work

> [!IMPORTANT]
> Already installed an older version? Update the existing checkout; **do not rerun Bootstrap**.
> Commit your work first, use a clean `main` branch, start Docker and stop Vite.
> Keep `APP_URL=http://127.0.0.1:8080` in your existing `.env`; do not replace it or regenerate the key.

> [!WARNING]
> `scripts/sync-local.sh` is currently a draft: its pull option must be corrected
> from `-ff-only` to `--ff-only`, and fail-fast/safety checks must be added and verified.
> **Do not run it until these issues are resolved.**

After the script is corrected and verified, receive it and sync from the repository root:

```bash
git switch main
git pull --ff-only origin main
bash scripts/sync-local.sh
```

For subsequent updates on a clean `main`, run `bash scripts/sync-local.sh`.
The intended flow updates dependencies and containers, applies pending migrations,
builds frontend assets and runs verification. Continue only after `[PASS] verify on this host`.
Sync does not merge feature branches or copy another member's database; Git conflicts still require review.

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
