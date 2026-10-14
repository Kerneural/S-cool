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
The upstream README advertises Laravel/Blade and HTML/Tailwind guidance, design
search/recommendations and UX checklists. Installation/execution has not been
performed for this document. It is a development aid, not an application dependency.

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

Recommended adoption sequence: central theme/font and shared primitives → Feed sample
→ Lesson sample → Creator management sample → approved remaining screens. Confirm
the representative samples before propagating appearance across the application.

## 10. Reference documentation

- Tailwind v4 theme variables: https://tailwindcss.com/docs/theme
- Laravel Blade components: https://laravel.com/docs/12.x/blade
- Font reference: https://fonts.google.com/specimen/Be+Vietnam+Pro
- WCAG text contrast: https://www.w3.org/WAI/WCAG22/Understanding/contrast-minimum.html
- Reduced-motion guidance: https://www.w3.org/WAI/WCAG22/Understanding/animation-from-interactions.html

References guide implementation; they do not certify the current application.
