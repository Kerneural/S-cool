# EUR-66 — Reviewed baseline and catalog

Review: 2026-11-02. Accepted application: `8a0982b1f3a374196354421c0887c7a933fb4174`.
Original PR #84 head: `9a1a7fbd1a684e6dafdb879b108b72ba7adadacc`.
This correction supersedes unsupported PASS claims; it is not final release acceptance.

## Reproduced checks on accepted source

| Check | Result | Boundary |
|---|---|---|
| AccountNotificationPreferencesAndHelpTest, LocalDemoSyncTest, AppliedCatalogUpgradeTest | 14 tests / 115 assertions PASS, exit 0, 13.77s | Guarded MySQL scool_test; fake mail/storage |
| ScopedLiveSearchFeatureTest, SearchPrivacyRegressionTest, LivewireCommunitySearchTest | 14 tests / 100 assertions PASS, exit 0, 8.03s | Feature/component tests; not Chrome |
| Guest /help, canonical mounts/ports/migrations, service-user media | As recorded in baseline | Read-only local checks; no host deployment |

Commands: php artisan test --filter='<suite names above separated by |>'
--compact --fail-on-skipped --fail-on-empty-test-suite, in the existing isolated
QA container. tests/TestCase.php guards MySQL scool_test before RefreshDatabase.
Relevant source/test parity is checked against accepted source. These are targeted
runs, never a full-suite claim.

## Peer reproduction setup

Use existing authorized synthetic Creator and Member sessions with effective
membership in the same community. Do not publish passwords or create more fixtures.
Search /communities, its /courses catalog and scoped navigation; compare docker
with dokcer. Expert accounts without access must not see unrelated communities.
Open the Creator management course tree and inspect the same published lesson
as Member. Only practical lessons have a submission workflow. Completion and
verified practical work are separate; no certificate is awarded by review.
The actual browser walkthrough remains pending; automated reproduction is separate.

## Corrections and remaining coverage

Singular /community/.../classroom routes were invalid. Guests cannot open the
dashboard. Navigation search is community-scoped. Current landing has no Pause
button. Old screenshots and test filenames do not establish a new PASS.
Font warnings require actual asset checks, not a claim that fonts load.
The manual matrix retains failed-access, persistence, network, keyboard, measured
viewport, real zoom, motion, provider and receiver cases without pre-checking them.
