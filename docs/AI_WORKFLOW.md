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

1. Read `AGENTS.md`, this file including **Execution efficiency**, and the relevant checkpoint in `docs/WORKING_CONTEXT.md`. The shared execution rules are required for every implementation/audit session, not optional background reading.
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

## Execution efficiency

This section is the persistent shared operational contract, including the lessons recorded on 2026-10-10. Read it before invoking execution tools in every implementation/audit session; in readiness, briefly identify the intended shell/execution route, relevant limitations and smallest sufficient verification scope. Do not recite the entire section.

Keep durable rules and recovery guidance here. `docs/WORKING_CONTEXT.md` holds issue plans, results, blockers and next actions, not another copy of this contract. Routine checkpoint updates or cleanup must not delete, shorten or rewrite these rules. Material changes to the shared contract require explicit authorization for that scope.

These rules never waive security, AC, required skills, approvals, review or publication gates. Markdown can require reading but cannot prove agent compliance or override tool permissions.

### Observed failures and corrective actions

| Observed behavior | Why it wasted work or weakened evidence | Required recovery / prevention |
|---|---|---|
| Git Bash startup denied with `NtCreateDirectoryObject ... 0xC0000022`; restricted Docker access and a Node/NVM realpath check also encountered permission boundaries. | Repeating the same invocation cannot fix a deterministic access restriction; a runtime/tool error can be mistaken for a source defect. | Identify the executable, requested resource and permission boundary. Keep normal permitted reads in the default sandbox. Use the supported narrowly scoped approval path when required, or a supported in-scope alternative. A rejected request is not permission to bypass the sandbox. Never default all commands to elevated/full access. |
| Repeated short empty polls of the same long-running Bash test. | No new evidence was obtained; requested waits did not always match the tool's effective minimum. | Save the returned session ID, inspect effective wait limits, and resume that session with a bounded wait (typically 20-30 seconds where supported). Do not start another copy. Report meaningful progress or uncertainty rather than every unchanged poll. |
| A post-fix sync regression run was interrupted after producing no visible output, then rerun. | Silence was treated as a hang without sufficient evidence; the interrupted run was unusable acceptance evidence. | Check process/session state and whether output is buffered before interruption. Stop only for an established timeout/hang, safety concern or explicit cancellation. Record the interrupted result separately; count only the completed rerun. |
| `bash -x ... 2>&1` was piped into `Select-Object -Last 55`. | The output filter withheld progress until completion, making a running test look stuck and prompting extra diagnosis. | Use the native session's streaming output during execution. Summarize/tail completed output afterward. If a log is necessary, keep it sanitized, bounded and local; do not dump traces containing secrets or duplicate logs into shared docs. |
| Broad tool-metadata and combined file reads exceeded output budgets and were truncated. | Useful material and required instructions needed retrieval again, consuming calls and context. | Discover names first, retrieve only the matching tool schema, and return selected fields. Locate source with `rg`, then read relevant ranges. Read selected instruction files fully with adequate budget or explicit pagination to EOF; do not assume a truncated read was complete. |
| A failing Bash invocation was followed by successful Git commands in the same PowerShell execution, leaving the overall final exit code at zero. | The tool-level result no longer represented the failed check. | Capture/check `$LASTEXITCODE` immediately after each native command, and propagate failure before later commands run. Handle PowerShell cmdlet failures with their own error mechanism. For Bash pipelines, retain `pipefail`; do not treat the last output filter as the test result. |
| An initial syntax invocation passed multiple script paths to `bash -n`. | Only the first path is the script; subsequent paths are arguments, not independently checked files. | Run one syntax check per script and verify each exit code. Do not claim all scripts were checked from a single multi-path invocation. |
| GitNexus returned file-level information but no Bash execution flow for the sync scripts. | Repeated graph queries or indexing cannot substitute for missing language/flow coverage. | State the limitation once. Inspect the bounded source/diff and use a regression that fails before the fix and passes after it. Use graph queries for supported cross-file relationships, not as proof of Bash safety or test coverage. |
| Restricted process inspection did not show the expected Bash process; a later permitted inspection showed it. | Absence from a restricted view was not proof that the command had stopped. | Prefer the originating session status. If process visibility is restricted, mark the result inconclusive; perform one permitted diagnostic when necessary instead of declaring a hang or launching a duplicate. |

