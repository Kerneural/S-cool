# S-cool experience completion — Skool reference analysis

Decision date: 2026-10-16. Status: approved planning direction; implementation,
browser acceptance and release evidence for the added scope remain pending.

The supplied screen notes describe Skool as a reference. This normalized analysis
preserves those ideas and records S-cool's deliberate differences. It is not a
second design system, live backlog, or proof of feature parity.

## 1. Authority and evidence

- `design.md` owns visual/interaction rules and visual acceptance.
- [Product Scope, section 21](01_PRODUCT_SCOPE.md#21-approved-experience-completion-direction-2026-10-16) owns the approved capability boundary.
- [Delivery Plan, section 18](03_DELIVERY_PLAN.md#18-interactive-learning-and-experience-completion-2026-10-16) maps implementation work and the handoff sequence.
- Linear EUR-50/51 own current outcome, scope, AC and metadata.
- `docs/WORKING_CONTEXT.md` owns issue-specific technical checkpoints.
- Source baseline: HEAD `4261c54b86e3e9ebdb4357b1a81ee80546b97696`,
  branch `eur-49-learning-focus`, with concurrent EUR-49 edits. This is source
  inspection, not a clean implementation snapshot or fresh browser acceptance.
- EUR-49 retains its existing scope. Additions belong to EUR-50/51 after their
  prerequisites; do not interrupt the active implementation to apply this file.

| Evidence label | Meaning |
|---|---|
| Supplied observation | Original screen analysis; exact behavior is not independently established |
| Documentation verified | Official Skool documentation; not proof of a user's current screen |
| Source verified | Inspected S-cool source, not runtime/visual acceptance |
| Approved adaptation | Selected S-cool behavior requiring implementation and verification |
| Deferred | Preserved reference idea, outside EUR-50/51 implementation scope |

Official sources checked on 2026-10-16:

- [Notifications](https://help.skool.com/article/95-how-to-manage-my-notifications): account navigation, group settings, daily/weekly email categories.
- [Chat settings](https://help.skool.com/article/96-how-to-turn-off-my-skool-chat): per-group chat controls.
- [Course permissions](https://help.skool.com/article/23-how-to-set-permissions-for-a-course): multiple unlock mechanisms; these do not replace S-cool's contracts.
- [Course publishing](https://help.skool.com/article/143-how-to-publish-a-course): name, description, cover and publishing workflow.
- Supplied `skool.com/settings` URLs require login. Exact colors, ratios,
  thresholds, autosave and private-screen behavior remain supplied observations
  unless separately demonstrated.

## 2. S-cool identity and task-specific composition

Design for a private learning community: read, discuss, practise, receive feedback,
and understand demonstrated progress. The distinctive path is Learning Focus ->
contextual question -> practical evidence -> creator feedback -> verified skill.

Retain light canvas, white surfaces, slate text, teal actions and Be Vietnam Pro.
Community/course images provide local character; reward colors remain secondary.
Reuse Blade, existing Livewire islands, Alpine and Tailwind. Follow `design.md`
sections 6 and 11.6 for purposeful, restrained motion.

| Surface | Composition | Main task |
|---|---|---|
| My Communities | Filter toolbar and image-led responsive grid | Open an authorized community |
| Feed | Readable column and optional useful context rail | Read, respond or start a discussion |
| Classroom | Compact Learning Focus followed by course grid | Continue or select a course |
| Lesson | Flexible media/reading area and compact outline | Learn, ask, complete or submit evidence |
| Member profile | Identity summary and activity/skills region | Understand useful contributions |
| Settings | Account navigation and bounded form panel | Change one coherent group of settings |
| Events | Calendar/list suited to date comparison | Find and attend an event |
| Leaderboard | Explained metrics, filters and rankings | Understand contribution and provenance |
| About | Editorial story/roadmap and concise context | Understand the community and begin |
| Creator operations | Operational navigation and tables/forms | Publish, review or manage |

Keep the 1280px shell baseline. Prose may be narrower; grids/tables use available
space. Do not stretch paragraphs to fill a monitor or add empty panels to remove
margins. Avoid repeating a large cover/name/status block on every route. Mobile
has a deliberate reading/focus order, not simply a shrunken desktop arrangement.

## 3. Source baseline and completion map

IDs below are analysis recommendations, not new issues or status stores.
Delivery Plan section 18 maps their work packages and acceptance gates.

| ID | Source-verified baseline / gap | Approved adaptation | Issue |
|---|---|---|---|
| UX-01 | Text account trigger; utilities in community-workspace below global nav | Compact contextual header, real avatar/fallback, one instance per utility | EUR-51 |
| UX-02 | Private My Communities and community covers exist | Complete image/card hierarchy, aligned actions and filter/empty states | EUR-51 |
| UX-03 | Feed has categories, inline composer, reactive viewer and onboarding | Accessible contextual composer dialog, post metadata, consolidated filters and learning/event rail | EUR-50; contribution rail EUR-51 |
| UX-04 | Course grid/access/progress exist; inspected Course has no cover field | End-to-end cover management and visual course cards | EUR-50 |
| UX-05 | External embeds, text, outline and progress/navigation exist | Focused study layout, accessible outline, immediate progress feedback and contextual help | EUR-50 |
| UX-06 | Proof of Work is planned in EUR-50 | Private submission/revision/review connected to lesson and Learning Focus | EUR-50 |
| UX-07 | Community profile has avatar, bio, role and join date | Scoped skills, activity history/heatmap and useful identity links | EUR-51 |
| UX-08 | Community points/levels/leaderboard exist | Separate engagement, verified practice and helpful-answer metrics | EUR-51 |
| UX-09 | /profile stacks profile, preferences, password and deletion | Account sidebar and coherent settings sections using existing destinations | EUR-51 |
| UX-10 | notify_replies and notify_courses already exist | Dedicated settings with independent, acknowledged toggle saves | EUR-51 |
| UX-11 | Private About has story, intro, rules and creator context | Readable orientation, permitted roadmap and relevant actions | EUR-51 |
| UX-12 | Calendar/list and member directory exist | Task-specific layouts and complete long/empty/access states | EUR-51 |
| UX-13 | Tenant messages and in-app notifications exist | Clear inbox/read/error states and safe destination handling | EUR-51 |
| UX-14 | Creator, commerce, account and Admin routes exist | Route inventory and compatible controls preserving role/financial meaning | EUR-51 |
| UX-15 | Shared components exist; old coverage is historical | Reuse components and verify all routed surfaces with current evidence | Both; final matrix EUR-51 |

## 4. Learning and discussion surfaces — EUR-50

### Feed and composer

- One clear Write a post entry, real avatar/fallback and community context.
  A labelled composer dialog contains title, plain-text body, current category
  choices and optional authorized course/lesson context.
- Follow shared dialog focus entry/trap/return, Escape/Cancel and backdrop rules.
  Retain the draft in page memory or confirm discard before closing dirty input.
  Preserve values/errors after validation. No persistent draft service.
- Preserve direct post pages and a usable server-form fallback. Reuse reactive
  like/comment/save and persisted counts. No unsupported attachment/poll/GIF tools;
  categories keep existing optional semantics.
- Post cards have author/avatar, valid role, time, category/pinned state, readable
  preview and safe context breadcrumb. Fix literal escaped entities.
- A bounded latest-comment summary/avatar group may use currently visible comments.
  Missing/removed authors have a neutral fallback. Batch queries. Do not show an
  unread dot without a defined post-read model.
- Consolidate category/sort/Saved; retain URL filters, pagination and back/reload.
  The context rail reuses EUR-49 Learning Focus/event data, incomplete onboarding
  and compact community context. Avoid duplicate Continue panels or fake activity.

### Classroom and course covers

- Course cards have 16:9 covers, complete titles, permitted summary, real price/
  access/progress and one obvious Open/Continue action.
- Creator upload/replace/remove: JPEG/PNG/WebP, maximum 5 MiB and 4096px per side,
  server-validated content/dimensions, private local storage. No SVG/path/URL import.
- Failure preserves the old cover; clean staged files on failure and replaced files
  only after successful persistence. Missing images use local educational/initials
  fallbacks, never broken images or remote placeholder services.
- Deliver via authorized course route. Eligible members may see a published locked
  course's cover/title/price as catalog metadata; description/outline/lessons/progress
  retain entitlement checks. Draft cover is creator-only. Expired/suspended/
  cross-tenant requests fail closed.
- Included/purchased/locked/not-started/in-progress/complete/empty states use real
  facts. No new Premium/VIP/level/time unlock product.

### Lesson and Proof of Work

- Keep safe external embeds/provider controls; no custom player. Readable text,
  stable aspect ratio, breadcrumb and current/completed outline indicators.
  Prefer page scrolling over nested lesson-body scroll.
- Existing completion semantics gain acknowledged async feedback and form fallback.
  Continue is explicit; completion must not force navigation or imply verification.
- Ask a question reuses EUR-49 contextual composer/viewer and safe unavailable state.
- Proof of Work retains the original EUR-50 private lifecycle. Practical lessons
  have explicit creator-assigned skill labels for EUR-51, not inferred certification.
  Draft, Waiting for feedback, Needs revision, Verified and Rejected survive reload;
  feedback/history are actionable. Personal completion and verification stay distinct.
- Creator review needs a reachable actionable list/detail, status filters and
  revision history, rather than a hidden lesson-only action.

## 5. Identity, shell and operational surfaces — EUR-51

### Header and account settings

- Community header: S-cool home/brand and community selector left, scoped typeahead
  middle, notices/messages/avatar right. Fit long names/counts; one accessible
  instance of each utility. Compact identity replaces repeated large banners.
- Outside a community: My Communities/account/help navigation. No global search/
  inbox or tenant inferred from another browser tab. Resolve authorized route context;
  preserve Creator operations versus Platform Admin.
- Mobile retains named controls, expanding search row and reachable community tabs.
  Sticky regions never cover content/focus.
- Settings groups: Profile; Account & security; Notifications; My access;
  Payment history; Referrals. Share the shell with existing destinations; direct
  URLs/back/reload retain the active section. Preserve routes or compatibility redirects.
- Profile keeps avatar/full name/bio and adds optional GitHub/portfolio HTTPS links.
  Keep the existing bio limit and show counter/errors. No first/last split,
  change-name-once rule or global handle route. No server fetch/unfurl of links.
  Community profile never exposes email.
- Compact email/password/delete disclosures/dialogs retain current security contracts.
  Sensitive operations do not autosave.
- Preference toggles use only existing notify_replies/notify_courses semantics.
  Independently acknowledge saves, preserve the other field, recover on failure,
  survive refresh and converge after rapid actions. No new producer/channel.

### Community profile, activity and contribution

- Community-only identity/bio/role/join-date, demonstrated skills and contribution
  details; own profile has Edit profile, another eligible member has Message.
  Mobile presents identity before activity.
- History and heatmap are scoped to the selected community. Heatmap covers the last
  90 UTC calendar days including today; counts currently visible authored posts/
  comments by creation day; labels UTC and explains the definition. No likes,
  logins, manufactured streaks or private evidence content.
- Provide text totals and accessible daily list/table alternative. Exclude deleted/
  no-longer-visible sources; zero is valid. Aggregate in queries, not per cell.
  Do not claim deleted activity history exists where it was never retained.
- Skills derive from EUR-50 verified evidence and explicit labels. Show label/count/
  safe provenance, never another learner's private image/link/summary.
- Implement helpful-answer acceptance in EUR-51 on the existing contextual viewer:
  creator-only, eligible answer from another user, one current answer per contextual
  post, idempotent update and auditable correction. Deny self-awards/tenant mixing.
- Leaderboard separates engagement, verified practice and helpful answers with
  explained ordering/ties. Feed may reuse a small authorized ranking with explicit
  metric/window and See all; never relabel all-time data as 30-day data.

### About and other routed surfaces

- About is private orientation: actual intro/story/rules/creator and permitted course
  roadmap, not a public sales funnel. No fabricated review/online/admin values or
  promotional guarantees.
- Dashboard cards use authorized covers, role/access meaning, concise description
  and aligned Open action; no raw route strings as decoration.
- Events preserve month/list/timezone/cancellation and meeting-link guards.
  Members preserve search, useful bios/avatars and permitted profile/message actions.
- Messages stay participant-only, asynchronous and plain text. Notices re-authorize
  stale destinations. Referrals stay Simulated / Unpaid; payment/Test Mode meaning stays.
- Check existing Creator, checkout/account, guest/auth and Platform Admin routes for
  shell/component compatibility. Keep usable operational tables/forms; do not force
  these screens into the same social-card composition.

## 6. Full reference disposition

Deferred rows preserve the original analysis without authorizing implementation
or displaying dead production controls.

| Original reference idea | S-cool disposition |
|---|---|
| Header brand/switcher/search/chat/bell/avatar | Adopt UX-01, authorized tenant scope and real counts |
| Large profile avatar, bio, join date, Edit profile | Adopt UX-07/09 within community privacy |
| Level badge/progress | Reuse F-10; distinguish from verified skill |
| Activity history and heatmap | Adopt UX-07, visible sources/90 UTC days |
| Public membership list | Keep self-only My access; do not expose private membership graph |
| Public profile, custom handle and SEO visibility | Deferred public identity/global reputation |
| Followers/following | Deferred relationship model; no placeholder counters |
| Online now / Online total | Deferred presence system; recent activity is not online proof |
| Photo upload/cropping | Reuse avatar upload/preview/fallback; cropping deferred |
| First/last split and change-name-once | Keep full name; no copied restriction |
| Contribution/follower/age threshold for custom URL | Deferred with handles |
| Bio character counter | Adopt existing limit with feedback |
| Social links | Adopt optional GitHub/portfolio; other networks deferred |
| Map location/remove location/MBTI | Deferred; not selected learning needs |
| Settings sidebar/active section | Adopt UX-09, functional permitted destinations |
| Email/password dialogs and account deletion | Adopt compact controls with current security |
| Language/timezone preference/theme | Deferred new settings; keep English/light and explicit event zones |
| Log out everywhere/device sessions | Deferred security feature; preserve current logout |
| Follower/like/Ka-ching/referral notification toggles | No unsupported producers; improve existing preferences |
| Per-group digest/broadcast/reminder email settings | Deferred delivery/preference expansion |
| Instant preference save | Adopt UX-10 with acknowledgement/error/retry |
| Per-group chat on/off and blocked users | Deferred privacy lifecycle; keep eligibility checks |
| Chat sound/blinking tab/offline email | Deferred; no incidental presence/scheduler |
| Affiliate links/copy/metrics/history | Reuse private referral workflow/simulated ledger |
| Lifetime 40% recurring/platform affiliate | Not selected; preserve ADR-011's rate/window/eligibility |
| 30–60 day cookie attribution | Not selected; preserve email-bound invitation attribution |
| Wallet/payout/payment cards/available-soon balance | Deferred real financial products; no withdrawable-funds claim |
| Course thumbnails/status/progress/pagination | Adopt UX-04 with cover lifecycle |
| Premium/VIP/level/time unlock | Deferred pricing/access models; retain INCLUDED/ONE_TIME |
| Curriculum accordion/current/completed | Adopt UX-05 with accessible mobile outline |
| Custom video controls/CC/quality/speed | Use supported provider capabilities; no custom player |
| Automatic next lesson on completion | Explicit Continue, no forced navigation |
| Arbitrary lesson files/rich HTML/resources | Keep plain text/current safe links; private PoW images are separate |
| About video/story/creator/resources | Adopt UX-11 from permitted content/routes |
| Ratings/reviews/retention badges/video reactions | Deferred new models; no fabricated testimonials |
| Feed composer dialog/category choice | Adopt UX-03, preserve optional category |
| Unread post dot/latest-comment metadata | Visible bounded latest comments allowed; unread model deferred |
| Polls/files/GIF/mentions/rich-text editor | Deferred storage/content/notification features; no fake tools |
| Onboarding/community sidebar/event banner | Reuse actual onboarding/EUR-49 data compactly |
| Mini leaderboard/See all | Adopt UX-08, explicit metric/window |
| Powered by branding | Subtle S-cool identity, no repeated branding cards |

## 7. Reuse and verification

Inspect layouts, community-workspace, modal, dropdowns, fields/buttons, avatar
delivery, classroom fragments and Livewire discussion/search before extracting
components. Use concrete consumers and small APIs. No React/MUI/shadcn rewrite,
second Alpine bootstrap, generic UI engine, remote image or chart dependency.

For each scoped screen verify applicable populated/empty/long/error/loading/
success/locked/expired states at 320/375/768/1280px, 200% zoom, keyboard/focus,
touch targets, contrast and reduced motion. Async controls require failure/retry/
rapid-action checks; screenshots alone cannot establish behavior.

Record route/state coverage, migrations, guarded MySQL scool_test tests, browser/
build/review/CI evidence in the issue checkpoint. Recommendations are not accepted
implementation from this file alone. Complete EUR-50/51 in reviewable work packages;
Delivery Plan section 18 and Linear own execution, not a replacement application.
