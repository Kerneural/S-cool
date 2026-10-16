# S-cool design system

Status: approved design direction; implementation and visual acceptance are pending.
This is the single shared visual/interaction reference. Feature scope and acceptance
remain in Linear and `docs/01_PRODUCT_SCOPE.md` / `docs/03_DELIVERY_PLAN.md`.
Read `AGENTS.md` and `docs/AI_WORKFLOW.md` first. This file does not authorize a
redesign during a functional issue or change backend access rules.

## 1. Product and design intent

S-cool is a private multi-community learning and knowledge-sharing application.
Members read, discuss, learn and attend events. Creators publish content and operate
their communities. Platform Admin handles platform moderation with separate rights.

Design for these three experiences:

- Community: welcoming, readable and conversational; content and people are primary.
- Classroom: calm, focused reading/video with a clear course outline and progress.
- Creator management: compact, predictable tables/forms with trustworthy data.

Use a light canvas, white surfaces, dark text and restrained teal accents. Community
covers/avatars provide local identity inside a consistent application shell. Primary
UI copy currently uses English as the baseline with existing translation keys and
helpers (`__()`). Vietnamese localization is deferred to a separate approved task
and must migrate related copy together in a scoped localization change. Shared
technical documentation remains English.

Required stack: Laravel Blade, Alpine.js, Tailwind CSS and Vite. Prefer existing
components. Do not add a frontend framework, UI/icon/chart package or remote service
without applicable authorization. Use semantic HTML and accessible controls.

## 2. Current baseline and adoption boundary

Source inspected on 2026-10-13 at HEAD `2996aa6a24e4f8821d9351c0beb6460b554b8d6e`:

- `package.json`: Tailwind `^4.3.3` and `@tailwindcss/postcss`.
- `resources/css/app.css`: Tailwind import plus `@config` for the existing JS config.
- `tailwind.config.js`: forms plugin, content paths and Figtree font extension.
- `resources/views/layouts/app.blade.php` and `guest.blade.php`: external Figtree loading.
- Shared button/input/modal/dropdown components already exist; reuse and inspect them.

The target tokens/font below are NOT installed utilities or current UI evidence.
Adopt them through a scoped UI change: map tokens, adapt shared components, then
review representative Feed, Lesson and Creator management screens. Functional
F-xx work may add necessary controls using the accepted current components. It must
not restyle the entire application merely because this document exists.

Reverify package/config/source before implementation; this snapshot may age.

## 3. Color tokens

Exact values are the target source of truth. Existing Tailwind palette names are
convenient references, not a guarantee of these exact hex values across versions.

| Semantic token | Value | Intended use |
|---|---|---|
| canvas | `#F8FAFC` | Application background |
| surface | `#FFFFFF` | Content panels, forms, menus |
| ink | `#0F172A` | Main text and headings |
| muted | `#475569` | Secondary text, metadata and placeholders |
| line | `#E2E8F0` | Decorative panel/list separators |
| control-line | `#64748B` | Input/control boundary when needed to identify it |
| brand | `#0F766E` | Primary actions, active navigation and progress |
| brand-hover | `#115E59` | Hover/pressed primary action |
| brand-soft | `#F0FDFA` | Selected/supporting background |
| reward | `#B45309` | Points, levels and earned achievements |
| danger | `#B91C1C` | Destructive action/error emphasis |

Primary buttons use brand with white text. Links use brand plus a non-color cue in
body text. Active navigation uses a label and underline/shape, not color alone.
Use neutral secondary actions and one clear primary action per task region.

Status presets: success emerald-50/700/200; warning amber-50/800/200; error
rose-50/700/200; information sky-50/700/200 (background/text/border). Verify actual
compiled combinations. State always includes readable text; never invent a status
or infer payment/access rights from a visual badge.

Validate normal text at least 4.5:1 and large text at least 3:1. Important control
boundaries/state indicators require suitable non-text contrast; the pale decorative
line token is not sufficient for every interactive control. Focus must be visible.

### Tailwind mapping contract

During the approved adoption change, define semantic colors centrally in
`resources/css/app.css` using top-level Tailwind v4 `@theme`: for example,
`--color-brand` enables `bg-brand`, `text-brand` and related utilities. Map the
remaining semantic colors once using the same convention. Keep existing imports,
the forms plugin and compatibility config until their consumers are reviewed.

Do not reset the default token namespace, remove `@config` as cosmetic cleanup,
or redefine these values per page. Use semantic tokens for brand/common surfaces
and verified palette utilities for status variants. Keep utility class names
statically discoverable rather than assembling arbitrary class strings at runtime.