These are historical observations, not a claim that every machine has these restrictions or that a particular model is slow. The GitHub #20 checkpoint in `docs/WORKING_CONTEXT.md` contains the actual source/test results; these lessons do not rerun or upgrade that evidence.

### Bounded execution flow

1. **Hydrate once:** verify branch/HEAD/dirty paths, current AC, relevant checkpoint and source. Preserve unrelated work. Identify whether the task authorizes reads, fixes, runtime writes or publication; do not infer authority from an older issue.
2. **Plan the minimum useful calls:** choose shell/workdir, relevant files, permission route and verification gates. Group independent reads/static checks when safe. Retrieve only task-relevant skill/tool instructions, while honoring mandatory skill rules.
3. **Execute and retain the session:** if a command yields, keep its ID and resume it. Do not sleep blindly, spawn the same test twice or run shared-database suites concurrently. Keep user-facing progress concise during long waits.
4. **Classify a failure before retrying:** preserve the first useful diagnostic, exit code and last completed step. Change the invocation, approved permission route, implementation or hypothesis before repeating. For a suspected transient error, identify the transient evidence and use a bounded retry; repeated failure needs investigation or a clear blocker.
5. **Verify proportionately:** iterate with targeted checks, then run required integrated gates on the final relevant code/runtime state. Documentation-only changes normally need routing/privacy and diff checks, not Docker rebuilds, dependency installation or a full application suite. Shared runtime/schema/security changes may need broader integration even when the source diff is small.
6. **Hand off once:** record exact revision plus dirty diff when applicable, command, scope, final exit/result and evidence limits in the active checkpoint. Include elapsed time when available. If unfinished, preserve the session ID (only usable in its active tool/session), last observed progress, reason and next diagnostic. Do not create another tracking file or publish raw logs.

### Verification selection and reuse

| Change / question | Default starting evidence | Broader gate when needed |
|---|---|---|
| Shared instruction/checkpoint only | Read final routing, shared contract check, `git diff --check` | Contract regression if its scripts or enforced routing logic change; no claim of actual agent compliance. |
| Bash sync/setup defect | Separate Bash syntax checks, relevant mocked red/green regression, related setup regressions | Read-only real CLI comparison where mocks cannot establish the behavior; live upgrades require applicable authority and data guards. Mock PASS is not another host's successful sync. |
| Application feature / tenant policy | AC-mapped targeted feature and negative authorization tests on guarded MySQL `scool_test` | Required integration/full suite, relevant browser flows and CI/review before acceptance. Never waive tenant/security checks to save time. |
| Runtime/container/queue/configuration change | Validate affected configuration and a bounded real behavior probe | Integrated runtime/application checks; isolate approved fixtures. Never reset the development DB or delete volumes to accelerate verification. |

- Reuse means verifying that the relevant source/test/runtime inputs still match the tested state, not quoting an old PASS as current proof. A documentation-only follow-up can reference unchanged implementation evidence with an explicit qualification; new implementation changes invalidate affected evidence.
- Choose one completed authoritative run per gate after the relevant final change. Additional runs need a reason: corrected failure, changed inputs, missing coverage, independent-host evidence or a required acceptance gate. Do not skip a required run simply because an older run passed.
- Index freshness matters only when relying on graph results. Check freshness/coverage before queries; reindex stale relevant source when needed. Do not repeatedly rebuild an optional index for a bounded documentation or unsupported Bash task.
- Do not install another plugin, upgrade tooling, weaken guards or rewrite scripts as a speculative speed fix. Establish the bottleneck first and obtain approval for dependencies, scope or permission changes.

### Exit checklist for the next agent

- Did each retry have a corrected cause or a supported transient hypothesis?
- Was a command still running, buffered or invisible to restricted diagnostics before any interruption?
- Were native-command failures propagated rather than masked by later commands or filters?
- Were reads bounded but all mandatory instruction files read completely?
- Were graph results applicable/fresh, or was unsupported coverage explicitly replaced with source/test evidence?
- Is each PASS tied to a completed run and matching inputs, with mocks/manual/CI/independent-host limits separated?
- Are security, data safety, approvals and publication gates unchanged?

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

## UI scope and conflict resolution

These rules apply equally to implementation, audits and authorized fixes.

