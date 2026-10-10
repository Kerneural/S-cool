# S-cool - shared agent entrypoint

1. Before editing, read `docs/AI_WORKFLOW.md` and the relevant issue section in `docs/WORKING_CONTEXT.md`. Verify Git HEAD/status and actual source; checkpoints are not current evidence.
2. Report readiness: outcome, AC source, branch/HEAD, existing edits to preserve, next step and unverified facts. Do not invent issue status or progress.
3. Read only the relevant product, architecture and delivery sections using the routing in `docs/AI_WORKFLOW.md`. Runtime setup is documented in README and checked-in scripts.
4. Linear owns status, assignee, dependencies, milestone and AC. Git/GitHub owns code, review and CI evidence. The working context owns issue-specific technical plans and checkpoints.
5. Work within the assigned issue/module. Preserve unrelated edits; obtain approval before expanding scope, changing shared contracts or adding dependencies. Do not create duplicate plans/state stores.
6. Hand off flow, diff, trade-offs, security, test commands/results and unverified items. Explain the system before owner teach-back; ask one question at a time.
7. `save` means checkpoint only; `ship` means acceptance preparation only. Commit/push/PR/merge and Done require explicit authorization for the current action; never bypass review or required checks.
8. Tests use only MySQL `scool_test`, guarded before RefreshDatabase. Use unique Mailpit recipients; never delete the shared inbox. Do not reset data, delete volumes or expose secrets.
9. Write shared agent instructions and technical checkpoints in English. Keep personal IDE settings, credentials, `.agent/`, `.agent-reference/` and local notebooks private and ignored; they are not setup prerequisites.
10. `bash scripts/verify-agent-contract.sh` checks shared files and privacy boundaries; `--require-tracked` additionally checks the Git index. Neither proves agent compliance, publication or test coverage.
11. Use neutral issue branches such as `eur-21-community-baseline`; never include tool/agent branding in branch names. Do not rename/delete remote or unrelated historical branches without authorization.
12. Before creating or materially changing any issue, use the issue template and quality gate in `docs/03_DELIVERY_PLAN.md`. Specify observable outcome, scope, workflow/contracts, testable AC, dependencies, security, verification and evidence. Unresolved decisions prevent Ready; never invent evidence or put assignments/personal names in technical prose.
13. Follow the UI scope and conflict resolution rules in `docs/AI_WORKFLOW.md` during implementation, audit and fixes. Preserve existing design; unrelated redesign or replacing entire views to resolve conflicts requires explicit approval. Passing functional tests does not prove visual parity.
