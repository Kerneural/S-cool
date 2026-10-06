# S-cool documentation

## Shared entrypoints

- [Project overview](00_PROJECT_OVERVIEW.md): background and product context; not a daily progress log.
- [Product Scope](01_PRODUCT_SCOPE.md): problem, actors, golden flow, MVP and exclusions.
- [Domain & Architecture](02_DOMAIN_ARCHITECTURE.md): domain model, tenancy, authorization, workflows and ADRs.
- [Delivery Plan](03_DELIVERY_PLAN.md): milestones, slices, ownership and acceptance gates.
- [README](../README.md) and checked-in runtime scripts/configuration: reproducible setup and operations.
- [AI workflow](AI_WORKFLOW.md): tool-neutral working rules.
- [Working context](WORKING_CONTEXT.md): issue-specific technical plans and checkpoints, not authoritative progress.

Root `AGENTS.md` routes agents to the shared contract. Linear owns delivery status and assignments; Git/GitHub owns code/review/CI evidence. Personal IDE configuration, upstream caches and the local operations notebook are ignored, optional and never prerequisites for teammates.

After cloning, open the repository root and request `start EUR-XX - Read AGENTS.md and report readiness before editing.` Shared file/privacy checks run during setup. They do not prove agent obedience or authorize publication.

## Current product decisions

- Multi-community platform; a user may own or join multiple communities.
- MVP communities are private and invite-only; no public discovery or self-join.
- Email-bound invitations remain a proposed baseline to confirm before migration.
- Initial payment integration uses SePay Sandbox; real-money production payment is deferred.
- Community/Classroom/Calendar follows Skool's broad product structure, not copied branding or pixel-perfect UI.
- Stabilize local delivery before choosing hosting.
- MySQL is the only database. Laravel, Vite, Tailwind CSS and Alpine.js are required; Blade is the rendering baseline.
- Decision on 2026-10-05: finish the MVP first. The mentor demo is a feature/workflow reference, not a requirement to switch to React or copy its UI. The research handoff remains in Product Scope, section 19.

## Updating the documents

Clarify the next slice's scope and golden flow, then tenancy/authorization/state, delivery dependencies and verification. Do not postpone all coding until every document is finished. Update the relevant canonical document when a decision changes.

Use `FACT` for verified facts, `ASSUMPTION` for provisional claims, `DECISION` for agreed choices, `TODO` for gaps and `OUT` for explicit exclusions. Keep architecture decisions in Domain & Architecture and operating behavior in checked-in setup documentation/source.

Before implementation, the slice needs clear roles/permissions, testable AC, tenancy/security impact, relevant entities/states, assigned implementation/review responsibility and ready dependencies.