## 4. Typography

Target family: Be Vietnam Pro, with `ui-sans-serif`, `system-ui`, `Segoe UI` and
`sans-serif` fallbacks. Self-host licensed WOFF2 font assets when adoption begins;
retain the license, include Vietnamese glyph coverage and use `font-display: swap`.
Load only necessary weights (400/500/600/700). Keep fallback text visible if assets
fail; test accented Vietnamese and mixed-language lesson content.

The adoption change must update both app/guest font loading and the central sans
font declaration consistently. Do not leave Figtree loaders plus a competing new
font override, or change the global font incidentally inside another feature.

| Role | Target size / weight / line height |
|---|---|
| Page title | 26–30px / 600–700 / 1.25 |
| Content/card heading | 18–20px / 600 / 1.4 |
| Post/lesson body | 16px / 400 / 1.65 |
| Form/table/menu/button | 14–16px / 400–600 / 1.5 |
| Metadata/helper text | 13–14px / 400 / 1.5 |

Use sentence-case actions, readable table labels and consistent headings. Avoid
all-caps buttons or excessive tracking. Each page has one meaningful h1; preserve
heading hierarchy. Use tabular numerals for comparative metrics. Reserve monospace
for code/reference identifiers, not ordinary learning content. Long text wraps;
do not truncate essential validation, title context or payment instructions.

## 5. Layout, spacing and surfaces

- Overall desktop content max width: 1280px; fluid with 16px mobile/24–32px desktop gutters.
- Header: approximately 64px; switcher, scoped search and account/notification controls.
- Community navigation: clear active tab; horizontal scroll on narrow screens with
  visible affordance. Render only routes/features available to the current role.
- Feed: readable main column around 680–760px plus optional 280–300px information
  sidebar; one column on mobile. Do not stretch paragraphs across the whole screen.
- Lesson: flexible content area plus 280–300px course outline; collapsible outline
  on mobile. Preserve video aspect ratio and usable text width.
- Creator management: optional approximately 240px sidebar, flexible data area;
  mobile drawer/navigation. Creator and Platform Admin workspaces remain distinct.
- Use the existing Tailwind spacing scale: 8/12/16/24/32px for common gaps. Region
  spacing is normally 24–32px; panel padding 16–24px; label/input gap 4–8px.
- Button/input radius: 8px. Panel/card radius: 12px. Avoid deeply nested cards.
- Surfaces use subtle borders; shadow mainly expresses elevation for menus/modals.
  A modest card shadow may be used consistently where separation needs it.
- Photos/thumbnails communicate community/course identity. Keep ratios consistent,
  alt text appropriate and a neutral fallback; do not publish private media paths.

Avoid ornamental gradients, glass layers and animated backgrounds in routine
learning/management screens. Meaningful content, navigation and states take space
before decoration. Dark mode is a later scoped decision after light-mode acceptance.

## 6. Component and behavior contract

### Buttons and links

Primary, secondary and destructive variants share sizing/focus/disabled/loading
behavior. Use about 40–44px height for comfortable common controls. Keep icon-only
targets comfortably tappable and give them an accessible name. Declare button type
explicitly. Navigation is a link; a state-changing action is a button/form.
Destructive confirms name the affected object and explain the consequence.

### Forms and feedback

Visible associated labels, clear required/optional status and relevant limits.
Preserve `name`, IDs, route names, methods, CSRF, old values and server validation.
Show errors next to fields and focus a useful error summary when appropriate.
Submitting state prevents accidental duplicate submission while the server retains
idempotency. Keep input and errors readable on mobile. A placeholder is not a label.

### Lists, tables, cards and metrics

Feed uses content lists/cards; administrative comparisons use tables. Align numbers
and make primary actions discoverable. Paginate existing data flows. On small screens,
use a deliberate horizontal table scroll or a semantic alternative with actions
still reachable. Preserve labels/status/meaning for long names and large amounts.
Metrics reflect controller/query data; label Test Mode receipts as simulated.
Never invent sample business values or online counts to make a screen attractive.

### Modal and dropdown

Reuse shared components and verify all affected consumers when changing them.
Modal has a labelled dialog, focus entry/trap/return, Escape/Cancel behavior and a
clear confirm action. Backdrop stays behind the panel; clicks inside must not dismiss
it unintentionally. Error feedback remains visible inside the dialog. Prevent focus
or key handlers in hidden dialogs from interfering with the page. Dropdown supports
keyboard access, outside-click close and visible focus. Tooltip is supplementary.

