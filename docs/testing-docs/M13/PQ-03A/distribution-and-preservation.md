# EUR-68 (PQ-03A) — Content Distribution & Non-Destructive Preservation Proof

**Date**: 2026-11-01  
**Milestone**: M13 — Product quality & content completion  
**Authority**: Linear Issue [EUR-68 (PQ-03A)](https://linear.app/eurusdevsec/issue/EUR-68/pq-03a-complete-the-technical-demo-learning-catalog-with-reproducible)  
**Parent Acceptance Issue**: [EUR-65](https://linear.app/eurusdevsec/issue/EUR-65/m13-acceptance-complete-existing-journeys-curated-learning-content-and)  
**Evaluated Candidate**: `1ce19a23905c0f0b398b24f7e17b9ba3767ce11b` (origin/main)

---

## 1. Managed Catalog Seeding & Rollout Architecture (AC-05)

Content for Group A technical communities is deterministically seeded and versioned via `LearningCommunitySeeder`:
1. **Target Communities**:
   - `networking-learning`
   - `cloud-master`
   - `docker-engineering`
   - `ai-engineer`
2. **Safe Rollout Strategy**:
   - Uses transactional queries with slug matching (`where('slug', $spec['slug'])`).
   - If an existing community has been edited or is owned by another creator, the seeder detects the state and aborts or preserves existing data rather than silently resetting records.
   - Course sections and lessons are mapped by `order` and `title`, preserving foreign key references in `lesson_progresses` and `lesson_proof_of_works`.

---

## 2. Media Distribution & Permission Isolation (AC-05, AC-06)

* **Disk Storage**: Covers are loaded from `database/seeders/assets/community-covers/` into the private media disk (`storage/app/community-media/{id}/cover.png`).
* **Service User Access**: Permissions are explicitly set to `0755` for directories and `0644` for files.
* **Testing Sandbox Guard**: In `testing` environment on MySQL `scool_test`, `LearningCommunitySeeder` skips writing to local media roots to prevent fixture leakage into development media volumes.

---

## 3. Preservation & Idempotency Evidence

* **Rerun Safety**: Repeated execution of `LearningCommunitySeeder` preserves existing user accounts, passwords, earned progress status, and proof of work evidence submissions.
* **Verification Command**:
  ```bash
  docker compose -p scool run --rm --no-deps app php scripts/verify-runtime.php seed
  ```
  *Result*: `[PASS] Five personas; all attributes unchanged on rerun.`
* **Database Isolation**: All automated tests run on guarded MySQL database `scool_test`. The application database `scool` remains untouched during test suites.

