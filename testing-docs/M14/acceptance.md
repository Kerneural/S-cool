# M14 acceptance evidence

Date: 2026-11-01 (Asia/Ho_Chi_Minh).
Scope: EUR-74 through EUR-83 and EUR-85. Host release remains EUR-84.

## Candidate and environment

Baseline: PR #83 at f3e87559f53c67e558b3470af18cee50820fde42, already containing
the identity, access, management, locale, discussion and LMS PR histories.
The reviewed DRM note retains PR #75 history through the integration merge.
The publication SHA and required CI/review are recorded on PR #83.

Tests run sequentially on guarded MySQL scool_test only. A Linux filesystem copy
avoids incomplete PHPUnit discovery on the Windows bind mount. The connection is
checked before testing; TestCase also guards it before RefreshDatabase.
No development or host database was reset.

Browser acceptance used synthetic fixtures in that isolated database, expert mode,
a verified configured owner, and http://127.0.0.1:8080. The temporary proxy was
removed and the original local service restored. This is candidate acceptance,
not CloudPanel/Azure deployment evidence.

## Final automated gates

- Full PHP: 736 passed, 9,321 assertions, zero skipped/failures, 301.74s.
  Command: php artisan test --fail-on-skipped --fail-on-empty-test-suite
  --log-junit /tmp/m14-final-junit.xml --no-ansi.
- Discovery/parity: 106 PHP test class files; 378 application/catalog/resource/test
  files matched the frozen Linux runtime by SHA-256.
