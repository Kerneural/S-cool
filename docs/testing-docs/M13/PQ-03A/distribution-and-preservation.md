# EUR-68 — Independent technical catalog review

Review: 2026-11-02. Accepted application: `8a0982b1f3a374196354421c0887c7a933fb4174`.
Original PR #87: `fa694f4ee6f9f78b7825134b845edfee5e0af129`.
The previous all-PASS curriculum report is superseded by the source-backed findings below.

## Actual receiving path

LocalDemoSyncSeeder calls LearningCommunitySeeder with preserveExisting=true.
It fills a missing community, but skips an existing same-owner community, retaining
its edited content and state. It does not version-upgrade Group A lesson text.
The generic LearningCommunitySeeder default is repair mode: it force-fills content,
publication, community and membership state. It must not be presented as safe routine sync.

scripts/verify-runtime.php seed requires an empty users table and invokes DatabaseSeeder
twice. It checks the five bootstrap personas, not Group A content/progress preservation.
Do not run this command on the accepted development database as an audit probe.

## Actual evidence

On the unchanged accepted implementation, the targeted Help/LocalDemoSync/
AppliedCatalogUpgrade run passed 14 tests / 115 assertions, exit 0, 13.77s.
LocalDemoSyncTest covers edited content, custom covers, roles/passwords, suspended
state and progress retention in isolated MySQL scool_test with fake media.
It does not prove receipt on another person's machine or every audit/history field.
App service-user www-data reads all eight Group A community/course covers.

## Missing delivery gates

No content patch or managed Group A upgrade is included in PR #87.
Before any future content update: define old/desired version identity, preserve
unknown/personal changes as conflicts, retain lesson IDs and all access/progress/
entitlement/history, then verify a missing-record setup and an existing receiving
environment. Never force a generic seed/reset to complete the report.
Second teammate receipt and actual Chrome lesson/progress/private-practice retest
remain unverified. The current app/data have not been modified by this review.
