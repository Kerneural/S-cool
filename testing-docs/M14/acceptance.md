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