### Dynamic states

Provide states appropriate to the real request: empty/no results, validation/error,
successful mutation, processing and unavailable access. Server-rendered initial pages
do not need invented loading skeletons. Reserve layout space for actual asynchronous
updates. Explain disabled/unavailable actions where that helps the user; do not leak
private metadata. Preserve backend 404/private-access behavior and draft visibility.

### Icons and motion

Use one consistent line-icon style, approximately 20–24px, from existing inline SVG
assets/components first. Do not use emoji as core navigation/status icons or install
a package merely to change an icon. Decorative SVGs are hidden from assistive tech.

Small transitions: about 150–180ms for menus/button feedback; 180–220ms for dialogs.
Like/progress feedback may be restrained and must reflect the persisted result.
Respect `prefers-reduced-motion`; use immediate states when motion is reduced.
Keep focus/controls responsive during transitions. Avoid parallax, page-entry
animations and recurring celebratory effects during reading/learning.

## 7. Responsive and accessibility acceptance

Check at 375px, 768px and 1280px, plus long content and 200% browser zoom. Where
practical also check 320px reflow. There should be no page-wide horizontal overflow;
an intentional data-table scroll region is different and must remain usable.

Use semantic nav/main/form/button/link/label structures, keyboard-operable controls,
visible focus, logical reading/tab order and appropriate accessible names. Use ARIA
only where semantics need it. Status includes text and feedback can be announced
without repeatedly interrupting reading. Meaningful images have alt text; decorative
images do not repeat nearby labels. Verify color contrast on actual rendered states.

Test desktop/mobile open-close menus, modal Cancel/Confirm/error/Escape/focus return,
forms with long errors, loading failure/retry and stale revoked access when relevant.
Do not claim accessibility compliance from these guidelines or static tests alone.

## 8. Working with UI/UX Pro Max

Optional reference: https://github.com/nextlevelbuilder/ui-ux-pro-max-skill
The upstream README and skill usage instructions were reviewed on 2026-10-15.
They support Laravel and HTML/Tailwind searches, UX guidance and design proposals.
No installer or search engine was executed in this review; the implementation
session must verify its actual local skill path. This is a development reference,
not an application dependency or a ready-made component library.

Use it with this product brief: private learning community; readable Feed/Classroom;
separate data-focused Creator management; Laravel Blade + Alpine + Tailwind v4;
light slate/white surfaces; teal actions; Be Vietnam Pro target; restrained motion.

Apply recommendations through these rules:

1. Read this file before requesting upstream suggestions; keep established tokens,
   component contracts and issue scope in the request.
2. Select Laravel/Blade or HTML/Tailwind guidance; do not adopt default framework,
   Livewire/Inertia, package or service suggestions without a separate decision.
3. Use upstream search/checklists to improve a bounded screen and expose problems.
   A suggested palette/layout is a proposal; it cannot replace this design direction.
4. Keep `design.md` canonical. Do not enable persistent generation of a parallel
   MASTER/page-override design system unless its output is reconciled here through
   an explicitly scoped documentation change. Avoid conflicting design files.
5. Keep personal IDE/skill installers/caches/configuration private and optional.
   Shared instructions must work for teammates who do not install this skill.
6. Verify source changes and rendered screens. Generated output is not test evidence,
   and a successful build does not establish usability, parity or accessibility.

### Reference workflow for this redesign

