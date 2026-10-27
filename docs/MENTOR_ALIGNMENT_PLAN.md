# S-cool — Mentor alignment: superseded planning input

## 1. Original mentor request

> Em tối ưu lại: Giao diện mặc định là Tiếng Việt, Giao diện càng giống Skool càng tốt, Chủ cộng đồng có thể dùng được ngay.
> Ở đây không tập trung vào 1 hub saas, mà có thể là 1 chủ động đồng là sở hữu cả web luôn.
> Dùng được ngay được hiểu là: Có thể vận hành cộng đồng, tạo khoá học, thành viên trao đổi trong thảo luận dễ dàng, cơ chế điểm, đặc biệt là tính năng Khoá học phải dc học dễ dàng và quàn lý dễ dàng
> Cái này tụi em làm cho anh nhưng cũng là 1 key anh cho tụi em.
> Triển khai dc cho các chuyên gia là có tiền.
> Riêng báo cáo thực tập thì cứ viết đúng saas cho bài bản, ngầu nha.
> Còn anh chỉ cần cái dùng dc và đơn giản.
> Riêng phần hạ tầng: Em tìm hiểu về bảo mật video, chống tải video DRM
> Tìm hiểu thôi, có thời gian thì triển khai.

## 2. Status and use of this document

- Prepared 2026-10-27; revised the same day against `origin/main` and the product owner's instruction to cover the full mentor request.
- Purpose: retain the reviewed planning input and actual EUR issue links. Section 7 was published using the canonical issue template. Linear now owns the live descriptions, metadata, dependencies, evidence and status; do not maintain progress in this file.
- Published on 2026-10-27: M14 and 13 issues EUR-73 through EUR-85 exist in Linear with assignees, priority, Points, labels, due dates and native dependency relations verified after creation. At the initial publication snapshot, implementation/review/CI/deployment evidence was Pending and AC were unchecked; consult Linear for current evidence and status.
- Linear owns status, assignee, priority, estimate, labels, due date and relations. Section 6 proposes those metadata values for publication. Personal names appear only in that metadata/coordination table, not in technical issue prose. Hours are scheduling estimates; Points are separate relative estimates.
- Commit, push, PR, merge, deployment, owner grants and ownership transfers each require explicit authorization at the time of the action.
- **SUPERSEDED by EUR-73 canonical contract (2026-10-27):** [Product Scope section 23](01_PRODUCT_SCOPE.md#23-m14-owner-ready-website-contract-2026-10-27), [Architecture section 18](02_DOMAIN_ARCHITECTURE.md#18-m14-expert-deployment-contracts-eur-73-2026-10-27), [Design section 13](../design.md#13-m14-vietnamese-owner-and-learning-workspace-eur-73-2026-10-27) and [Delivery section 20](03_DELIVERY_PLAN.md#20-m14-owner-ready-website-delivery-eur-73-2026-10-27). Those documents own the frozen contract; [EUR-73](https://linear.app/eurusdevsec/issue/EUR-73) and EUR-74–85 own live AC/evidence/status. The retained drafts below are historical publication input, not instructions to reopen decisions or a second progress tracker. Documentation review/publication and implementation acceptance are separate gates.
- Shared technical prose is English per `docs/AI_WORKFLOW.md`; the mentor request and Vietnamese product labels stay in Vietnamese. No credentials, account identifiers, tokens or production records belong here.

## 3. Product decisions

| # | Decision | Status |
|---|---|---|
| D1 | One expert owns the whole website and operates it from one account and one entry point, **Quản trị website**. No SSH or technical help for routine operations. | CONFIRMED (mentor) |
| D2 | The website can hold several communities, each with its own courses, discussions and members. Community count and website ownership are different boundaries. | CONFIRMED (product owner) |
| D3 | Ordinary registration creates a learner only. It never grants site ownership, platform administration or community creation in an expert deployment. | CONFIRMED (product owner) |
| D4 | A member sees only communities they can access. A fresh account sees a welcome/access state, not a directory of pre-existing communities. Deliberate About links and invitations keep working. | CONFIRMED (product owner, reported reference behavior to avoid) |
| D5 | Vietnamese is the default interface for **every** screen, message, validation error and mail template. User-authored content is never translated automatically. | CONFIRMED (mentor) |
| D6 | As Skool-like as practical: six community tabs **Cộng Đồng / Lớp Học / Lịch / Thành Viên / Bảng xếp hạng / Giới Thiệu**, readable feed, course management with a chapter/lesson tree beside the editor, reader with content and curriculum separated. S-cool branding stays; no brand or asset copying. | CONFIRMED (mentor) |
| D7 | Courses must be easy to study (clear Start/Continue, outline, progress, completion) and easy to manage (tree + editor, formatting toolbar, preview, publish state). | CONFIRMED (mentor) |
| D8 | Points use the existing contribution ledger and `PointContribution::levelForPoints()`; the UI explains how points are earned. No invented counters or manual score edits. | CONFIRMED (mentor + existing contract) |
| D9 | Each expert gets an independent deployment from one codebase: own domain, environment, database/database user, application key, uploads and owner account. No cross-customer directory or shared login. Several sites may share a CloudPanel host; that is a shared-host risk, not separate-host isolation. | CONFIRMED (product-owner clarification) |
| D10 | Video security/DRM is research first; implementation only if time remains and it is separately approved. Current 512-byte masking is not DRM and must not be described as such. | CONFIRMED (mentor) |
| D11 | The internship report may describe a proper SaaS architecture and its evolution toward independent deployments. It is not an engineering issue and cites only evidenced behavior. | CONFIRMED (mentor) |
| D12 | Expert mode is a deployment configuration. With it off, the current general product (open creation, discovery) keeps working and stays tested. | CONFIRMED (product owner) |
| D13 | Owner identity is configured on the host: `SCOOL_DEPLOYMENT_MODE=general\|expert` (default `general`) and `SCOOL_OWNER_EMAIL`, read via `config/`. One predicate, `User::isDeploymentOwner()`, is true only in expert mode for the verified account with that email. No migration, no DB flag, no `Gate::before` bypass. | CONFIRMED in EUR-73; canonical detail in Architecture 18.1 |
| D14 | Module translations use per-module JSON catalogs registered with the translator's `addJsonPath()` (`lang/modules/<module>/vi.json`), so parallel issues do not edit one shared file. | CONFIRMED in EUR-73; canonical detail in Architecture 18.3 |
| D15 | Lesson body stays Markdown rendered by `LessonMarkdown` (raw HTML disabled). The editor adds a formatting toolbar and a server-rendered preview; it does not claim WYSIWYG. No new editor dependency. | CONFIRMED in EUR-73; canonical detail in Architecture 18.4 |

**Delivery journey:** owner signs in → **Quản trị website** → creates a community → builds a course with chapters and lessons in the tree + editor → previews and publishes → invites or admits a learner → learner studies, completes lessons and discusses → owner sees members, progress and points.

**Not in this milestone** (not part of the mentor request; existing functionality is kept, nothing is removed): Dark Mode or a second theme; quizzes, certificates, unlock-by-level; recurring subscriptions or trials; live payment provider or payouts (paid flows stay labeled Test Mode); uploaded lesson attachments or a file manager; a site builder or role designer; public guest browsing; another landing redesign; DRM implementation without separate approval.

## 4. Verified baseline (2026-10-27)

- `origin/main` = `28f250e` (merge of PR #69), no content difference from `6188d7b`. Branch every issue from the then-current `origin/main`.
- Canonical local acceptance origin: `http://127.0.0.1:8080` with the existing Compose ports. `scool_app` mounts the checkout under test at `/var/www/html`.
- The workspace root checkout (`community-directory-local`, `3b1d6c6`) has uncommitted edits to `CommunityController.php`, `CommunityPolicy.php` and `communities/index.blade.php`, which overlap [EUR-74](https://linear.app/eurusdevsec/issue/EUR-74/m14-expert-deployment-mode-and-deployment-owner-identity)/[EUR-75](https://linear.app/eurusdevsec/issue/EUR-75/m14-access-scoped-learner-entry-and-discovery). Decide whether that work is superseded before those issues start. Do not stage it as part of this milestone.

| Source fact | Consequence |
|---|---|
| `CommunityPolicy::create()` returns `true` for every user; `CommunityController::create/store` call `Gate::authorize('create', …)`. | [EUR-74](https://linear.app/eurusdevsec/issue/EUR-74/m14-expert-deployment-mode-and-deployment-owner-identity) changes the policy; hiding links is not enforcement. |
| `CommunityController::index()` (also `/dashboard`) defaults to `filter=all` and lists active communities without membership. | [EUR-75](https://linear.app/eurusdevsec/issue/EUR-75/m14-access-scoped-learner-entry-and-discovery) scopes the query in expert mode, including direct `filter`, search and pagination. |
| The navigation switcher already uses `Community::accessibleTo($user)` and only appears inside an authorized community. | [EUR-75](https://linear.app/eurusdevsec/issue/EUR-75/m14-access-scoped-learner-entry-and-discovery) verifies it; no rewrite. |
| Auth redirects use `intended(route('dashboard'))`; `RegisteredUserController` uses plain `redirect(route('dashboard'))`. | Entry routing lives in `/dashboard`; [EUR-75](https://linear.app/eurusdevsec/issue/EUR-75/m14-access-scoped-learner-entry-and-discovery) tests invitation → register → verify → return. |
| `isPlatformAdmin()` reads `is_platform_admin`; `/admin` has only community suspend/reactivate. Per-community creator tools exist: Overview, Orders, Reports, Review submissions, Invitations, Referrals, Manage Members, Edit Settings. | [EUR-76](https://linear.app/eurusdevsec/issue/EUR-76/m14-quan-tri-website-management-hub) builds the **Quản trị website** hub on existing screens; no new site-wide lists. |
| The workspace already has six tabs: Community Feed, Classroom, Events, Members, Leaderboard, About. | D6 tabs need translation and verification, not new routes. |
| No `lang/` directory; `.env.example` has `APP_LOCALE=en`; `phpunit.xml` sets no locale. 96 views: about 1,100 `__()` calls in 72 views; 24 views contain hardcoded English. Controllers and `CommunityInvitationMail` also emit strings. Font is self-hosted Be Vietnam Pro. | [EUR-77](https://linear.app/eurusdevsec/issue/EUR-77/m14-vietnamese-default-locale-foundation) foundation + translation inside each module issue + [EUR-78](https://linear.app/eurusdevsec/issue/EUR-78/m14-vietnamese-sweep-account-commerce-admin-and-secondary-screens) sweep. Hardcoded strings must be wrapped in `__()`. Glyph coverage still to verify. |
| Course page is a stack of `<details>` forms; lesson edit is on the reader page (`classroom/lessons/show.blade.php`). Reorder endpoints exist for courses, sections and lessons. | [EUR-79](https://linear.app/eurusdevsec/issue/EUR-79/m14-course-management-workspace-tree-editor) builds a tree + editor management screen on existing endpoints. |
| `classroom-lesson-fields.blade.php` has a textarea, supported video URL and publish fields; `LessonMarkdown` renders safely. | [EUR-80](https://linear.app/eurusdevsec/issue/EUR-80/m14-lesson-authoring-toolbar-preview-and-resource-links) adds toolbar + preview to that component. |
| `LessonController::show()` hides unpublished lessons and empty sections from members. `LessonProgressController` returns JSON with `completed` and course summary via `LessonProgressMutation`. | [EUR-81](https://linear.app/eurusdevsec/issue/EUR-81/m14-learner-course-flow-and-completion-feedback) verifies and improves feedback; no rebuild. |
| `PointContribution` has the post-like ledger, scoped rankings and `levelForPoints()`. | [EUR-82](https://linear.app/eurusdevsec/issue/EUR-82/m14-community-discussion-members-and-points-clarity) explains rules; no calculation change unless a defect is proven. |
| Invitation mail is a queued job using `Mail::mailer('smtp')`. | [EUR-84](https://linear.app/eurusdevsec/issue/EUR-84/m14-expert-site-provisioning-runbook-and-host-release) requires a running queue worker and real SMTP on the host. |
| A prior review reported only 74 discovered tests in a container, but this plan has no reproducible command/log proving that count. | Treat the number as unverified. Compare tracked test inventory, runtime mounts and PHPUnit discovery before acceptance. Explicit-file runs are targeted evidence only; repair or resolve incomplete discovery and run the complete required suite before release. |

Mentor reference (`https://skool.phung.vn`) was inspected on 2026-10-27 for navigation and management layout only. It is a usability reference, not acceptance authority. Do not reproduce its empty chapters, `??? pts` placeholders or missing lesson content. Private captures stay under ignored `output/playwright/`.

## 5. Milestone and Linear metadata

- Pre-publication inspection on 2026-10-27 verified project S-cool MVP — Local, team EUR, existing milestones M1–M13, users, workflow states and labels. The user subsequently authorized publication; the records below now use the real returned EUR identifiers.
- Created milestone: **M14 — Owner-ready website: Vietnamese & LMS** in S-cool MVP — Local. The original M1–M13 issues and statuses were not changed.
- The draft handles have been replaced with actual returned EUR identifiers and links. Never recreate these issues from this historical file; inspect/update the linked issue after checking current state.
- Section 6 records the initial published metadata snapshot; consult Linear for current assignment/state. Names appear only in metadata/coordination; technical issue prose stays objective. Points use the existing relative 1/2/3/5/8 scale; hours are separate scheduling estimates. Priority 2 means High, priority 3 means Normal. Use existing labels; do not invent a cycle.
- Initial status: Todo for fully defined issues, Backlog if a material decision/required metadata remains unresolved, using the actual project workflow. No issue becomes In Progress or Done from this plan.
- Target: **2026-10-27, Asia/Saigon**, for the speed-run candidate. Confirm realistic due dates from the actual start time when publishing. Host deployment follows required review/CI/merge and authorization; if it cannot finish today, keep it open and report the remaining work.

## 6. Issue map, sequence and file ownership

| Issue | Title | Initial assignee | Reviewer coordination | Priority | Points | Hours | Labels | Blocked by |
|---|---|---|---|---|---:|---:|---|---|
| [EUR-73](https://linear.app/eurusdevsec/issue/EUR-73/m14-contract-freeze-and-canonical-document-update) | Contract freeze and canonical document update | Hoàng | Khoa | High (2) | 1 | 0.5 | Improvement, area:platform | — |
| [EUR-74](https://linear.app/eurusdevsec/issue/EUR-74/m14-expert-deployment-mode-and-deployment-owner-identity) | Expert deployment mode and deployment-owner identity | Hoàng | Khoa | High (2) | 5 | 2 | Improvement, area:identity, security | [EUR-73](https://linear.app/eurusdevsec/issue/EUR-73/m14-contract-freeze-and-canonical-document-update) |
| [EUR-75](https://linear.app/eurusdevsec/issue/EUR-75/m14-access-scoped-learner-entry-and-discovery) | Access-scoped learner entry and discovery | Hoàng | Khoa | High (2) | 5 | 2 | Improvement, area:community, security | [EUR-74](https://linear.app/eurusdevsec/issue/EUR-74/m14-expert-deployment-mode-and-deployment-owner-identity) |
| [EUR-76](https://linear.app/eurusdevsec/issue/EUR-76/m14-quan-tri-website-management-hub) | Quản trị website management hub | Tiến | Hoàng | High (2) | 3 | 1 | Improvement, area:platform | [EUR-74](https://linear.app/eurusdevsec/issue/EUR-74/m14-expert-deployment-mode-and-deployment-owner-identity) |
| [EUR-77](https://linear.app/eurusdevsec/issue/EUR-77/m14-vietnamese-default-locale-foundation) | Vietnamese default locale foundation | Khoa | Hoàng | High (2) | 5 | 2 | Improvement, area:platform | [EUR-73](https://linear.app/eurusdevsec/issue/EUR-73/m14-contract-freeze-and-canonical-document-update) |
| [EUR-78](https://linear.app/eurusdevsec/issue/EUR-78/m14-vietnamese-sweep-account-commerce-admin-and-secondary-screens) | Vietnamese sweep: account, commerce, admin and secondary screens | Khoa | Tiến | High (2) | 5 | 2.5 | Improvement, area:platform | [EUR-77](https://linear.app/eurusdevsec/issue/EUR-77/m14-vietnamese-default-locale-foundation) |
| [EUR-79](https://linear.app/eurusdevsec/issue/EUR-79/m14-course-management-workspace-tree-editor) | Course management workspace (tree + editor) | Tiến | Hoàng | High (2) | 8 | 3.5 | Improvement, area:learning | [EUR-73](https://linear.app/eurusdevsec/issue/EUR-73/m14-contract-freeze-and-canonical-document-update) |
| [EUR-80](https://linear.app/eurusdevsec/issue/EUR-80/m14-lesson-authoring-toolbar-preview-and-resource-links) | Lesson authoring toolbar, preview and resource links | Tiến | Khoa | High (2) | 3 | 1.5 | Improvement, area:learning, security | [EUR-73](https://linear.app/eurusdevsec/issue/EUR-73/m14-contract-freeze-and-canonical-document-update) |
| [EUR-81](https://linear.app/eurusdevsec/issue/EUR-81/m14-learner-course-flow-and-completion-feedback) | Learner course flow and completion feedback | Tiến | Khoa | High (2) | 3 | 1 | Improvement, area:learning | [EUR-77](https://linear.app/eurusdevsec/issue/EUR-77/m14-vietnamese-default-locale-foundation) |
| [EUR-82](https://linear.app/eurusdevsec/issue/EUR-82/m14-community-discussion-members-and-points-clarity) | Community discussion, members and points clarity | Khoa | Tiến | High (2) | 3 | 1.5 | Improvement, area:community | [EUR-77](https://linear.app/eurusdevsec/issue/EUR-77/m14-vietnamese-default-locale-foundation) |
| [EUR-83](https://linear.app/eurusdevsec/issue/EUR-83/m14-integrated-acceptance-of-the-owner-ready-release) | Integrated acceptance of the owner-ready release | Hoàng | Khoa | High (2) | 5 | 2 | Improvement, area:platform, area:devops, security | [EUR-74](https://linear.app/eurusdevsec/issue/EUR-74/m14-expert-deployment-mode-and-deployment-owner-identity)–[EUR-82](https://linear.app/eurusdevsec/issue/EUR-82/m14-community-discussion-members-and-points-clarity) |
| [EUR-84](https://linear.app/eurusdevsec/issue/EUR-84/m14-expert-site-provisioning-runbook-and-host-release) | Expert-site provisioning runbook and host release | Hoàng | Khoa | High (2) | 5 | 1 + deploy | Improvement, area:devops, security | [EUR-73](https://linear.app/eurusdevsec/issue/EUR-73/m14-contract-freeze-and-canonical-document-update); [EUR-83](https://linear.app/eurusdevsec/issue/EUR-83/m14-integrated-acceptance-of-the-owner-ready-release) for deployment |
| [EUR-85](https://linear.app/eurusdevsec/issue/EUR-85/m14-video-security-and-drm-research-note) | Video security and DRM research note | Khoa | Hoàng | Normal (3) | 2 | 1 | Improvement, area:platform, security | None |

**Load:** Tiến 7h, Khoa 7h, Hoàng 7.5h plus actual deployment/review time. Estimates are not timesheets. Reviews take additional time and do not transfer implementation ownership. Assignments were recorded in Linear; no separate direct messages were sent.

**Same-day sequence (three implementation lanes):**

| Window from actual start | Hoàng — critical contracts and release | Khoa — Vietnamese/community | Tiến — LMS and management |
|---|---|---|---|
| T+0–0.5h | [EUR-73](https://linear.app/eurusdevsec/issue/EUR-73/m14-contract-freeze-and-canonical-document-update) contract freeze | Review locale/access contracts | Review UI/owner contracts |
| T+0.5–7.5h | [EUR-74](https://linear.app/eurusdevsec/issue/EUR-74/m14-expert-deployment-mode-and-deployment-owner-identity) → [EUR-75](https://linear.app/eurusdevsec/issue/EUR-75/m14-access-scoped-learner-entry-and-discovery); prepare [EUR-84](https://linear.app/eurusdevsec/issue/EUR-84/m14-expert-site-provisioning-runbook-and-host-release) runbook, verify full test inventory and review | [EUR-77](https://linear.app/eurusdevsec/issue/EUR-77/m14-vietnamese-default-locale-foundation) first → [EUR-78](https://linear.app/eurusdevsec/issue/EUR-78/m14-vietnamese-sweep-account-commerce-admin-and-secondary-screens) → [EUR-82](https://linear.app/eurusdevsec/issue/EUR-82/m14-community-discussion-members-and-points-clarity) → [EUR-85](https://linear.app/eurusdevsec/issue/EUR-85/m14-video-security-and-drm-research-note) | [EUR-79](https://linear.app/eurusdevsec/issue/EUR-79/m14-course-management-workspace-tree-editor) → [EUR-80](https://linear.app/eurusdevsec/issue/EUR-80/m14-lesson-authoring-toolbar-preview-and-resource-links) → [EUR-81](https://linear.app/eurusdevsec/issue/EUR-81/m14-learner-course-flow-and-completion-feedback) → [EUR-76](https://linear.app/eurusdevsec/issue/EUR-76/m14-quan-tri-website-management-hub) |
| T+7.5–9.5h | [EUR-83](https://linear.app/eurusdevsec/issue/EUR-83/m14-integrated-acceptance-of-the-owner-ready-release) on one integrated revision | Fix own defects; independent review | Fix own defects; independent review |
| After required review/CI/merge and authorization | [EUR-84](https://linear.app/eurusdevsec/issue/EUR-84/m14-expert-site-provisioning-runbook-and-host-release) host deployment/smoke | Verify permissions, mail and translated host paths | Retest learner/owner host screens |

- Planning envelope is **about 9.5 hours before additional review/host-deployment time**, not seven hours. Use the real remaining time today. At T+4h surface blockers/shared-file conflicts and stop optional polish first. Unfulfilled required AC stay open; speed does not waive security, review, CI or visual acceptance.
- [EUR-77](https://linear.app/eurusdevsec/issue/EUR-77/m14-vietnamese-default-locale-foundation) merges first because it registers the catalogs every lane uses. Until it merges, lanes wrap strings in `__()` and write their module catalog files; they do not edit `lang/vi.json` or `AppServiceProvider`.
- One active implementation issue per person → focused PR → independent review/applicable checks → authorized merge → next issue. Dependent work consumes the reviewed contract, preserving all unrelated root-checkout edits.
- One implementation lane owns [EUR-79](https://linear.app/eurusdevsec/issue/EUR-79/m14-course-management-workspace-tree-editor)/07/08 to avoid editor/reader conflicts. The management hub consumes the agreed LMS route contract; integrated acceptance waits for every required slice.

**Shared-file ownership** (coordinate before touching another issue's files):

| Files | Owner issue |
|---|---|
| `config/scool.php` (new), `User::isDeploymentOwner()`, `CommunityPolicy.php`, `EnsureUserIsPlatformAdmin.php`, owner console command | [EUR-74](https://linear.app/eurusdevsec/issue/EUR-74/m14-expert-deployment-mode-and-deployment-owner-identity) |
| `CommunityController::index()`, `communities/index.blade.php`, entry/welcome views, intended-destination handling in registration/verification if required | [EUR-75](https://linear.app/eurusdevsec/issue/EUR-75/m14-access-scoped-learner-entry-and-discovery) |
| Hub route/controller/view, `layouts/navigation.blade.php` management link | [EUR-76](https://linear.app/eurusdevsec/issue/EUR-76/m14-quan-tri-website-management-hub) |
| `config/app.php`, `.env.example`, `phpunit.xml`, `AppServiceProvider` translator paths, `lang/vi.json`, `lang/vi/*.php`, `layouts/*`, `auth/*`, `components/community-workspace.blade.php` | [EUR-77](https://linear.app/eurusdevsec/issue/EUR-77/m14-vietnamese-default-locale-foundation) |
| `lang/modules/classroom/`, `classroom/*`, `CourseController`, `CourseSectionController`, `LessonController`, classroom components | [EUR-79](https://linear.app/eurusdevsec/issue/EUR-79/m14-course-management-workspace-tree-editor) ([EUR-80](https://linear.app/eurusdevsec/issue/EUR-80/m14-lesson-authoring-toolbar-preview-and-resource-links) owns `classroom-lesson-fields.blade.php` and its JS; [EUR-81](https://linear.app/eurusdevsec/issue/EUR-81/m14-learner-course-flow-and-completion-feedback) owns `lesson-progress.blade.php` and reader copy) |
| `lang/modules/community/`, `communities/posts/*`, `members/*`, `directory/*`, `leaderboard`, `events/*`, `livewire/*` | [EUR-82](https://linear.app/eurusdevsec/issue/EUR-82/m14-community-discussion-members-and-points-clarity) |
| Account/profile/commerce/admin/invitation/mail/notices/help; remaining landing/About/preview/create/edit UI copy and corresponding controller/notification text; assigned module catalogs | [EUR-78](https://linear.app/eurusdevsec/issue/EUR-78/m14-vietnamese-sweep-account-commerce-admin-and-secondary-screens), excluding views assigned to other lanes |
| `routes/web.php`, `resources/css/app.css`, `resources/js/app.js` | Shared: one small additive hunk per issue, rebased in merge order [EUR-77](https://linear.app/eurusdevsec/issue/EUR-77/m14-vietnamese-default-locale-foundation) → [EUR-74](https://linear.app/eurusdevsec/issue/EUR-74/m14-expert-deployment-mode-and-deployment-owner-identity) → [EUR-79](https://linear.app/eurusdevsec/issue/EUR-79/m14-course-management-workspace-tree-editor)/07 → others |

## 7. Issue drafts

### [EUR-73](https://linear.app/eurusdevsec/issue/EUR-73/m14-contract-freeze-and-canonical-document-update) — Contract freeze and canonical document update

#### Outcome

The team implements from one agreed contract: decisions D1–D15 are recorded in the canonical documents and the root-checkout overlap is resolved.

#### Scope

Includes updating `docs/01_PRODUCT_SCOPE.md` (expert deployment, D1–D12, expert vs general discovery), `docs/02_DOMAIN_ARCHITECTURE.md` (deployment vs community boundary, owner identity, permission matrix), `design.md` (Vietnamese default copy rules, management entry, tree + editor pattern, retained reader) and `docs/03_DELIVERY_PLAN.md` (milestone and issue map). Confirm or amend D13–D15. Read-only inventory of current community → creator mapping. Excludes code, data changes and ownership transfers.

#### Workflow and contracts

Owner-approved decisions -> canonical scope/design/architecture/delivery diff -> independent review -> issue publication mapping. Read-only inventory only; no code, grants or data mutation.

#### Acceptance criteria

- [ ] AC-01: Canonical documents state D1–D15 without contradicting earlier sections; superseded statements are edited, not appended.
- [ ] AC-02: D13–D15 are marked CONFIRMED or replaced with the approved alternative.
- [ ] AC-03: Overlapping root-checkout edits have a documented disposition, without deleting, staging or overwriting unrelated work.
- [ ] AC-04: This file is marked superseded with links to the published issues.

#### Dependencies

Blocked by: None. Blocks: [EUR-74](https://linear.app/eurusdevsec/issue/EUR-74/m14-expert-deployment-mode-and-deployment-owner-identity), [EUR-77](https://linear.app/eurusdevsec/issue/EUR-77/m14-vietnamese-default-locale-foundation), [EUR-79](https://linear.app/eurusdevsec/issue/EUR-79/m14-course-management-workspace-tree-editor), [EUR-80](https://linear.app/eurusdevsec/issue/EUR-80/m14-lesson-authoring-toolbar-preview-and-resource-links) and the runbook portion of [EUR-84](https://linear.app/eurusdevsec/issue/EUR-84/m14-expert-site-provisioning-runbook-and-host-release). Other dependent work is blocked transitively; [EUR-85](https://linear.app/eurusdevsec/issue/EUR-85/m14-video-security-and-drm-research-note) research can run independently.

#### Security and failure cases

Documentation-only. Preserve unrelated edits, redact credentials/real records and keep inferred decisions distinct from approval.

#### Verification plan

`bash scripts/verify-agent-contract.sh`; reviewer reads the diff.

#### Evidence

- Commit / PR / exact revision: Pending.
- Automated command, environment, exit/result: Pending.
- Manual evidence / sanitized UI artifact: Pending, or justified N/A for docs/research.
- Independent review / required CI: Pending.
- Blocking AC / exceptions: Pending verification; never treat unresolved required AC as Done.
- Comment/artifact delivery: follow Section 8 Readback, Handoff and Review/retest requirements.

### [EUR-74](https://linear.app/eurusdevsec/issue/EUR-74/m14-expert-deployment-mode-and-deployment-owner-identity) — Expert deployment mode and deployment-owner identity

#### Outcome

In an expert deployment, the configured owner account can create communities and reach site administration; no other account can, and the general product is unchanged when the mode is off.

#### Scope

Includes `config/scool.php` reading `SCOOL_DEPLOYMENT_MODE` and `SCOOL_OWNER_EMAIL`; `User::isDeploymentOwner()`; `CommunityPolicy::create()` owner-only in expert mode; `EnsureUserIsPlatformAdmin` requiring the deployment owner exclusively in expert mode and retaining the existing Admin predicate in general mode; `@can('create', Community::class)` around create links; read-only `php artisan scool:owner:check` reporting whether the owner resolves to a verified account and listing active communities not created by the owner. It exits non-zero for invalid mode, unresolved owner or unresolved ownership mismatch in the intended release communities. Excludes ownership transfer, bulk reassignment, a DB flag, a new role model.

#### Workflow and contracts

Host env → config → predicate → policy/middleware. Predicate requires expert mode, a non-empty configured email, exact normalized match and `email_verified_at`. Existing content policies (`CoursePolicy` creator checks) stay; the owner manages content because the owner is the creator of the operated communities. Expert mode without a valid owner: privileged actions fail closed; members keep authorized learning access. Any mode value other than `general` or `expert` blocks privileged actions/release; a typo must not silently enable open creation. Operator recovery: correct host configuration, rebuild config cache, re-run the check and confirm app/queue consume the same configuration.

Before expert-mode host activation, inventory the intended release communities and their creators. Every operated release community must resolve to the configured owner. Any reassignment or exclusion from the release requires an exact, separately approved operator action with backup/audit/readback. No bulk transfer, silent deletion or historical Admin exception. Keep deployment blocked while the mapping is unresolved; preserve membership, progress and payments.

#### Acceptance criteria

- [ ] AC-01: Given expert mode and a verified owner, when the owner creates two communities, then both are created with the owner as creator.
- [ ] AC-02: Given expert mode, a learner and a second verified non-owner receive 403 on `GET communities/create` and `POST communities`, and the create links are absent.
- [ ] AC-03: Given an unverified account whose email equals `SCOOL_OWNER_EMAIL`, it is not the owner.
- [ ] AC-04: Given expert mode, the owner opens `/admin/communities` without `is_platform_admin`; a learner and another account with `is_platform_admin=true` receive 403. General mode retains existing Admin authorization.
- [ ] AC-05: Given general mode, any verified user creates a community as before (existing tests stay green).
- [ ] AC-06: Given expert mode with an empty or unmatched owner email, creation is denied for everyone and the check command reports the problem with a non-zero exit.
- [ ] AC-07: Registration, profile update and request parameters cannot set the mode or owner.
- [ ] AC-08: Invalid deployment mode or an intended active release community owned by a different creator makes the check command exit non-zero and blocks handover. Diagnostics expose no credentials and perform no ownership mutation.

#### Dependencies

Blocked by [EUR-73](https://linear.app/eurusdevsec/issue/EUR-73/m14-contract-freeze-and-canonical-document-update). Blocks [EUR-75](https://linear.app/eurusdevsec/issue/EUR-75/m14-access-scoped-learner-entry-and-discovery), [EUR-76](https://linear.app/eurusdevsec/issue/EUR-76/m14-quan-tri-website-management-hub) (predicate and mode helper).

#### Security and failure cases

Server-side only; no `Gate::before`; no secret or real email in source, seeders or evidence.

#### Verification plan

New `tests/Feature/ExpertOwnerModeTest.php` (both modes) plus `PlatformAdministrationTest`, `CommunityBaselineTest`, explicit paths on `scool_test`.

#### Evidence

- Commit / PR / exact revision: Pending.
- Automated command, environment, exit/result: Pending.
- Manual evidence / sanitized UI artifact: Pending, or justified N/A for docs/research.
- Independent review / required CI: Pending.
- Blocking AC / exceptions: Pending verification; never treat unresolved required AC as Done.
- Comment/artifact delivery: follow Section 8 Readback, Handoff and Review/retest requirements.

### [EUR-75](https://linear.app/eurusdevsec/issue/EUR-75/m14-access-scoped-learner-entry-and-discovery) — Access-scoped learner entry and discovery

#### Outcome

In an expert deployment, a learner lands where they can actually learn: their only community's Feed, a chooser of their communities, or a welcome state — never a directory of communities they have not joined.

#### Scope

Includes `/dashboard` routing in expert mode: valid intended destination first; zero accessible → **Chào mừng** state with invitation/access instructions; one → its Feed; several → **Cộng đồng của tôi** listing only `accessibleTo` communities. `CommunityController::index()` ignores `filter=all` and any search/pagination outside that scope in expert mode. Owner sees its operated communities (hub link from [EUR-76](https://linear.app/eurusdevsec/issue/EUR-76/m14-quan-tri-website-management-hub)). Switcher verified. Excludes general-mode discovery changes, a public/private listing field, auto-membership, deleting communities.

#### Workflow and contracts

Intended URL must be a local, permitted route; redirect never grants access. Remembered selection is re-authorized each request. Permitted About links and invitation pages keep working and do not add the community to the member's list or grant protected access.

#### Acceptance criteria

- [ ] AC-01: Given expert mode and a fresh verified learner with zero memberships, `/dashboard` shows the welcome state and no pre-existing community name.
- [ ] AC-02: Given one effective membership, `/dashboard` redirects to that community's Feed.
- [ ] AC-03: Given two memberships and one unjoined community, the chooser shows exactly the two; `?filter=all`, `?q=<unjoined name>`, `?page=2` and counts never reveal the third.
- [ ] AC-04: Given a membership suspended or expired after login, the next request neither lists nor redirects into that community.
- [ ] AC-05: Given a guest opening an invitation, registering and verifying email, the user returns to the invitation page.
- [ ] AC-06: Given a permitted About link, a learner sees the approved preview and join/checkout action without gaining membership.
- [ ] AC-07: Given general mode, the existing discovery behavior and `CommunityDiscoveryTest` remain green.

#### Dependencies

Blocked by [EUR-74](https://linear.app/eurusdevsec/issue/EUR-74/m14-expert-deployment-mode-and-deployment-owner-identity) (mode helper). Related: [EUR-77](https://linear.app/eurusdevsec/issue/EUR-77/m14-vietnamese-default-locale-foundation) copy.

#### Security and failure cases

Scope enforced in the query, not only in menus; no enumeration via counts or pagination.

#### Verification plan

New `tests/Feature/ExpertLearnerEntryTest.php`; existing `CommunityWorkspaceSwitcherTest`, `CommunityDiscoveryTest`, `PrivateCommunityAccessTest`, `CommunityAcquisitionTest`.

#### Evidence

- Commit / PR / exact revision: Pending.
- Automated command, environment, exit/result: Pending.
- Manual evidence / sanitized UI artifact: Pending, or justified N/A for docs/research.
- Independent review / required CI: Pending.
- Blocking AC / exceptions: Pending verification; never treat unresolved required AC as Done.
- Comment/artifact delivery: follow Section 8 Readback, Handoff and Review/retest requirements.

### [EUR-76](https://linear.app/eurusdevsec/issue/EUR-76/m14-quan-tri-website-management-hub) — Quản trị website management hub

#### Outcome

The owner opens **Quản trị website** from the main navigation and reaches every management task for every community without switching accounts or hunting through menus.

#### Scope

Includes a hub page listing the owner's communities (status, member count from existing queries) with **Tạo cộng đồng**, and per community direct links to existing tools: Tổng quan, Khóa học ([EUR-79](https://linear.app/eurusdevsec/issue/EUR-79/m14-course-management-workspace-tree-editor) workspace), Thành viên, Lời mời, Báo cáo, Đơn hàng, Bài nộp, Cài đặt; plus link to community administration (`/admin/communities`). Navigation link visible only to the deployment owner (general mode: creators keep the current "Manage Community" path). Excludes new site-wide orders/members/settings lists, new metrics, branding/settings editors.

#### Workflow and contracts

Verified deployment owner -> management hub -> chosen owned community -> existing authorized management route. Preserve context and label status/count scope; ordinary learners cannot fetch the hub.

#### Acceptance criteria

- [ ] AC-01: Given the owner with two communities, the hub lists both and each link opens the correct community's existing tool.
- [ ] AC-02: Given a learner, the hub link is absent and the hub URL returns 403.
- [ ] AC-03: Given 375px and desktop, the hub is usable without horizontal overflow and reachable by keyboard.
- [ ] AC-04: Every label on the hub is Vietnamese.

#### Dependencies

Blocked by [EUR-74](https://linear.app/eurusdevsec/issue/EUR-74/m14-expert-deployment-mode-and-deployment-owner-identity). Related: [EUR-79](https://linear.app/eurusdevsec/issue/EUR-79/m14-course-management-workspace-tree-editor) (course link target).

#### Security and failure cases

Authorize the hub server-side and scope every linked action to ownership. A hidden menu is not access control; status/counts must not leak unrelated communities.

#### Verification plan

Feature test for visibility/denial; browser check at 375 and 1280.

#### Evidence

- Commit / PR / exact revision: Pending.
- Automated command, environment, exit/result: Pending.
- Manual evidence / sanitized UI artifact: Pending, or justified N/A for docs/research.
- Independent review / required CI: Pending.
- Blocking AC / exceptions: Pending verification; never treat unresolved required AC as Done.
- Comment/artifact delivery: follow Section 8 Readback, Handoff and Review/retest requirements.

### [EUR-77](https://linear.app/eurusdevsec/issue/EUR-77/m14-vietnamese-default-locale-foundation) — Vietnamese default locale foundation

#### Outcome

A fresh session is Vietnamese by default — shell, navigation, six tabs, auth screens, validation messages and pagination — and every other issue has a place to put its translations.

#### Scope

Includes `APP_LOCALE=vi`, `APP_FALLBACK_LOCALE=en` in `config/app.php` defaults and `.env.example`; hand-written `lang/vi/{validation,auth,passwords,pagination}.php` (Vietnamese attribute names for real form fields); shared `lang/vi.json` for layouts/navigation/auth/components and six tabs; module translator registration (D14); wrapping hardcoded strings in these files; displayed Vietnamese status labels and readable date/VND formatting with timezone semantics unchanged. Separate the explicitly English compatibility-test configuration from the clean-bootstrap default-locale check; verify shipped Vietnamese glyphs. Excludes other issues' module views, a translation package and translating user content.

#### Workflow and contracts

Deployment defaults -> Laravel locale -> shared/module catalogs -> rendered Vietnamese responses. Keep default-bootstrap, explicit vi feature tests and explicit en compatibility runs separate; detect conflicting module translation keys.

#### Acceptance criteria

- [ ] AC-01: Given a new browser session, login, register, forgot-password and verify-email pages render in Vietnamese.
- [ ] AC-02: Given invalid login and registration input, the errors are Vietnamese and name the field in Vietnamese.
- [ ] AC-03: Given any community, the six tabs show the D6 labels.
- [ ] AC-04: In a separate clean application bootstrap with APP_LOCALE absent from process, test XML, test dotenv input and stale config cache, the resolved default is `vi` and a representative response is Vietnamese. Retain isolated test DB/mail settings; never load production secrets. A separate compatibility run explicitly uses `en`; Vietnamese feature tests explicitly use `vi`. Calling `setLocale('vi')` after bootstrap alone does not prove the deployment default.
- [ ] AC-05: A module catalog file under `lang/modules/<module>/vi.json` is loaded (test).
- [ ] AC-06: Vietnamese diacritics render in the shipped font with no missing-glyph fallback (browser check).

#### Dependencies

Blocked by [EUR-73](https://linear.app/eurusdevsec/issue/EUR-73/m14-contract-freeze-and-canonical-document-update). Blocks [EUR-78](https://linear.app/eurusdevsec/issue/EUR-78/m14-vietnamese-sweep-account-commerce-admin-and-secondary-screens), [EUR-81](https://linear.app/eurusdevsec/issue/EUR-81/m14-learner-course-flow-and-completion-feedback), [EUR-82](https://linear.app/eurusdevsec/issue/EUR-82/m14-community-discussion-members-and-points-clarity) (catalog mechanism); merge first.

#### Security and failure cases

No production environment/secrets in default-locale tests. Preserve enum/API contracts, escaped content and isolated test DB/mail configuration; catalog registration must be deterministic.

#### Verification plan

New `tests/Feature/VietnameseLocaleTest.php`; existing auth tests; build; browser capture.

#### Evidence

- Commit / PR / exact revision: Pending.
- Automated command, environment, exit/result: Pending.
- Manual evidence / sanitized UI artifact: Pending, or justified N/A for docs/research.
- Independent review / required CI: Pending.
- Blocking AC / exceptions: Pending verification; never treat unresolved required AC as Done.
- Comment/artifact delivery: follow Section 8 Readback, Handoff and Review/retest requirements.

### [EUR-78](https://linear.app/eurusdevsec/issue/EUR-78/m14-vietnamese-sweep-account-commerce-admin-and-secondary-screens) — Vietnamese sweep: account, commerce, admin and secondary screens

#### Outcome

No screen reachable by the owner or a learner still shows English interface text.

#### Scope

Includes the views in the [EUR-78](https://linear.app/eurusdevsec/issue/EUR-78/m14-vietnamese-sweep-account-commerce-admin-and-secondary-screens) ownership row, controller flash messages and mail/notification text, written to assigned account/admin catalogs; wrapping remaining hardcoded strings; mapping provider/API errors to useful Vietnamese feedback without internals. Include retained landing, acquisition preview, framework verification/password-reset messages and invitation subject/body. Combine source scan and rendered checks: grep alone cannot prove translation completeness. Excludes views assigned to other lanes and user content.

#### Workflow and contracts

Enumerate reachable owner/learner screens and mail paths -> wrap/translate UI and feedback -> inspect rendered messages -> record legitimate exceptions. User content and stored API/status values remain unchanged.

#### Acceptance criteria

- [ ] AC-01: Given each screen in scope, visible interface text, buttons, empty and error states are Vietnamese.
- [ ] AC-02: The invitation mail subject and body are Vietnamese; the link still works.
- [ ] AC-03: A screen/message inventory and source scan have no unexplained user-facing English interface text; rendered spot checks cover landing, About preview, account, Admin and verification/password-reset/invitation messages. Document legitimate brand, code/protocol and user-content exceptions.
- [ ] AC-04: Paid screens keep their Test Mode labeling in Vietnamese.

#### Dependencies

Blocked by [EUR-77](https://linear.app/eurusdevsec/issue/EUR-77/m14-vietnamese-default-locale-foundation). Related: [EUR-76](https://linear.app/eurusdevsec/issue/EUR-76/m14-quan-tri-website-management-hub) hub labels.

#### Security and failure cases

Escape untrusted substitutions, retain signed invitation/reset/verification URLs and Test Mode meaning. Never expose provider internals or real recipient/token data.

#### Verification plan

Affected feature tests with explicit locale; scan command recorded; browser spot checks.

#### Evidence

- Commit / PR / exact revision: Pending.
- Automated command, environment, exit/result: Pending.
- Manual evidence / sanitized UI artifact: Pending, or justified N/A for docs/research.
- Independent review / required CI: Pending.
- Blocking AC / exceptions: Pending verification; never treat unresolved required AC as Done.
- Comment/artifact delivery: follow Section 8 Readback, Handoff and Review/retest requirements.

### [EUR-79](https://linear.app/eurusdevsec/issue/EUR-79/m14-course-management-workspace-tree-editor) — Course management workspace (tree + editor)

#### Outcome

The owner manages a course the way Skool does: chapter/lesson tree on one side, the selected item's editor on the other, with create, reorder, publish and delete in one place.

#### Scope

Includes a management view for creators: **Lớp Học → Quản lý khóa học → khóa học**, tree of sections and lessons (status badges Nháp/Đã xuất bản), selected lesson/section editor beside it (stacked on mobile), inline **Thêm chương / Thêm bài học**, existing reorder controls, course settings and cover in a secondary panel. Uses existing store/update/destroy/reorder endpoints; selected item kept in the URL (query parameter) so redirects and validation errors return to it with input preserved. Members never see this view. Translations in `lang/modules/classroom/vi.json`. Excludes new endpoints for drag-and-drop, bulk import, attachments.

#### Workflow and contracts

Authorized creator -> chosen course -> selected chapter/lesson in URL -> existing mutation -> persisted tree/editor state. Validation retains selected item and entered text; preserve draft/published and existing deletion/retention rules.

#### Acceptance criteria

- [ ] AC-01: Given the owner, when they create a course, two chapters and three lessons, then each appears in the tree without a full page hunt and in the saved order.
- [ ] AC-02: Given a validation error while editing lesson 2, the page returns with lesson 2 selected and the entered text preserved.
- [ ] AC-03: Reordering a lesson within a chapter and a chapter within the course persists after refresh.
- [ ] AC-04: Given a learner or a creator of another community, the management view and every mutation (including forged section/lesson IDs from another course) return 403/404.
- [ ] AC-05: At 375px the tree and editor are usable without horizontal overflow; keyboard reaches tree items, editor fields and save.
- [ ] AC-06: All labels are Vietnamese.

#### Dependencies

Blocked by [EUR-73](https://linear.app/eurusdevsec/issue/EUR-73/m14-contract-freeze-and-canonical-document-update). Related: [EUR-80](https://linear.app/eurusdevsec/issue/EUR-80/m14-lesson-authoring-toolbar-preview-and-resource-links) (component embedded in the editor), [EUR-76](https://linear.app/eurusdevsec/issue/EUR-76/m14-quan-tri-website-management-hub) (link target).

#### Security and failure cases

Existing `CoursePolicy` and resource ancestry on every action.

#### Verification plan

`ClassroomPublishingTest`, `ClassroomRegressionTest`, `CourseAccessAuditRegressionTest` plus new `tests/Feature/CourseManagementWorkspaceTest.php`; browser recording of AC-01–03.

#### Evidence

- Commit / PR / exact revision: Pending.
- Automated command, environment, exit/result: Pending.
- Manual evidence / sanitized UI artifact: Pending, or justified N/A for docs/research.
- Independent review / required CI: Pending.
- Blocking AC / exceptions: Pending verification; never treat unresolved required AC as Done.
- Comment/artifact delivery: follow Section 8 Readback, Handoff and Review/retest requirements.

### [EUR-80](https://linear.app/eurusdevsec/issue/EUR-80/m14-lesson-authoring-toolbar-preview-and-resource-links) — Lesson authoring toolbar, preview and resource links

#### Outcome

The owner formats lesson content (headings, bold, lists, links) with buttons and sees how it will look before publishing, without learning Markdown.

#### Scope

Includes a toolbar on `classroom-lesson-fields.blade.php` inserting Markdown for heading, bold, italic, bullet/numbered list, link and quote, with Vietnamese tooltips and short help; **Xem trước** tab rendering through an authorized server endpoint using `LessonMarkdown` (D15); guidance for labeled resource links. JS module registered with Alpine; text output inserted with `textContent`/server-sanitized HTML only. Excludes WYSIWYG, uploads, raw HTML, content migration.

#### Workflow and contracts

Authorized creator selects text -> toolbar inserts Markdown -> bounded authorized POST preview -> existing safe renderer -> preview. CSRF, throttling, content-size validation and course/community ancestry apply; no arbitrary URL fetch.

#### Acceptance criteria

- [ ] AC-01: Given the editor, each toolbar button wraps the selection (or inserts a template) and keeps focus in the field.
- [ ] AC-02: Given content with headings, lists and links, the preview matches the published reader output.
- [ ] AC-03: Given `<script>`, `javascript:` links or raw HTML, the preview and reader do not execute or render them.
- [ ] AC-04: Legacy plain-text lessons still render correctly.
- [ ] AC-05: The preview endpoint denies non-creators and foreign courses.

#### Dependencies

Blocked by [EUR-73](https://linear.app/eurusdevsec/issue/EUR-73/m14-contract-freeze-and-canonical-document-update). Related: [EUR-79](https://linear.app/eurusdevsec/issue/EUR-79/m14-course-management-workspace-tree-editor).

#### Security and failure cases

Deny foreign community/course and non-creator preview requests; CSRF/throttle/size guards and safe Markdown rendering apply. No raw HTML, unsafe schemes, attachments or server-side remote fetch.

#### Verification plan

JS test for toolbar insertion; feature test for preview authorization and sanitization; existing `LessonMarkdown` tests.

#### Evidence

- Commit / PR / exact revision: Pending.
- Automated command, environment, exit/result: Pending.
- Manual evidence / sanitized UI artifact: Pending, or justified N/A for docs/research.
- Independent review / required CI: Pending.
- Blocking AC / exceptions: Pending verification; never treat unresolved required AC as Done.
- Comment/artifact delivery: follow Section 8 Readback, Handoff and Review/retest requirements.

### [EUR-81](https://linear.app/eurusdevsec/issue/EUR-81/m14-learner-course-flow-and-completion-feedback) — Learner course flow and completion feedback

#### Outcome

A learner opens a course, sees where to start or continue, studies with a clear outline, and marks lessons complete with feedback that reflects what the server saved.

#### Scope

Includes a prominent **Bắt đầu học / Học tiếp** on course entry pointing to the first incomplete published lesson; retained reader layout, highlight, previous/next and mobile outline; completion button with loading, success and error/retry states driven by the JSON response; translations of reader, course page and `lesson-progress` component. Excludes auto-complete, forced auto-next, new progress rules.

#### Workflow and contracts

Authorized learner -> Start/Continue -> published lesson -> completion mutation -> server-confirmed JSON -> visible progress. Failure retains prior state and allows retry; no auto-advance or client-only grant.

#### Acceptance criteria

- [ ] AC-01: Given a learner with lessons 1–2 complete, **Học tiếp** opens lesson 3.
- [ ] AC-02: Given a failed completion request, the previous state remains, a Vietnamese error appears and retry works.
- [ ] AC-03: Double-clicking completion creates one progress record and no duplicate contribution.
- [ ] AC-04: Drafts and empty unpublished sections stay hidden; suspended/removed users and mismatched course/lesson URLs cannot read content or change progress.
- [ ] AC-05: An unentitled learner on a paid course sees price/access explanation and cannot fetch lesson bodies or private media.

#### Dependencies

Blocked by [EUR-77](https://linear.app/eurusdevsec/issue/EUR-77/m14-vietnamese-default-locale-foundation). Related: [EUR-79](https://linear.app/eurusdevsec/issue/EUR-79/m14-course-management-workspace-tree-editor) (shared classroom catalog — append keys in its file by agreement or use `lang/modules/reader/vi.json`).

#### Security and failure cases

Re-authorize membership, community/course/lesson state and ancestry server-side. Do not infer entitlement or progress from UI state; retain idempotency.

#### Verification plan

Existing progress, course access and secure stream tests; new completion-feedback JS test; browser flow.

#### Evidence

- Commit / PR / exact revision: Pending.
- Automated command, environment, exit/result: Pending.
- Manual evidence / sanitized UI artifact: Pending, or justified N/A for docs/research.
- Independent review / required CI: Pending.
- Blocking AC / exceptions: Pending verification; never treat unresolved required AC as Done.
- Comment/artifact delivery: follow Section 8 Readback, Handoff and Review/retest requirements.

### [EUR-82](https://linear.app/eurusdevsec/issue/EUR-82/m14-community-discussion-members-and-points-clarity) — Community discussion, members and points clarity

#### Outcome

Members post, reply and find each other easily, and everyone understands how points and levels are earned.

#### Scope

Includes checking and fixing real friction in posting, replying, member search and invitations (clear success/failure feedback); a short Vietnamese **Cách tính điểm** explanation from the actual ledger rules, ranking window and `levelForPoints()` thresholds on the leaderboard; translations of feed, posts, members/directory, leaderboard, events and Livewire views in `lang/modules/community/vi.json`. Excludes point rule changes, online counts, new activity types.

#### Workflow and contracts

Effective member -> post/reply/search -> validated mutation and readable feedback. Eligible like/unlike uses the existing scoped ledger and ranking period; explain actual points/levels without changing rules.

#### Acceptance criteria

- [ ] AC-01: Given a member, creating a post and a reply shows Vietnamese confirmation and the content appears without reload confusion.
- [ ] AC-02: Another member cannot edit or delete someone else's content; authorized moderation works.
- [ ] AC-03: Repeated like/unlike follows the ledger without duplicate points or cross-community scoring.
- [ ] AC-04: The leaderboard shows the real rules and level thresholds; no placeholder values.
- [ ] AC-05: Feed, members, leaderboard and events are fully Vietnamese.

#### Dependencies

Blocked by [EUR-77](https://linear.app/eurusdevsec/issue/EUR-77/m14-vietnamese-default-locale-foundation).

#### Security and failure cases

Retain authorship/moderation boundaries, tenant scoping, inactive-user rules and ledger idempotency. No new scoring activities or manufactured counters.

#### Verification plan

`CommunityLeaderboardTest`, `LeaderboardMultiMetricTest`, post/comment/like tests; browser check.

#### Evidence

- Commit / PR / exact revision: Pending.
- Automated command, environment, exit/result: Pending.
- Manual evidence / sanitized UI artifact: Pending, or justified N/A for docs/research.
- Independent review / required CI: Pending.
- Blocking AC / exceptions: Pending verification; never treat unresolved required AC as Done.
- Comment/artifact delivery: follow Section 8 Readback, Handoff and Review/retest requirements.

### [EUR-83](https://linear.app/eurusdevsec/issue/EUR-83/m14-integrated-acceptance-of-the-owner-ready-release) — Integrated acceptance of the owner-ready release

#### Outcome

One integrated revision demonstrates the delivery journey end to end on `127.0.0.1:8080`, with evidence the mentor can review.

#### Scope

Includes the role-based browser run: owner (expert mode) → hub → two communities → course in tree + editor → preview/publish → invitation; fresh learner → welcome → invitation → join → study → complete → discuss; denied actor attempts. Sequential guarded tests on `scool_test` with explicit file paths, JS tests, production build, Pint, `git diff --check`, review and CI. Sanitized captures at 375 and 1280. Excludes host deployment.

#### Workflow and contracts

Verify source/runtime mounts and full test discovery -> integrate candidate -> sequential guarded automation/build -> role/state browser flows -> independent review and required CI. Host release is separate.

#### Acceptance criteria

- [ ] AC-01: Every required AC of [EUR-74](https://linear.app/eurusdevsec/issue/EUR-74/m14-expert-deployment-mode-and-deployment-owner-identity)–[EUR-82](https://linear.app/eurusdevsec/issue/EUR-82/m14-community-discussion-members-and-points-clarity) passes with evidence applicable to one integrated revision. An open or failed required AC blocks this issue's Done state; reporting the reason is not an alternative successful result.
- [ ] AC-02: Required CI passes on the PR; local success is not reported as CI success.
- [ ] AC-03: Served assets match the build manifest; landing motion baseline unchanged.
- [ ] AC-04: Handoff records commands, results, revision, captures, configuration steps and non-blocking limitations; unresolved required AC are never reported as accepted.
- [ ] AC-05: Full-suite execution discovers the expected complete test inventory on the correct runtime mounts and passes, with command/revision/environment evidence. Investigate the reported 74-test anomaly if reproducible; explicit-file targeted runs alone cannot meet this AC.

#### Dependencies

Blocked by [EUR-74](https://linear.app/eurusdevsec/issue/EUR-74/m14-expert-deployment-mode-and-deployment-owner-identity)–[EUR-82](https://linear.app/eurusdevsec/issue/EUR-82/m14-community-discussion-members-and-points-clarity). Blocks [EUR-84](https://linear.app/eurusdevsec/issue/EUR-84/m14-expert-site-provisioning-runbook-and-host-release) deployment.

#### Security and failure cases

Run DB tests sequentially on guarded scool_test only. No development-data resets, real payments or unsanctioned moderation. Preserve required CI/review gates.

#### Verification plan

Validate the complete discovered test inventory and run the repository's required guarded full suite, relevant JS tests, build, Pint and final diff/privacy checks. Record exact commands, revision and results. Perform owner/learner/denied-role browser checks at 375/1280 and relevant keyboard states.

#### Evidence

- Commit / PR / exact revision: Pending.
- Automated command, environment, exit/result: Pending.
- Manual evidence / sanitized UI artifact: Pending, or justified N/A for docs/research.
- Independent review / required CI: Pending.
- Blocking AC / exceptions: Pending verification; never treat unresolved required AC as Done.
- Comment/artifact delivery: follow Section 8 Readback, Handoff and Review/retest requirements.

### [EUR-84](https://linear.app/eurusdevsec/issue/EUR-84/m14-expert-site-provisioning-runbook-and-host-release) — Expert-site provisioning runbook and host release

#### Outcome

An operator can stand up a new expert's website from the runbook alone, and the accepted release runs on the host with data preserved.

#### Scope

Extend the existing canonical deployment-documentation route instead of creating a competing runbook: domain, expert/owner configuration, separate database/user, application key, uploads, queue, SMTP, owner registration/verification, `scool:owner:check`, smoke, backup and rollback. After explicit authorization, update CloudPanel with the documented code/dependency/build/schema/cache/queue procedure. Azure remains separate; inspect its interrupted update first. Excludes local-DB imports, destructive seeders, overwriting uploads and automatic ownership transfer.

#### Workflow and contracts

Approved environment and backup -> owner/release mapping check -> documented dependency/build/schema/cache/queue update -> host smoke -> handover or rollback. Use the existing deployment guide and approved data operations only.

#### Acceptance criteria

- [ ] AC-01: Following only the runbook, a second expert site is configured on a test environment without code changes and the check command reports a valid owner.
- [ ] AC-02: After deployment, accounts, memberships, payments, uploads and progress are preserved; the deployed revision and assets are verified.
- [ ] AC-03: With the queue worker running, verification and invitation mail reach an authorized test inbox; no log-mail or Mailpit result is presented as real delivery.
- [ ] AC-04: Owner grants or ownership changes on the host are listed with exact targets and separately approved.
- [ ] AC-05: Every intended operated release community passes the owner check before handover; owner and fresh learner complete create/publish/admit/study/discuss on the deployed revision. Distinguish local acceptance, host deployment and real mail delivery.
- [ ] AC-06: Independent expert-site provisioning is proved in an approved isolated local/test environment without implicitly creating a new production site or incurring cloud costs. Existing host data/configuration are preserved.

#### Dependencies

Runbook: Blocked by [EUR-73](https://linear.app/eurusdevsec/issue/EUR-73/m14-contract-freeze-and-canonical-document-update). Deployment: Blocked by [EUR-83](https://linear.app/eurusdevsec/issue/EUR-83/m14-integrated-acceptance-of-the-owner-ready-release) and explicit authorization.

#### Security and failure cases

No secrets, recipient addresses or tokens in the runbook or evidence.

#### Verification plan

Review the canonical runbook, reproduce provisioning on approved isolated local/test infrastructure, then perform authorized host owner-check/revision/assets/mail/queue/browser/persistence smoke. Record exact commands/results; no live host work is implied by this draft.

#### Evidence

- Commit / PR / exact revision: Pending.
- Automated command, environment, exit/result: Pending.
- Manual evidence / sanitized UI artifact: Pending, or justified N/A for docs/research.
- Independent review / required CI: Pending.
- Blocking AC / exceptions: Pending verification; never treat unresolved required AC as Done.
- Comment/artifact delivery: follow Section 8 Readback, Handoff and Review/retest requirements.

### [EUR-85](https://linear.app/eurusdevsec/issue/EUR-85/m14-video-security-and-drm-research-note) — Video security and DRM research note

#### Outcome

The mentor gets a clear, honest answer about protecting course videos from download, and a recommendation for what to implement later.

#### Scope

Includes a note comparing access-controlled/signed URLs, the current S-cool masking and licensed DRM (Widevine, PlayReady, FairPlay; see the [Mux DRM guide](https://www.mux.com/docs/guides/protect-videos-with-drm)); what each prevents and does not prevent (screen recording, authorized viewer extraction); browser/device support; provider options, cost questions and an implementation outline. Excludes implementation, provider sign-up or purchase.

#### Workflow and contracts

Read current primary documentation and inspect existing protection -> compare guarantees/limits -> recommend a scoped next step -> independent review. No provider registration, implementation or purchase.

#### Acceptance criteria

- [ ] AC-01: The note states that current masking is not DRM and does not stop an authorized viewer from extracting content.
- [ ] AC-02: It recommends a next step with open cost/provider questions, and is reviewed.

#### Dependencies

None; independent research may run in parallel and does not authorize implementation.

#### Security and failure cases

Research only; cite primary sources and distinguish obfuscation/access controls from DRM. No secret evidence, account signup or commercial commitment.

#### Verification plan

Review against the original mentor research requirement; verify current technical claims from primary provider/browser documentation, date the findings and label implementation as deferred.

#### Evidence

- Commit / PR / exact revision: Pending.
- Automated command, environment, exit/result: Pending.
- Manual evidence / sanitized UI artifact: Pending, or justified N/A for docs/research.
- Independent review / required CI: Pending.
- Blocking AC / exceptions: Pending verification; never treat unresolved required AC as Done.
- Comment/artifact delivery: follow Section 8 Readback, Handoff and Review/retest requirements.

## 8. Milestone acceptance

**Local candidate accepted:** [EUR-73](https://linear.app/eurusdevsec/issue/EUR-73/m14-contract-freeze-and-canonical-document-update)–[EUR-83](https://linear.app/eurusdevsec/issue/EUR-83/m14-integrated-acceptance-of-the-owner-ready-release) meet every required AC on the integrated revision, with independent review, applicable complete automated checks, build and product-owner visual acceptance. This is local/source acceptance only; an open required AC blocks the corresponding Done state.

**Website handed over for real use:** [EUR-84](https://linear.app/eurusdevsec/issue/EUR-84/m14-expert-site-provisioning-runbook-and-host-release) also passes on the approved host: deployed revision/assets, valid owner mapping, persistent existing data, queue/mail and owner/learner browser journey. Scheduling deployment is not handover. CloudPanel acceptance never implies Azure is synchronized.

**Mentor request completed:** All of [EUR-73](https://linear.app/eurusdevsec/issue/EUR-73/m14-contract-freeze-and-canonical-document-update)–[EUR-85](https://linear.app/eurusdevsec/issue/EUR-85/m14-video-security-and-drm-research-note) are accepted, including the DRM research note; DRM implementation remains deferred. Only then mark the proposed milestone complete. If host release or research is scheduled later, leave the milestone open and report the accepted local candidate accurately. Paid features stay Test Mode unless separately accepted for live use.

### Required issue comments and artifacts

- Before implementation: **Readback and plan** comment naming understood outcome, affected contracts/files, verification and actual blockers.
- Before In Review: **Handoff** mapping each AC to evidence, exact commit/PR, commands and exit/results, environment, sanitized UI captures/recording, configuration/data impact and unresolved blockers. Keep missing evidence Pending.
- Before Done: independent **Review and retest** on the published candidate plus required review/CI/merge evidence. Each implementer fixes their own slice.
- Deployment record: backup/checkpoint, approved targets, deployed revision/assets, owner-check result, queue/mail/browser smoke and rollback readiness. No real recipient addresses, tokens, secrets or raw user/payment records.
- Publication: use actual EUR IDs, verified project/milestone, assignees, priorities, Points, existing labels, due dates and dependency relations from Section 6. Do not change unrelated issue/project state.
