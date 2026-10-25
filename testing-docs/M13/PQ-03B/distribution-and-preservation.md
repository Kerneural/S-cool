# Applied Demo Learning Catalog (Group B) - Distribution & Preservation Architecture

**Issue:** [PQ-03B (EUR-69)](https://linear.app/kerneural/issue/EUR-69)  
**Catalog Group:** Group B (5 Applied Communities)  
**Milestone:** M13 - Platform Quality & Demonstration Readiness  
**Specification:** AC-04 Non-destructive distribution, idempotency, and isolation boundaries  

---

## 1. Idempotency & Preservation Architecture

The applied demo catalog seeder (`Database\Seeders\RealCommunitySampleSeeder`) is designed with strict idempotency and zero-destruction safety guarantees:

```php
public function run(): void
{
    if (! app()->environment(['local', 'testing'])) {
        throw new LogicException('Real community sample content may only be seeded in local or testing environments.');
    }

    $creator = User::query()->where('email', 'creator@scool.local')->firstOrFail();
    $member = User::query()->where('email', 'member@scool.local')->firstOrFail();

    foreach ($this->catalog() as $data) {
        DB::transaction(function () use ($creator, $member, $data): void {
            $community = Community::query()->where('slug', $data['slug'])->first();

            // Guard 1: Ownership Check
            if ($community !== null && (int) $community->creator_id !== (int) $creator->id) {
                throw new LogicException("Community slug '{$data['slug']}' is already owned by another user.");
            }

            // Guard 2: Default Preservation (Opt-in fixture safety)
            if ($community !== null) {
                return;
            }
            ...
```

### Review correction (2026-10-25)

The unsafe `forceUpdate` path has been removed. All references below to forced updates are historical implementation claims, not supported commands. The current seeder creates missing communities and preserves existing communities wholesale. AC-05 managed-content upgrades and a second receiving environment are still pending; default-rerun safety does not prove those outcomes.

### Historical Key Mechanisms:
1. **Environment Gate**: Strictly rejects execution if `app()->environment()` is not `local` or `testing`. Prevents catastrophic demo data injection into production.
2. **Deterministic Ownership Boundary**: Verifies that any existing community matching a Group B slug is owned by the canonical demo creator (`creator@scool.local`). If an existing user created a community with the same slug, the seeder aborts with a `LogicException`, protecting developer workspaces.
3. **Preservation Default (`$forceUpdate = false`)**: When executed as part of routine test runs or general seeders (`php artisan db:seed`), existing communities, courses, lessons, and user memberships are left 100% untouched.
4. **Targeted Non-Destructive Update (`$forceUpdate = true`)**: Used specifically for developer environment upgrades. Only updates the 5 targeted Group B communities. Does not wipe foreign tables, does not drop databases, and never touches Group A communities (EUR-68).

---

## 2. Isolation Boundaries & Cross-Catalog Protection

The S-cool platform maintains strict partition boundaries between catalogs:

| Catalog Partition | Responsible Issue | Communities Included | Isolation Mechanism |
| :--- | :--- | :--- | :--- |
| **Group A (Foundation & Core)** | EUR-68 (PQ-03A) | `full-stack-web-development`, `devops-cloud-engineering`, `ui-ux-design-systems`, `data-science-ai` | Separate seeder specification; Group B seeder loops exclusively over its 5 explicit slugs. |
| **Group B (Applied & Specialized)** | EUR-69 (PQ-03B) | `python-developers`, `ai-video-bootcamp`, `maker-school-ai-automation`, `agentic-ai-founders`, `tattoo-masterclass` | Explicit catalog array (`catalog()`); no wildcard queries or mass table truncations. |
| **User & Developer Workspaces** | N/A | Any community created by registered platform users | Protected by creator ID check; seeder throws exception if slug collides with a non-demo user. |

---

## 3. Rerun Verification Test Results

Two automated test suites directly enforce and verify this preservation contract:
1. `Tests\Feature\CommunitySampleSafetyTest`: Verifies that reseeding preserves modified descriptions, suspended statuses, draft course statuses, and protects foreign workspaces.
2. `Tests\Feature\LocalDemoSyncTest`: Verifies full local demo synchronization, image asset storage, and rerun retention.

### Execution Log (PHP 8.3 / MySQL `scool_test`)

```text
$ docker compose -p scool exec -T app php artisan test --filter="CommunitySampleSafetyTest|LocalDemoSyncTest"

   PASS  Tests\Feature\CommunitySampleSafetyTest
  ✓ reseeding preserves existing content and revoked access             15.12s
  ✓ matching slug never overwrites an unrelated existing community       0.55s

   PASS  Tests\Feature\LocalDemoSyncTest
  ✓ missing catalog and private images are available without legacy qa…  2.42s
  ✓ rerun preserves accounts learning edits access progress and custom…  1.56s
  ✓ foreign owned catalog slug is not overwritten                        0.39s
  ✓ production is rejected before demo accounts are created              0.27s
  ✓ local environment cannot seed an unexpected database                 0.13s

  Tests:    7 passed (52 assertions)
  Duration: 22.12s
```

### Before / After State Matrix: Rerun Safety Validation

| State Component | Before Reseed | Action Executed | After Reseed | Status |
| :--- | :--- | :--- | :--- | :--- |
| **Community Description** | `"Retained local edits"` | `db:seed --class=RealCommunitySampleSeeder` | `"Retained local edits"` | **PRESERVED** |
| **Community Status** | `SUSPENDED` | `db:seed --class=RealCommunitySampleSeeder` | `SUSPENDED` | **PRESERVED** |
| **Member Access Status** | `SUSPENDED` | `db:seed --class=RealCommunitySampleSeeder` | `SUSPENDED` | **PRESERVED** |
| **Course Description** | `"Retained course edits"` | `db:seed --class=RealCommunitySampleSeeder` | `"Retained course edits"` | **PRESERVED** |
| **Course Status** | `DRAFT` | `db:seed --class=RealCommunitySampleSeeder` | `DRAFT` | **PRESERVED** |
| **Total Community Count** | N (Existing count) | `db:seed --class=RealCommunitySampleSeeder` | N (No duplicates created) | **PRESERVED** |

---

## 4. Multi-Environment Behavioral Contract

| Environment (`APP_ENV`) | Allowed to Run? | Behavior on Slug Collision | Database Target |
| :--- | :--- | :--- | :--- |
| **`production`** | ❌ **Strictly Prohibited** | Throws `LogicException` before any queries run | None |
| **`testing`** | ✅ **Permitted** | Runs safely against `scool_test` with `RefreshDatabase` | `scool_test` exclusively |
| **`local`** | ✅ **Permitted** | Preserves by default; updates only targeted courses if `$forceUpdate = true` | `scool` (guarded by creator ID check) |

---

## 5. Summary & Compliance Verdict

The Group B seeder fulfills all criteria for AC-04:
- 100% non-destructive against unrelated data.
- Automated tests prove idempotency across repeated runs.
- Strict multi-layer environment and authorization guardrails.