Read the current [upstream usage guide](https://github.com/nextlevelbuilder/ui-ux-pro-max-skill#usage)
and [search workflow](https://github.com/nextlevelbuilder/ui-ux-pro-max-skill/blob/main/.claude/skills/ui-ux-pro-max/SKILL.md)
before use. If available locally, locate its actual `scripts/search.py` and use
that absolute path; do not assume the README's Claude example path applies here.

Suggested advisory queries (replace `<absolute-search-path>` with the verified path):

```text
python "<absolute-search-path>" "private learning community" --design-system -p "S-cool"
python "<absolute-search-path>" "keyboard focus modal" --domain ux
python "<absolute-search-path>" "reduced motion feedback" --domain ux
python "<absolute-search-path>" "responsive navigation Blade" --stack laravel
python "<absolute-search-path>" "responsive layout" --stack html-tailwind
```

Check whether each result fits the product, page and installed stack. If it does
not, narrow the query once; mark unmatched advice as fallback. Reconcile selected
recommendations into this document. Omit `--persist`: no competing MASTER/page
rules. The optional motion dial can return GSAP examples; it does not authorize
installing GSAP. Prefer existing CSS/Alpine for this project's interactions.

The README documents Antigravity support and `uipro init --ai antigravity` for
installation. This review does not install software or alter IDE configuration;
reading the upstream reference remains usable without a local tool installation.

## 9. Implementation and review flow

Before editing: read the current issue, this file and actual components/config; report
the intended screens and source changes. Keep UI-only changes within presentation
contracts. Necessary behavior fixes follow the authorized issue's scope, not an
assumed permission to rewrite controllers/models/validation.

During editing: reuse central tokens/Blade components, preserve authorizations and
data bindings, and use Alpine for small interactions. Keep its existing single
initialization. Do not hardcode business facts or move access logic into the browser.

After editing: run production asset build and relevant existing component/application
regressions; add behavior tests only for meaningful changed behavior. Verify rendered
screens at target viewports/roles/states and keyboard flow. Record base/head, changed
screens, sanitized comparable screenshots, checks actually completed and limitations.
Review shared-component consumers, not only the page that motivated the edit.

Visual Definition of Done: scoped screens follow these tokens/hierarchy; necessary
states and responsive controls work; forms/routes/access contracts remain valid;
relevant checks pass; rendered evidence is available. If browser checks were not
performed, mark them pending. Commit/push/PR/merge and delivery status follow the
shared workflow and are not authorized by finishing this checklist.

Recommended adoption sequence: central theme/font and shared primitives → correct the
representative Feed, Lesson and Creator management patterns → roll the accepted system
through every routed browser screen in functional batches. Representative screens are
calibration points, not a reason to leave the rest of the product on scaffold styling.

## 10. Reference documentation

- Tailwind v4 theme variables: https://tailwindcss.com/docs/theme
- Laravel Blade components: https://laravel.com/docs/12.x/blade
- Font reference: https://fonts.google.com/specimen/Be+Vietnam+Pro
- WCAG text contrast: https://www.w3.org/WAI/WCAG22/Understanding/contrast-minimum.html
- Reduced-motion guidance: https://www.w3.org/WAI/WCAG22/Understanding/animation-from-interactions.html

References guide implementation; they do not certify the current application.

## 11. UI/UX redesign proposal and execution brief (2026-10-15)

Decision boundary: the user confirmed retaining the light/slate/white/teal identity
and Be Vietnam Pro direction while redesigning layout and components deeply across
the complete routed browser experience. The blueprints below establish reusable
patterns; every Member, Creator, account, authentication, checkout and Admin screen
must ultimately use the same coherent system. This separate UI scope does not complete
the outstanding F-19 acceptance.

Confirmed preference update: polished, smooth and moderately lively interaction;
subtle hover, opening menus/outlines and short like/progress feedback. Exclude
cyber/neon styling, glowing techno treatments and futuristic dashboard decoration.
Keep the current identity. Exact motion treatments remain sample-review decisions.

### 11.1 Experience and visual hierarchy

Design for comfortable reading, immediate task discovery and clear community context.
Use balanced density: generous reading space for Members, compact labelled data for
Creators. A polished result comes from consistent alignment, type hierarchy, useful
imagery, restrained depth and complete interaction states.

- Each page answers: which community/workspace am I in, what is this page for, and
  which action is relevant now? Use one page title, a short contextual description
  where useful, and one dominant action per task region.
- Establish four levels: workspace context, page heading/action, section heading,
  content/metadata. Reserve strong teal emphasis for selection and primary actions.
- Align headings, controls and content to the same grid. Use the existing spacing,
  radii and typography contracts; avoid a separate card around every label or number.
- Use real covers, avatars and course media when available. Use an initials/neutral
  fallback when absent. Keep text and controls complete without imagery.
- Use familiar task labels and visible active states. Keep secondary options in
  clearly labelled menus/disclosures; keep the main action visible. Preserve a
  predictable route back, Cancel, validation feedback and persisted progress.

These choices apply [Nielsen's usability heuristics](https://www.nngroup.com/articles/ten-usability-heuristics/),
especially consistency, recognition, system feedback and error recovery. They are
a design rationale, not evidence that this application's usability has been tested.

### 11.2 Navigation and shared shell

Keep the existing 1280px maximum content width and approximately 64px header.

- App header: logo/community switcher on the left, existing community-scoped search
  in the available middle space, notifications/messages/account on the right. Only
  render community tools when an eligible current community exists. On the community
  list/account pages, retain appropriate global navigation without inventing a
  cross-community search or messaging contract.
- Community header: modest cover/identity region with community name and relevant
  context. Preserve access/status meaning. Avoid repeating a large banner and the
  same title at multiple levels on every route; show a compact identity on lessons
  and management screens.
- Member navigation: Community Feed, Classroom, Events, Members, Leaderboard and
  About. Search, notifications and messages remain discoverable labelled utilities;
  avoid duplicating every utility as both a header action and a full-width tab.
- Creator entry: a clearly labelled Manage community link to the existing overview.
  Management navigation groups Overview, content/learning, Members/Invitations,
  Orders/Referrals, Reports and Settings using existing permitted routes. Group
  headings organize links; they do not introduce new resource endpoints.
- Preserve a route inventory mapping every existing permitted destination to its new
  location. Distinguish member directory from Creator membership management, account
  payment history from community orders, and Platform Admin from Creator operations.
- Mobile: compact header with named controls; search may expand into its own row.
  Member tabs use a horizontally scrollable labelled region with visible overflow
  affordance. Creator navigation becomes a keyboard-operable drawer/disclosure.
  All utilities remain reachable. Sticky elements must not obscure focus or content.

### 11.3 Representative screen blueprints

| Screen | Desktop composition | Mobile composition | Primary task |
|---|---|---|---|
| Community Feed | Main column 680–760px, gap 24–32px, optional context column 280–300px | One reading column; useful context moves into a labelled disclosure or after the feed | Find/read a discussion and contribute |
| Lesson | Main video/text area plus 280–300px outline; reading text capped to a comfortable measure | Video/text first, accessible collapsible outline, complete progress action | Read/watch and mark progress |
| Creator overview | Approximately 240px management navigation plus flexible operational content | Management drawer, stacked metrics and locally scrollable tables | Understand current state and reach an operational action |

Community Feed:

1. Compact identity and active community navigation, followed by heading and a
   visible Write a post action. The existing post form can be a labelled disclosure
   or in-page editor; expose it automatically after validation failure, preserve
   old values/category choices and focus the relevant error. No new draft service.
2. One filter toolbar for category, existing sort options and Saved posts. Preserve
   query parameters, pagination and direct-link/back/reload behavior. Long categories
   must remain usable without expanding the page horizontally.
3. Post content: author/avatar and time, readable title/body preview, category/pinned
   metadata, then the existing like/comment/save actions. Secondary moderation/report
   controls remain available only to their permitted users. Separate rows with
   consistent rhythm; do not manufacture engagement counts or replace persisted state.
4. Context column: use existing community description/onboarding data and existing
   navigation links. Extra event/progress widgets require data already supplied to
   the view; omit them when unavailable rather than adding an aggregate/query feature.
   Keep onboarding prominent only while it is incomplete.
5. Category management remains discoverable to Creators through its existing actions
   and a labelled disclosure/link. It should not dominate the Member reading flow.

Lesson:

1. Compact community context and a course/back breadcrumb; clear lesson title.
2. Existing safe video embed at its intended ratio, then readable lesson content.
   Plain text, code/URLs and mixed Vietnamese/English content must wrap appropriately.
3. Course outline shows section grouping, current lesson and actual completed/locked
   states. Retain publication, entitlement and private-access boundaries; do not reveal
   inaccessible outline metadata or infer access from a completed badge.
4. Reuse the existing progress form and show the persisted result. Keep completion
   action in the natural reading flow. Next/previous links may be composed only from
   existing permitted lesson data/routes; do not invent navigation or autosave logic.

Creator overview:

1. Compact identity, management navigation, overview heading and relevant existing
   shortcuts. Keep entry back to the Member workspace visible.
2. A consistent metric row/grid using the currently supplied member/content/learning/
   simulated-receipt data. Use tabular numerals, explicit units and faithful No data
   states. Preserve Test Mode labels and exact denominators; add no decorative trend
   chart, fabricated percentage change or online count.
3. Existing activity/order information follows in a readable data region, with
   aligned numbers, visible statuses and labelled actions. Preserve available filters,
   audit/history and pagination. Actions remain reachable on narrow viewports.
4. Forms group related fields into meaningful sections with concise helper text.
   Destructive actions are visually secondary and have object-specific confirmation.

Community discovery/dashboard cards:

1. A community card is a visual entry point, not a bordered block of database fields.
   Render the existing authorized `communities.cover.show` image when `cover_path` is
   present. Use a polished local branded/educational fallback with initials or an icon
   when absent; keep the image region at a consistent aspect ratio and never leave an
   unexplained blank area.
2. Lead with name, concise description and one clear Open community action. Show role,
   access/status and price/term only when they help the decision. Do not expose the raw
   `/communities/{slug}` path as card content. The whole card may be clickable when the
   nested controls remain valid and keyboard behavior is unambiguous.
3. Search and ownership filters belong in one compact toolbar. Empty results explain
   the filter and offer the relevant next action. Cards reflow from three to two to one
   column without clipped titles or uneven action placement.

Authentication and guest experience:

1. Login, registration, password reset, password confirmation and email verification
   share a purposeful S-cool guest shell with the actual product name, a concise value
   statement and an obvious route back. The login screen includes a meaningful heading,
   supporting copy, registration route when enabled and an orderly forgot-password path.
2. On wide screens, use a restrained split or composed panel with an education/community
   visual and compact form. On narrow screens, prioritize the form without empty desktop
   space. Keep decoration quiet, local and compatible with reduced motion.
3. Preserve CSRF, autocomplete, autofocus, validation, session status, rate limiting and
   password-reset contracts. Add show-password only when it is keyboard accessible and
   does not alter the submitted field. Do not place demo credentials in production UI.

### 11.4 Reuse before implementation

Source inventory verified at `fe634b7` on 2026-10-15. Existing components are building
blocks, not proof of polished appearance or keyboard accessibility. Upgrade them in
place with reviewed props/slots, preserving their callers and behavior contracts.

| Need | Existing source to reuse | Redesign responsibility |
|---|---|---|
| App/guest shell | `layouts/app.blade.php`, `layouts/guest.blade.php`, `layouts/navigation.blade.php` | Central font/theme, responsive header and consistent regions |
| Community context/navigation | `components/community-workspace.blade.php` | Member/Creator composition, compact variants and active states |
| Actions | `primary-button`, `secondary-button`, `danger-button` | Shared sizing, sentence case, semantic colors, focus/disabled behavior; retain explicit button type |
| Form fields | `input-label`, `text-input`, `input-error` | Consistent field grouping, focus and server errors; preserve IDs/names/old values |
| Menus/links | `dropdown`, `dropdown-link`, `nav-link`, `responsive-nav-link` | Named triggers, expanded/current state, keyboard/Escape/outside-close and focus behavior |
| Confirmation/dialog | `modal` | Labelled dialog, focus entry/trap/return and hidden-dialog isolation; verify existing callers |
| Learning forms/status | `classroom-course-fields`, `classroom-lesson-fields`, `classroom-feedback`, `classroom-reorder`, `lesson-progress` | Shared field/status/action styling; preserve completion and reorder payloads |
| Authentication feedback/brand | `auth-session-status`, `application-logo` | Consistent status and identity within guest/app layouts |
| Existing view fragments | `communities/partials/*`, profile partials and existing pagination rendering | Compose these before duplicating their markup/conditions |

Inventory paths above are relative to `resources/views/`. Inspect actual include/tag
usage before editing, including custom caller classes and Alpine/event contracts.
Dropdowns are menus, not generic containers for arbitrary interactive forms.

There is no dedicated UI/icon/chart library installed in the inspected package.json.
Use these Blade components, semantic HTML, existing inline SVG, Tailwind and the one
existing Alpine initialization. Do not install another kit to avoid styling work.
If a repeated need is genuinely missing (for example avatar fallback, status badge,
empty state, select/textarea or page heading), extract the smallest shared component
from two concrete consumers. Define a narrow API and replace those duplicated uses.
Do not build a generic design-system engine, duplicate buttons/modals, introduce a
second styling layer, or use framework-incompatible component snippets.

### 11.5 Implementation sequence and visual acceptance

1. Capture comparable baseline screens at the same role, fixture and viewport. Map
   routes/actions/data and component consumers before presentation edits.
2. Adopt central semantic tokens and licensed self-hosted Be Vietnam Pro as specified
   in sections 3–4. Upgrade shared primitives once, preserving compatibility. Render
   a temporary review specimen for their states; it is not another canonical design file.
3. Repair the representative screens until their hierarchy, navigation and responsive
   behavior are fit to become shared patterns. The initial 2026-10-15 sample is evidence,
   not an accepted visual baseline. Keep observed functional bugs separately attributable.
4. Continue through community discovery/feed/detail/search/directory/about/events,
   classroom/course/lesson, messages/notices, Creator forms/orders/reports/referrals,
   account/access/checkout, authentication/guest and Admin surfaces in scoped batches.
   Do not pause after only three screens; verify each batch's shared consumers and retain
   the distinct role contracts. Final screen-by-screen review gates visual acceptance.

Acceptance targets for the samples and later batches:

- At 375/768/1280px, each scoped screen has clear context, one meaningful h1, visible
  active navigation and an immediately identifiable primary task. Include long titles,
  long names, empty/no-data results, server validation and realistic populated content.
- At 320 CSS pixels and browser zoom, ordinary content reflows without page-wide
  horizontal scrolling or lost actions; genuine tables use their own usable scroll
  region. Check 200% zoom and a 320px-equivalent reflow condition, not just screenshots.
- Primary controls target 40–44px comfort sizing and icon buttons approximately 44px
  hit areas. Measure hit areas/spacing, not the SVG size. WCAG 2.2 AA target-size
  minimum is 24x24 CSS pixels with defined exceptions; comfort targets are stronger
  project choices, not a claim that 44px is the AA minimum.
- Keyboard-only use covers navigation, search, filters, editor, outline, menus and
  dialogs: visible focus, labels, Escape/Cancel and correct focus return. Check open
  and hidden dialogs and reduced motion. Measure rendered contrast per section 3.
- All existing actions, permission checks, submitted fields, retry keys, validation
  and persisted states remain valid. Reflow must not conceal error, payment purpose,
  expiry, Test Mode or access information. No client-only authorization.
- Run production assets and relevant existing regressions after presentation changes.
  Shared-shell/primitive changes require checking affected Member, Creator, guest and
  Platform Admin consumers; final integrated review/CI follows the shared workflow.
- Collect sanitized before/after evidence with viewport, role, fixture/state and exact
  revision. Record pending browser/accessibility checks honestly. A passing build or
  attractive screenshot alone does not prove usability or visual completion.
- Use a short task walkthrough: locate a community discussion and its reply action;
  locate a lesson and completion action; locate Creator order/report controls. Record
  hesitation, wrong turns or inaccessible controls as findings to address. Do not claim
  a successful user study without observing representative users perform the tasks.

Accessibility references: [W3C Reflow](https://www.w3.org/WAI/WCAG22/Understanding/reflow.html)
and [W3C Target Size (Minimum)](https://www.w3.org/WAI/WCAG22/Understanding/target-size-minimum.html).
These checks support the professional design goal; formal conformance is not asserted.

### 11.6 Motion direction confirmed on 2026-10-15

Motion should make the interface responsive and understandable while keeping reading
and learning calm. Apply it to the interaction being performed, with a consistent
visual language. The user selected moderately lively polish, not motion at every
page boundary. Existing section 6 timings are starting targets; review actual samples.

| Interaction | Proposed treatment | Boundary |
|---|---|---|
| Buttons/links | Short color/border feedback and visible focus | Preserve contrast and button size; no moving label |
| Course/community tiles | Slight elevation or border emphasis on hover/focus | No layout shift; touch users retain visible actions |
| Dropdown/dialog | Short opacity/limited translation within section 6 timings | Controls and focus respond immediately; no overlay/focus race |
| Course outline/editor disclosure | Clear open/closed state with restrained transition | Closed controls leave the tab order; validation exposes errors |
| Like/save/progress | Brief emphasis on the actual successful state | Never imply persisted success on a rejected/failed request |
| Feedback message | Clear inline status with a short optional reveal | Errors remain readable; feedback is not animation-only |

Use local CSS/Tailwind transitions and the existing Alpine components first. Prefer
opacity/transform for decorative effects, avoid per-frame layout measurements and
reserve space for changing state. Measure a real disclosure rather than choosing a
fragile height animation that clips long content. Rapid repeated actions must leave
correct final state/focus; motion must not delay navigation or duplicate mutations.

Keep Feed text, lesson body/video and operational tables stable. Do not add scroll
hijacking, cursor trails, continuous floating/pulsing decoration, repeated staggered
page entry, parallax, or celebration effects to routine reading/learning. Any distinct
future landing-page motion is separate scope. Under `prefers-reduced-motion`, remove
decorative movement and retain immediate, readable state/focus/feedback.

The handoff must include short interaction recordings, keyboard/touch review, rapid
toggle/cancel/error cases and reduced-motion behavior for representative interactions
plus a screen coverage matrix for the complete rollout. Screenshots alone cannot
establish motion quality. Owner review gates visual acceptance; test/build PASS cannot
be used as evidence that the design is professional or comfortable to use.

### 11.7 Approved experience-completion adaptations (2026-10-16)

This amendment authorizes the presentation scope mapped to EUR-50/51 in Delivery
Plan section 18. It does not change EUR-49's active scope, backend access policy,
the established identity, or the distinction between planned and accepted UI.
`docs/Need_update_analysis.md` retains the reference observations/disposition;
that file cannot override these design rules or the live issue AC.

**S-cool composition:** learning action -> contextual help -> practical evidence
-> creator feedback -> demonstrated skill. Make this path discoverable with real
data. Community/course imagery and people give identity; do not copy Skool's dark
surfaces/yellow actions or fill whitespace with decorative metrics.

| Surface | Required composition and behavior |
|---|---|
| Contextual shell | About 64px header; S-cool home/brand and community selector, scoped search, notices/messages/avatar. One instance per utility; compact identity/tabs below. Account/list routes have a global variant, not an inferred tenant. |
| My Communities | Image-led responsive cards, concise identity/access information and aligned Open action under one useful filter toolbar. |
| Feed | Readable post column; contextual composer dialog; safe author/avatar/time/category and comment metadata; reactive actions. Optional rail prioritizes the one current learning action/event and incomplete onboarding, then useful community/contribution context. |
| Classroom | Compact Learning Focus over a visual course grid. Consistent 16:9 cover/fallback, true access/progress and clear Open/Continue. Missing images never become blank slots. |
| Lesson | Flexible external media/reading region plus 280–300px outline on wide screens; mobile reading first with accessible outline disclosure. Place completion/continuation, Ask a question and practical evidence in a logical sequence. No forced auto-next or nested lesson-body scrollbar. |
| Proof of Work | Distinct Draft/Waiting/Needs revision/Verified/Rejected feedback, retained learner input and visible revision history. Creator queue/detail makes review actionable; verification never masquerades as self-marked progress. |
| Member profile | Identity/bio/role/join date followed by explained activity and verified skills. Wide-screen columns only when useful; mobile identity first. Community-scoped, no public membership/follower/presence claims. |
| Activity/leaderboard | Label metric, window and provenance; separate engagement from verified work/helpful answers. Accessible text/daily alternative for heatmap, zero/no-data state, stable ties. Avoid an unexplained aggregate score. |
| Settings | Account sidebar (mobile disclosure) plus bounded form content. Profile, Account & security, Notifications, My access, Payment history, Referrals. Keep existing full-name/bio semantics; optional GitHub/portfolio links. Sensitive forms use deliberate submission; existing preference toggles show persistence/failure. |
| About | Editorial orientation/story/video/rules and permitted roadmap; concise creator/context region. No fabricated sales claims, ratings or online numbers. |
| Events/Members | Calendar/list and directory grid respectively, suited to their tasks; retain timezone/search/context and useful empty/long/access states. |
| Messages/Notifications | Clear sender/read/status hierarchy, reachable reply/read actions and safe unavailable destinations. Preserve asynchronous/participant-only meaning. |
| Creator/commerce/Admin | Compact operational navigation, truthful metrics, forms/tables and review actions. Preserve separate roles, audit/payment history, Test Mode and destructive confirmations. |

Reuse the components in section 11.4 plus existing discussion/search Livewire
islands. Extract cover/avatar fallback, state feedback or navigation composition
only from actual consumers. Use static discoverable classes and the existing
icon style; no second UI kit, framework bootstrap or chart/player dependency.

Dialog and async requirements are functional: associated labels, focus entry/
trap/return, Escape/Cancel, recoverable validation and loading/error/success.
Dirty composer input is retained in page memory or requires confirmed discard.
No unsupported media/notification/payout controls. Rapid actions must converge on
the server state; animation cannot imply persistence on failure.

Verify all permitted routed screens, not only the samples: dashboard/discovery;
Feed/direct discussion/Saved/search; Classroom/course/lesson/evidence/review;
Events/Members/profile/leaderboard/About; inbox/thread/notices; account/settings/
access/history/referrals/help; Creator operations; checkout/status; guest/auth;
Platform Admin. Record each route/role/state, viewport and evidence or blocker in
the existing issue checkpoint/PR. Apply section 11.5 responsive/accessibility gates
and section 11.6 motion review. No new standalone coverage/roadmap file is needed.