- JavaScript: node --test tests/js/*.test.mjs, 72 passed.
- Production: npm run build; app-DL_tfS_H.css and app-BF5cHejR.js.
- Pint: 333 files PASS. PHPStan: no errors. git diff --check PASS.
- Shared contract/privacy: bash scripts/verify-agent-contract.sh --require-tracked PASS.

The historical 154-test bind-mount run is superseded and is not complete-suite proof.
Interrupted runs are excluded. Required CI will independently verify the published
commit before merge; local PASS is not reported as CI PASS.

## AC-to-evidence map

| Issues | Evidence |
| --- | --- |
| EUR-74 | ExpertOwnerModeTest and existing authorization regressions; browser Owner Admin access and learner hub denial; read-only owner check passed with all active QA communities mapped correctly. |
| EUR-75 | ExpertLearnerEntryTest plus switcher/discovery/private-access/acquisition regressions; fresh welcome, invitation acceptance, single-community Feed, scoped list and deliberate unjoined About preview. |
| EUR-76 | WebsiteManagementHubTest; two-community hub at 375/768/1280, keyboard navigation and no page-wide overflow; learner denied. |
| EUR-77/78 | VietnameseLocaleTest and VietnameseSweepTest: clean HTTP bootstrap/default vi, explicit en compatibility, module/duplicate-key checks, rendered auth/invitation mail and account/commerce/Admin checks; source inventory below. |
| EUR-79/80 | CourseManagementWorkspaceTest, LessonAuthoringToolbarTest, CRUD/ancestry regressions and 3 preview-race JS regressions; browser native tree Enter, validation retaining selected lesson/body, successful save and Markdown preview. |
| EUR-81 | LearnerCourseFlowTest, LessonProgressTest and entitlement/private-media regressions; browser persisted completion, next incomplete lesson and unentitled paid-course explanation. |
| EUR-82 | CommunityDiscussionPointsTest, leaderboard/ledger and post/comment regressions; browser post/reply confirmations, actual zero-point level and post-like rules. |
| EUR-83 | Complete test inventory/full suite, JS/build/static gates and these captures. Required CI and independent approval remain separate gates. |
| EUR-85 | Reviewed EUR-85-DRM.md: masking is not DRM, authorized extraction/capture limits, primary sources and deferred implementation/cost/handover questions. |

## Browser receipts

- Owner opened the hub and tree/editor, previewed Markdown, submitted an invalid
  video URL, retained lesson 2/body, corrected input and saved successfully.
- Owner sent one synthetic invitation through local Mailpit. Its subject/body
  were Vietnamese and the recipient accepted it through the real UI.
- Fresh verified learner first saw welcome without existing community names.
  After acceptance, dashboard opened its Feed; filter=all listed only the joined
  community. Explicit /about preview did not grant membership.
- Learner completed an included lesson and reloaded: 1/3 (33%) remained. The
  discussion link carried course/lesson context; post and reply appeared with
  Vietnamese confirmations.
- Learner management/owner-hub access was denied. The unentitled paid course
  displayed 200,000 VND and explicit SePay Test Mode copy.
- Be Vietnam Pro reported 12 loaded faces and passed the Vietnamese glyph check.
  Measured 375/768/1280 layouts had no page-wide horizontal overflow.

## Source and message inventory

Reviewed landing/About, navigation/search, account/auth, commerce, Admin, creator
management/referrals/moderation, classroom/reader, community feed/members/events/
leaderboard, generated onboarding/notifications, validation and auth/invitation mail.
Checks covered literal translation lookups, raw visible labels, static accessible
names, module conflicts and rendered spot checks. Corrected raw statuses, malformed
lookup characters, keyboard hints, onboarding and duplicate scoring explanations.

Legitimate exceptions: brand/provider/institution names; protocol/format identifiers
and keycaps (HTTPS, UTC, VND, Markdown, Enter, Esc); authored names, lesson bodies,
categories and links. Existing Vietnamese authoring strings remain Vietnamese;
explicit en testing establishes auth-bootstrap compatibility, not a separate
complete English LMS localization.

## Captures

All accounts/content are synthetic. Full-page mobile images exclude the scrollbar,
so a 375px CSS viewport can produce a 360px-wide image.

- [Owner editor before, 375](current-evidence/owner-editor-before-375.jpg)
- [Owner editor after, 375](current-evidence/owner-editor-after-375.jpg)
- [Owner hub, 375](current-evidence/owner-hub-375.jpg)
- [Owner hub, 1280](current-evidence/owner-hub-1280.jpg)
- [Fresh welcome, 1280](current-evidence/fresh-welcome-1280.jpg)
- [Persisted completion, 1280](current-evidence/learner-completed-1280.jpg)
- [Learner reader, 375](current-evidence/learner-reader-375.jpg)
- [Paid course locked, 375](current-evidence/learner-paid-locked-375.jpg)

The editor comparison uses the PR #83 baseline/final management template with the
same selected lesson and saved QA content; it does not claim the entire old release
was deployed. The final template was restored and matched the worktree SHA-256.

## Limits

No host deployment, real payment, licensed DRM implementation or external real-mail
delivery. Native 200% zoom, no-JS and throttled-network visual checks were not repeated;
no accessibility-compliance claim. Permission/ancestry, progress-error/retry, stale
access and idempotency also have automated coverage, rather than every case being
reenacted manually.


## Approved-UI preservation follow-up — 2026-11-02

Base: published main af3540bd5e6f3955ef5a20ddc4e9ba6ab67952c6.
Candidate: the tested implementation on eur-83-preserve-approved-ui; the PR
commit identifies the published revision. Publication was authorized on 2026-11-02
and remains subject to required CI/review before merge.
Compare the complete M14 surface with contract-freeze baseline 1ce19a2.

This follow-up supersedes the earlier assumption that the integrated release
preserved all accepted branding/landing presentation. PR #74 deliberately changed
the logo, favicon, brand typography and static footer; its inclusion in the
integrated ancestry was not authority to expand the frozen M14 design scope.
The original 2026-11-01 captures above retain that historical appearance.

Review inputs: all 13 EUR-73–85 issue descriptions, 31 Linear comments, original
PR #72–83 descriptions and 14 discussion entries. Handoffs confirm that the hub
links in both navigation/account menu, tree/editor/preview, Start/Continue flow
and five real point levels are intentional and must remain. No other broad
unapproved view/layout rewrite was confirmed in the reviewed M14 diff.

Corrections:

- Restore the original S-chain SVG/favicon and brand typography. Restore the
  static outlined footer and its CSS; keep localization and existing landing
  carousel/marquee/re-entry behavior. Logo/favicon/CSS match baseline bytes.
- Remove only the 37 new dark utility tokens from lesson fields and one from the
  authoring placeholder, preserving every light class and preview/cancellation guard.
- Complete Vietnamese labels, accessible names, operation flashes, payment/purpose
  display labels and user-facing domain validation failures. Resolve escaped
  symbol labels as actual arrows/multiplication signs, rather than HTML text.
- Localize month/day names using the existing date objects/timezones. Preserve
  authored titles/body/names/reasons, stored enums, payment snapshots and timestamps.
- All nine backend file deltas normalize exactly to their original code after
  removing literal translation wrappers: no authorization, transaction or mutation
  branch changed. Schema, seeders, demo media, font assets and dependencies are unchanged.

Verification:

- Two regression cases fail against the original populated cover/flash views,
  then pass on the correction. Six added HTTP/error-path cases cover populated
  moderation/settings, flash keys, immutable payment statuses, duplicate invitation,
  stale reorder/ambiguous time and unavailable notification.
- Focused locale suites: 24 passed / 3,360 assertions on the final text corrections.
- Final full PHP suite: 742 passed / 9,530 assertions, no failures/skips,
  305.60 seconds; guarded sequential MySQL scool_test, fail-on-skipped and
  fail-on-empty enabled. Final runtime/worktree parity: 378 files.
- JavaScript: 72 passed. Production assets app-yB9fOYEg.css / app-qfwVCcvI.js.
  Pint 333 files passed; PHPStan no errors; shared routing/privacy and diff checks passed.
- Optional local graph refresh completed, but the MCP provider retained a stale
  snapshot and compare transport closed. No complete graph/taint coverage is claimed;
  conclusions rely on direct diff/source, focused independent review and runtime proof.

Browser acceptance uses the same canonical origin, a temporary QA proxy and synthetic
scool_test fixtures. Original runtime/source/data are restored after the run.

- Restored logo and static footer observed; footer has no reveal ancestor.
- Mobile menu works; both marquee transforms change while animations remain running.
- Journey/illustration labels and cover/moderation actions are Vietnamese.
- Owner hub, course tree keyboard disclosure and safe Markdown preview work.
- Learner completion remains 1/3 (33%) after reload; no creator forms appear.
- Fresh learner sees no community names and direct website hub access returns 403.
- Checked 375/768/1280 reflow; no page-wide overflow in the inspected states.
- The browser click helper was inconsistent once on mobile logout; keyboard Enter
  completed logout. No application change was inferred from that isolated tool result.
- Native 200% zoom, no-JS, network throttling, real external mail, payments,
  host rollout and licensed DRM remain outside this follow-up proof.

Follow-up captures (synthetic data only):

- [Restored mobile landing](current-evidence/preserved-landing-375.jpg)
- [Static footer](current-evidence/preserved-footer-1280.jpg)
- [Populated moderation](current-evidence/preserved-moderation-1280.jpg)
- [Guest login](current-evidence/preserved-login-375.jpg)
- [Owner hub mobile](current-evidence/preserved-owner-hub-375.jpg)
- [Owner hub desktop](current-evidence/preserved-owner-hub-1280.jpg)
- [Mobile authoring preview](current-evidence/preserved-owner-editor-375.jpg)
- [Learner persisted completion](current-evidence/preserved-learner-reader-375.jpg)
- [Fresh learner welcome](current-evidence/preserved-fresh-welcome-1280.jpg)

These local corrections do not change GitHub/Linear publication state or claim
a CloudPanel/Azure release. EUR-84 remains separate.


## Owner usability follow-up — 2026-11-02 (unpublished)

Owner-approved seven-group follow-up on eur-83-owner-usability, based on merged
main 3b37c67. The EUR-83 working checkpoint records the bounded authoring/media,
calendar, message polling and presentation changes.

- Full guarded MySQL scool_test: 746 tests, 9,584 assertions, 391.451s, PASS.
- After final view/security refinements: 48 focused tests, 404 assertions, PASS.
- JS: 77 tests PASS, including visual-editor Markdown round-trip and child-button
  instance lookup; build, Pint, PHPStan and shared contract checks PASS.
- Real local browser: editor upload and safe preview, unchanged stored lesson, expanded
  Save/heading, desktop/375px, restored SVG fill, Vietnamese pagination, 365-day calendar,
  real About cover with visible Classroom action, draft retention during polling, and
  a 321.6px pending discussion panel. No lesson mutation or direct message was submitted.
- Two generated unreferenced QA uploads were removed by exact content hash.
- Independent two-session browser delivery, real zoom and host rollout are unverified.
  No publication or formal Linear completion is implied.


## Management usability follow-up — 2026-11-02 (unpublished)

Authority: owner approved the investigated five-part management UX proposal. Branch
eur-83-owner-usability remains based on 3b37c67 with the earlier usability changes retained.

- Management context stays full width across settings, invitations, operations and course
  authoring. Courses opens creator actions directly; learner catalog/permissions remain intact.
- Settings groups retain all field names and separate cover/settings endpoints, validation
  input, actual save feedback and dirty/discard guards. Cover selection is labelled in Vietnamese.
- Invitation history states are translated. Post/comment secondary actions use native disclosures,
  44px targets, contextual deletion labels, preserved confirmation and Escape/focus restoration.
- Full guarded scool_test: 749 tests, 9,679 assertions, PASS in 337.730s. Final targeted verification
  after the last course-navigation and responsive refinements: 131 tests, 4,464 assertions,
  PASS in 14.552s. JavaScript: 81/81 PASS; build, touched-test Pint, diff and agent-contract PASS.
  No skipped tests. QA source/checkout hash parity: 840 files.
- Chrome at canonical 127.0.0.1:8080: 1280px management navigation stays one row across all eight
  operational pages. Stable scrollbar allocation yields the same 1159px nav width on short/long
  pages. Settings and invitations reflow at 375/768px; settings also at 320px. Native section
  anchors, dirty/revert, loaded cover, form ownership and course-editor entry were observed.
- Private local captures: owner-usability/management-settings-desktop.png and
  owner-usability/management-settings-mobile.png in this chat's visualization directory.
  Desktop final capture includes a fully loaded cover; no raw tokens or emails.
- No user fixture save/upload/delete/revoke, invitation send, seed/reset or host release.
  Real 200% zoom and browser no-JS are unverified in this follow-up. Temporary QA is isolated
  from the live scool database. Publication/review/CI and owner visual acceptance remain separate.


## Social and website-management completion — 2026-11-02

Approved follow-up on eur-83-owner-usability based on 3b37c67. Includes the earlier two
usability batches; publication is now authorized for a review PR, with no host release.

- Resource-list website hub, search/status filtering, retained secondary tools and direct
  managed course catalog. Feed prioritizes writing, categories wrap and sorting is separate.
- Shared lazy visual post/comment editor, private media, safe links/provider video, emoji,
  one-level replies and unique persisted single-choice votes with explicit close.
- Built-in category names are localized at display boundaries without rewriting slugs,
  custom names or existing post text. Compact replies follow the inspected Skool reference:
  inline icon controls, explicit formatting disclosure, bounded growing text and thumbnails.
- Full guarded scool_test run: 758 tests / 9,899 assertions PASS (410.816s), before final
  refinements. Final affected regression run: 67 tests / 543 assertions PASS (17.095s);
  JS 85/85, PHPStan, production build and Blade compilation PASS. Final icon-toolbar
  refinement was browser-checked after that targeted PHP run.
- Canonical Chrome 127.0.0.1:8080: actual image post/comment, preserved bold formatting,
  immediate successful reply, cleared draft, one-level reply, vote/change/close, author
  profile navigation, hub search and course-management destination observed.
  Feed/hub reflow at 375/768/1280px without page-wide overflow. Comment composer measured
  90.4px desktop / 134.4px mobile, with bounded image thumbnails and working mobile emoji.
- Private captures in this chat's visualization directory, owner-usability:
  social-comments-desktop.png, social-comments-mobile.png, social-categories-mobile.png,
  social-feed-desktop.png and social-hub-desktop.png. Only synthetic local acceptance data.
- No new two-session member social/browser, real zoom, no-JS or host proof is claimed.
  External URL metadata success was not browser-proven. Synthetic local post #39 and
  comments #52–54 were used; browser cleanup was interrupted at its confirmation and
  removal is unverified. Test fixtures, private captures, raw logs and DB backup stay local.
