# EUR-69 — Versioned applied catalog distribution

## Candidate and supported operation

2026-10-25 follow-up on `eur-67-69-71-acceptance`, base `b24451869a6aefbbd14c0275cbe71cfee0a50dae` plus the uncommitted diff. This supersedes the previous forced-update instructions. No `forceUpdate` option is supported.

`RealCommunitySampleSeeder` creates missing communities only. `AppliedCatalogUpgradeSeeder` upgrades the five Group B courses only when their complete content outline matches the checked-in historical baseline or the current catalog. `LocalDemoSyncSeeder` invokes both, then fills missing free catalog records and bundled media.

Preview before an operator-approved existing local DB upgrade:

```bash
docker compose -p scool exec -T --user www-data app php artisan demo:upgrade-applied
# After reviewing the preview and taking a DB backup:
docker compose -p scool exec -T --user www-data app php artisan demo:upgrade-applied --apply
```

`CURRENT` means content matches. `WOULD_UPGRADE` names a recognized baseline. `CONFLICT` means an edited/foreign-owned/renamed outline is retained for manual review. A conflict is a report, not a successful rollout of that course. Do not reset the DB to remove it.

## Preservation contract

- Allowed only on MySQL `scool_test` in testing, or MySQL `scool` with local origin `http://127.0.0.1:8080`; production is rejected.
- Each course upgrade is transactional with row locks. Unknown outlines are retained wholesale.
- Existing semantic lesson IDs are reused, including legacy lessons moved between modules, retaining progress/evidence references. New lessons are added without deleting existing lessons.
- Publication, membership/access state, account credentials/roles, prices, custom covers, payments and learning progress are not overwritten.
- Known community orientation text is updated only when it still matches a historical value; custom text remains.
- Historical content baselines contain public demo specifications only. No user database dump is committed.

## Actual local execution

User approved a backup and scoped local sync. The DB was backed up privately before writes. The first run completed catalog updates but failed creating a private image directory owned by root. Only the private media tree ownership was corrected to `www-data`; rerunning as `www-data` completed. The second run's before/after snapshot retained existing user, membership, progress, entitlement, payment, audit and evidence rows. Local totals after sync: 16 communities, 67 courses and 912 lessons, including pre-existing custom communities.

`AppliedCatalogUpgradeTest` plus `LocalDemoSyncTest`: 8 tests / 75 assertions passed on guarded `scool_test` during this follow-up. Cases cover legacy upgrade/semantic ID retention, preview, idempotency, changed-content conflicts, foreign ownership, production rejection and existing access/history preservation.

## Remaining acceptance

A second independent receiving machine has not executed this version. Another container against the same database is not that evidence. A teammate must record revision, preview, sync result, rerun/conflicts and retained edited/access/progress records before EUR-69 AC-05 can be closed. CloudPanel and Azure were not modified by this local sync.
