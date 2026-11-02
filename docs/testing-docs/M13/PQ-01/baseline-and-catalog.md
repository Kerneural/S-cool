# EUR-66 — Reviewed baseline and catalog

Review: 2026-11-02. Accepted application: `8a0982b1f3a374196354421c0887c7a933fb4174`.
Original PR #84 head: `9a1a7fbd1a684e6dafdb879b108b72ba7adadacc`.
This correction supersedes unsupported PASS claims; it is not final release acceptance.

## Observed baseline

- HEAD equals origin/main and the accepted checkout is clean. The canonical app
  mounts that checkout, never the isolated PR review directories.
- Five services healthy. Original loopback ports retained: web 8080, MySQL 3306,
  Mailpit SMTP 1025 and UI 8025. /up and guest /help return HTTP 200.
- Migration status lists no pending migration. No schema/data/media changes made.
- Curated runtime: **14 communities, 67 courses, 912 lessons**. The two old
  Demo Free/Paid Learning fixtures are absent and excluded, not counted as catalog.
- Service user www-data can read **18/18** covers for the nine A+B communities.
  This does not prove browser playback, every private route or media rights.

## Owner-approved allocation, 2026-11-02

Content acceptance: **A = 4 communities / 4 courses / 30 lessons; B = 5/5/20**.
A and B are disjoint and cover exactly the nine accepted content communities.
The owner retained five imported reference communities (**5/58/862**) outside
content acceptance. Keep their reference/regression use; do not infer rights
clearance or curriculum quality from source availability.

| Slug | Allocation | Courses | Lessons | Observed state |
|---|---|---:|---:|---|
| agentic-ai-founders | B | 1 | 4 | ACTIVE / PAID |
| ai-automation-society | REFERENCE_ONLY | 17 | 264 | ACTIVE / FREE |
| ai-engineer | A | 1 | 8 | ACTIVE / FREE |
| ai-video-bootcamp | B | 1 | 4 | ACTIVE / PAID |
| cliefnotes | REFERENCE_ONLY | 18 | 249 | ACTIVE / FREE |
| cloud-master | A | 1 | 8 | ACTIVE / FREE |
| docker-engineering | A | 1 | 8 | ACTIVE / FREE |
| learn-ai | REFERENCE_ONLY | 12 | 150 | ACTIVE / FREE |
| maker-school-ai-automation | B | 1 | 4 | ACTIVE / PAID |
| max-business-school | REFERENCE_ONLY | 2 | 106 | ACTIVE / FREE |
| networking-learning | A | 1 | 6 | ACTIVE / FREE |
| python-developers | B | 1 | 4 | ACTIVE / FREE |
| tattoo-masterclass | B | 1 | 4 | ACTIVE / PAID |
| the-ai-advantage | REFERENCE_ONLY | 9 | 93 | ACTIVE / FREE |

[in-scope-catalog.csv](in-scope-catalog.csv) enumerates all 50 in-scope lessons,
ordered sections, course publication/access state and body hashes. Source mapping:
A: LearningCommunitySeeder; B: RealCommunitySampleSeeder and
database/data/applied-catalog-baselines.json; reference: RealFreeCommunitySeeder
and database/seeders/data/skool-real-communities.json. Raw probes stay private.

## Reproduction and limits

The [case matrix](manual-cases.csv) defines checks, not execution results.
[Coverage](coverage-map.md) separates reproduced tests from pending browser checks.
Only LocalDemoSyncSeeder's preserve path is routine sync. The generic learning
seeder's default repair mode may overwrite state. Never invoke it to force parity.
scripts/verify-runtime.php seed is a bootstrap mutation, not a read-only probe.
Independent receiver proof, human walkthrough, real browser 200% zoom, media rights
and owner UAT remain unverified. No old fixture, account or volume was deleted.