- UI work is in scope when required by the issue outcome/AC or a reported UI defect: necessary controls, state indicators, validation/error feedback and usable responsive behavior. Use existing layouts, components and styling conventions; a functional issue is not blanket permission to redesign its screens.
- Preserve unrelated appearance, navigation and interactions. Cosmetic polish, new design systems, broad layout changes and shared-component redesign require explicit approval before editing; propose a separate issue when appropriate. If the boundary is unclear, pause the affected change and ask while continuing unaffected in-scope work.
- Before resolving a view conflict, compare the base, incoming implementation and accepted baseline. Merge the required functional/security corrections while preserving unrelated UI. Do not replace an entire view with either side, remove controls or simplify the design merely to resolve a conflict faster without explicit approval for that broader change.
- If a required correction cannot preserve the existing UI, explain the defect, minimum necessary visual/interaction change and trade-off, then obtain approval before making that change. An instruction to audit and fix defects does not authorize unrelated redesign.
- Review UI diffs as well as backend behavior. Handoff must list changed screens/controls, explain why each change is in scope, and attach sanitized before/after screenshots at a comparable role, viewport and state, tied to the tested revision; otherwise mark visual verification as pending. Keep functional test results and visual acceptance separate: passing tests does not prove visual parity.

## Checkpoints and handoff

Use one short-lived branch and focused PR per issue. Name branches `eur-<issue-number>-<short-topic>` without tool/agent branding. Keep issue sections in `WORKING_CONTEXT.md`; update only the active section without overwriting another issue's checkpoint.

Record the issue/AC source, timestamp, branch/HEAD/dirty files, technical plan/diff, commands/results, decisions/blockers, unverified items and next action. Move accepted evidence to Linear/PR; do not copy chat logs or the whole roadmap.

Handoff must identify the exact revision/PR, touched files and changed contracts. The receiving agent re-verifies source before continuing.

`save` updates the checkpoint using existing evidence only. It does not authorize tests, staging, commits, pushes, PR creation or status changes.
`ship` prepares handoff, evidence, teach-back and a proposed commit/PR description only.
Publication approval does not carry over from previous issues or turns. Tests passing or the word "finished" is not authorization.

## Optional code navigation: GitNexus

- GitNexus is a local navigation aid, not a runtime/setup prerequisite or a source of acceptance evidence.
- Before using graph results, check `gitnexus status` against the intended checkout/revision. If relevant indexed source is stale, run `gitnexus analyze` from the checkout root before relying on those results. Do not reindex for every task or documentation-only checkpoint when no graph-backed answer is needed; disclose stale/unsupported coverage and verify actual source instead. An index of another revision is not evidence for a PR.
- `.gitnexusrc` uses index-only mode and disables embeddings; do not override it to inject agent files, install hooks or self-commit. The index stays ignored and private/runtime paths are excluded by `.gitnexusignore`.
- In MCP, read `gitnexus://repo/S-cool/context`, then use `query` to locate flows and `context`/`impact` for specific symbols. Pass `repo: S-cool` explicitly because the server can serve other projects; confirm the registered name with `list_repos` on another machine.
- Prefer small result limits and omit full source unless needed; verify returned locations in actual source/diff. Laravel dynamic bindings, policies and Blade/Alpine behavior may not be fully represented. Authorization, security and runtime tests remain required.
- Do not publish graphs, generate a paid wiki or enable external embeddings without separate approval. Keep editor/MCP configuration local; the shared `AGENTS.md` contract remains authoritative.

## Tool-neutral onboarding

Open the repository root as the IDE workspace. Start a new session with:

> start EUR-XX - Read AGENTS.md and report readiness before editing.

If a client does not discover `AGENTS.md`, explicitly attach it and this workflow. Verify readiness before assigning implementation. No IDE-specific slash commands, global settings, hooks, installers or private configuration are required.

`.agent/`, `.agent-reference/` and the personal operations notebook are ignored. Never publish their content or make setup depend on them. Any local integration must preserve this shared contract.

`bash scripts/verify-agent-contract.sh` checks shared file availability, routing and ignored/private boundaries. `--require-tracked` additionally checks the Git index, not whether files were pushed. Bootstrap/Verify runs the structural check automatically. It does not execute an agent or prove compliance; application tests, CI, permissions and review remain separate gates.
