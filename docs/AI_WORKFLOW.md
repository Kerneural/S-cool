# S-cool - shared AI workflow

This contract applies to all team members and IDE agents. The person accepting a commit must understand and take responsibility for it. Markdown instructions do not override tool permissions or enforce agent behavior.

## Sources of truth

| Information | Canonical source |
|---|---|
| Outcome, AC, status, assignee, dependencies, milestone | Linear issue/project |
| Code, review, PR and CI evidence | Git/GitHub |
| Issue-specific technical plan, checkpoint and blockers | `docs/WORKING_CONTEXT.md` |
| Product roles, golden flow and scope | `docs/01_PRODUCT_SCOPE.md` |
| Domain, authorization, architecture and ADRs | `docs/02_DOMAIN_ARCHITECTURE.md` |
| Delivery ownership, milestones and DoR/DoD | `docs/03_DELIVERY_PLAN.md` |
| Reproducible setup and operational commands | `README.md`, `scripts/`, `docker-compose.yml`, `docker/` |

Do not create parallel roadmaps, specs, archives or memory files that duplicate these sources. Personal agent configuration and upstream caches are optional local tools, not shared runtime instructions.

## Start and readiness

Before edits or side effects:

1. Read `AGENTS.md`, this file and the relevant checkpoint.
2. Verify branch, `git rev-parse HEAD`, `git status --short` and actual source. Distinguish historical results from current evidence.
3. Read the current issue AC and dependencies. Without Linear access, use the supplied contract and explicitly mark live status as unverified; do not copy another person's credentials.
4. Report the outcome, AC source, branch/HEAD, existing edits to preserve, next action and evidence limits.

For a small request outside an issue, use the stated scope; do not invent an issue. Missing access blocks only checks requiring that access. Ask before decisions that materially change scope, architecture, permissions or cost.

Read the relevant sections in full: Product Scope for scope/UI flows; Domain & Architecture for schema/policies/shared contracts; Delivery Plan for ownership/dependencies/DoD; README and runtime source for Docker/DB/queue/build/CI. Do not load the entire repository or upstream cache for every task.

## One issue, one controlled loop

Issue creation and material updates must follow the canonical **Issue description template** and **Issue quality gate** in `docs/03_DELIVERY_PLAN.md`, for humans and agents alike. Fill the contract before implementation; generic phrases such as "CRUD works", "secure", "validate URL" or "tests pass" are not standalone AC. Label proposals and unresolved decisions explicitly. Keep assignee, priority, estimate, due date, project, milestone, cycle and relations in Linear metadata; do not silently change them while editing descriptions. The M2 cross-review waiver was a scoped acceptance exception, not a default waiver for subsequent work.

1. **Contract:** confirm outcome, includes/excludes, AC, dependencies and negative cases. Do not weaken requirements to make tests pass.
2. **Plan:** record target files, trade-offs, shared-contract impact, security and verification commands in the issue checkpoint. Use an ADR only for a significant architecture decision.
3. **Build:** make a bounded diff within the assigned module. Avoid unrelated refactors or repository-wide formatting.
4. **Test/review:** inspect the diff, AC, happy path and failure cases. Record exact commands, results and revision. Use targeted checks during iteration and proportionate full verification before acceptance.
5. **Handoff:** explain flow, diff, trade-offs/security, evidence, unverified items and next action. Explain before teach-back; ask one question per turn. Unclear understanding or missing evidence prevents acceptance.
6. **Publication:** obtain explicit current authorization for commit/push/PR/merge or Done. Review and required checks still gate merge. Open AC prevents Done or milestone completion.

Repeated failures require a changed hypothesis and investigation, not blind retries. An audit is read-only unless the request includes fixing findings. Analysis alone does not authorize implementation.

## Scope and security boundaries

- Follow the agreed stack and scope. Adding/upgrading dependencies, tools or integrations needs approval; installing existing lockfiles is a normal setup step.
- Work only on the assigned issue/module. Keep one implementation issue in progress per person; review/unblocking does not transfer ownership. Coordinate cross-module changes.
- Verify server-side authorization, tenant isolation and payment state/idempotency. Hidden UI buttons are not access controls.
- Tests use MySQL `scool_test` only, with a guard before `RefreshDatabase`, including connection URL overrides. Never reset the development DB.
- Use synthetic identities and UUID recipients for mail tests; never clear the shared Mailpit inbox.
- Never place secrets, cookies, tokens, raw user/mail/payment records or personal connection settings in docs, issues, PRs, screenshots or logs. Sanitize diagnostics.
- Do not overwrite `.env`/keys, delete volumes, reset databases or flush queues without separate authorization and an exact validated target.
- Preserve unrelated dirty edits. Stage an explicit file allowlist; never stage the entire repository, force-push, push directly to main or bypass protection/review/checks.
- Issue/PR prose must be objective: outcome, scope, AC, trade-offs and evidence. Keep assignments in metadata; omit personal names, agent identities and implementer/reviewer narrative.
- Write shared agent-facing files and technical checkpoints in English. Conversations may use the user's preferred language.

## Checkpoints and handoff

Use one short-lived branch and focused PR per issue. Name branches `eur-<issue-number>-<short-topic>` without tool/agent branding. Keep issue sections in `WORKING_CONTEXT.md`; update only the active section without overwriting another issue's checkpoint.

Record the issue/AC source, timestamp, branch/HEAD/dirty files, technical plan/diff, commands/results, decisions/blockers, unverified items and next action. Move accepted evidence to Linear/PR; do not copy chat logs or the whole roadmap.

Handoff must identify the exact revision/PR, touched files and changed contracts. The receiving agent re-verifies source before continuing.

`save` updates the checkpoint using existing evidence only. It does not authorize tests, staging, commits, pushes, PR creation or status changes.
`ship` prepares handoff, evidence, teach-back and a proposed commit/PR description only.
Publication approval does not carry over from previous issues or turns. Tests passing or the word "finished" is not authorization.

## Tool-neutral onboarding

Open the repository root as the IDE workspace. Start a new session with:

> start EUR-XX - Read AGENTS.md and report readiness before editing.

If a client does not discover `AGENTS.md`, explicitly attach it and this workflow. Verify readiness before assigning implementation. No IDE-specific slash commands, global settings, hooks, installers or private configuration are required.

`.agent/`, `.agent-reference/` and the personal operations notebook are ignored. Never publish their content or make setup depend on them. Any local integration must preserve this shared contract.

`bash scripts/verify-agent-contract.sh` checks shared file availability, routing and ignored/private boundaries. `--require-tracked` additionally checks the Git index, not whether files were pushed. Bootstrap/Verify runs the structural check automatically. It does not execute an agent or prove compliance; application tests, CI, permissions and review remain separate gates.
