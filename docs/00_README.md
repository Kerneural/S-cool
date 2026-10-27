# S-cool documentation

## Shared entrypoints

- [Project overview](00_PROJECT_OVERVIEW.md): background and product context; not a daily progress log.
- [Product Scope](01_PRODUCT_SCOPE.md): problem, actors, golden flow, MVP and exclusions.
- [Domain & Architecture](02_DOMAIN_ARCHITECTURE.md): domain model, tenancy, authorization, workflows and ADRs.
- [Delivery Plan](03_DELIVERY_PLAN.md): milestones, slices, ownership and acceptance gates.
- [M14 owner-ready contract](01_PRODUCT_SCOPE.md#23-m14-owner-ready-website-contract-2026-10-27): frozen D1–D15; technical contract in Architecture section 18, UI in Design section 13, issue/parallel-work map in Delivery section 20. Linear owns live status.
- [Functional completion handoff](03_DELIVERY_PLAN.md#17-functional-completion-release--implementation-handoff): mentor-reference coverage, ordered issue drafts, contracts and verification; part of the canonical Delivery Plan, not a separate progress tracker.
- [README](../README.md) and checked-in runtime scripts/configuration: reproducible setup and operations.
- [AI workflow](AI_WORKFLOW.md): tool-neutral working rules.
- [Design system](../design.md): shared visual/interaction direction, component rules, UI/UX reference integration and rendered acceptance; design adoption remains a scoped UI change.
- [Working context](WORKING_CONTEXT.md): issue-specific technical plans and checkpoints, not authoritative progress.

Root `AGENTS.md` routes agents to the shared contract. Linear owns delivery status and assignments; Git/GitHub owns code/review/CI evidence. Personal IDE configuration, upstream caches and the local operations notebook are ignored, optional and never prerequisites for teammates.

After cloning, open the repository root and request `start EUR-XX - Read AGENTS.md and report readiness before editing.` Shared file/privacy checks run during setup. They do not prove agent obedience or authorize publication.

## Current product decisions

- One expert may own a website containing multiple communities. Expert-mode ordinary registrants are learners; general mode retains existing creation/discovery.
- Community content is protected. Expert lists/search/switcher are access-scoped; deliberate signed-in About/admission and existing email-bound invitations remain valid. No guest community browsing.
- M14 defaults interface copy to Vietnamese and combines permitted owner operations under **Quản trị website**; implementation/release evidence remains separate from this contract.
- Initial payment integration uses SePay Sandbox; real-money production payment is deferred.
- Community/Classroom/Calendar follows Skool's broad product structure, not copied branding or pixel-perfect UI.
- Stabilize local delivery before choosing hosting.
- MySQL is the only database. Laravel, Vite, Tailwind CSS and Alpine.js are required; Blade is the rendering baseline.
- Decision on 2026-10-05: finish the MVP first. The mentor demo is a feature/workflow reference, not a requirement to switch to React or copy its UI. The research handoff remains in Product Scope, section 19.

## Updating the documents

Clarify the next slice's scope and golden flow, then tenancy/authorization/state, delivery dependencies and verification. Do not postpone all coding until every document is finished. Update the relevant canonical document when a decision changes.

Use `FACT` for verified facts, `ASSUMPTION` for provisional claims, `DECISION` for agreed choices, `TODO` for gaps and `OUT` for explicit exclusions. Keep architecture decisions in Domain & Architecture and operating behavior in checked-in setup documentation/source.

Before implementation, the slice needs clear roles/permissions, testable AC, tenancy/security impact, relevant entities/states, assigned implementation/review responsibility and ready dependencies.
