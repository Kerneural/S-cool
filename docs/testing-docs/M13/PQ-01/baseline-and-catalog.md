# EUR-66 (PQ-01) — Baseline and Catalog Freeze Report

**Date**: 2026-11-01  
**Authority**: Linear Issue [EUR-66 (PQ-01)](https://linear.app/eurusdevsec/issue/EUR-66/pq-01-verify-integrated-baseline-freeze-the-full-demo-catalog-and)  
**Milestone**: M13 — Product quality & content completion  
**Branch**: `hadangkhoasss/eur-66-pq-01-verify-integrated-baseline-freeze-the-full-demo`  
**Base Revision**: `1ce19a23905c0f0b398b24f7e17b9ba3767ce11b` (Merged origin/main)  
**Host & Runtime**: Windows 11, Docker Desktop (WSL2), Docker Engine 28.4.0, Compose v2.39.4, PHP 8.2.35 (FPM), Nginx (Alpine), MySQL 8.0, Node.js v24.16.0 / npm 11.13.0.  
**Canonical Local Origin**: `http://127.0.0.1:8080` (IPv4 Compose binding).

---

## 1. Runtime & Stack Verification (AC-01)

### 1.1 Service Inventory & Health State
Inspected via `docker compose -p scool ps`:

| Service | Container Name | Image | Status | Published Ports | Mount / Working Directory | Health Check Status |
|---|---|---|---|---|---|---|
| `app` | `scool_app` | `scool-app` (local build) | Up (healthy) | 9000/tcp | Host bind `./:/var/www/html` | Healthy (`nc -z 127.0.0.1 9000`) |
| `nginx` | `scool_nginx` | `nginx:alpine` | Up (healthy) | `127.0.0.1:8080->80/tcp` | Host bind `./:/var/www/html` | Healthy (HTTP reverse proxy to app:9000) |
| `mysql` | `scool_mysql` | `mysql:8.0` | Up (healthy) | `127.0.0.1:3306->3306/tcp` | Named volume `scool_scool_mysql_data` | Healthy (`mysqladmin ping`) |
| `mailpit` | `scool_mailpit` | `axllent/mailpit:latest` | Up (healthy) | `127.0.0.1:1025->1025/tcp`, `127.0.0.1:8025->8025/tcp` | Ephemeral | Healthy (HTTP probe on :8025) |
| `queue` | `scool_queue` | `scool-queue` (local build) | Up (healthy) | 9000/tcp | Host bind `./:/var/www/html` | Healthy (Worker process check in /proc/1/cmdline) |

* Observation: Canonical web entrypoint `http://127.0.0.1:8080/up` returns HTTP 200 OK.
* Mailpit web UI accessible at `http://127.0.0.1:8025`.
* MySQL running with databases `scool` (application) and `scool_test` (isolated test runner database).

### 1.2 Checkout & Git Integrity
* Working branch: `hadangkhoasss/eur-66-pq-01-verify-integrated-baseline-freeze-the-full-demo` (branched from `origin/main` at `1ce19a2`).
* Git working tree: Clean prior to adding PQ-01 testing documentation artifacts.
* Preserved work: Existing `hadangkhoasss/eur-82-*` commits remain intact on their respective feature branch.

---

## 2. Frozen Full Catalog Inventory (AC-02 & AC-03)

The total inventory contains **16 Communities**, **69 Courses**, and **916 Lessons**.
All communities are owned by single creator `creator@scool.local` and are in `ACTIVE` status.

### 2.1 Complete Catalog Matrix

| # | Slug | Name | Access Mode | Price (VND) | Courses | Lessons | Content Allocation | Provenance / Seeder |
|---|---|---|---|---|---|---|---|---|
| 1 | `demo-free-learning` | Demo Free Learning | FREE | null | 1 | 2 | **Baseline Core** | `DemoContentSeeder` |
| 2 | `demo-paid-learning` | Demo Paid Learning | PAID | 100,000 | 1 | 2 | **Baseline Core** | `DemoContentSeeder` |
| 3 | `networking-learning` | Computer Networking Fundamentals | FREE | null | 1 | 6 | **Group A: Technical** | `LearningCommunitySeeder` |
| 4 | `cloud-master` | Cloud Architecture & DevOps | FREE | null | 1 | 8 | **Group A: Technical** | `LearningCommunitySeeder` |
| 5 | `docker-engineering` | Docker & Containerization | FREE | null | 1 | 8 | **Group A: Technical** | `LearningCommunitySeeder` |
| 6 | `ai-engineer` | AI Engineer | FREE | null | 1 | 8 | **Group A: Technical** | `LearningCommunitySeeder` |
| 7 | `python-developers` | Python Developers | FREE | null | 1 | 4 | **Group B: Applied** | `RealCommunitySampleSeeder` / `skool-real-communities.json` |
| 8 | `ai-video-bootcamp` | AI Video Bootcamp | PAID | 225,000 | 1 | 4 | **Group B: Applied** | `RealCommunitySampleSeeder` |
| 9 | `maker-school-ai-automation` | Maker School: AI Automation | PAID | 4,600,000 | 1 | 4 | **Group B: Applied** | `RealCommunitySampleSeeder` |
| 10 | `agentic-ai-founders` | Agentic AI Founders | PAID | 2,400,000 | 1 | 4 | **Group B: Applied** | `RealCommunitySampleSeeder` |
| 11 | `tattoo-masterclass` | Tattoo Artist Business & Practice | PAID | 980,000 | 1 | 4 | **Group B: Applied** | `RealCommunitySampleSeeder` |
| 12 | `ai-automation-society` | AI Automation Society | FREE | null | 17 | 264 | **Group C: Curated Skool Reference** | `RealFreeCommunitySeeder` / `skool-real-communities.json` |
| 13 | `learn-ai` | AI Automation Agency Hub | FREE | null | 12 | 150 | **Group C: Curated Skool Reference** | `RealFreeCommunitySeeder` / `skool-real-communities.json` |
| 14 | `max-business-school` | Max Business School™ | FREE | null | 2 | 106 | **Group C: Curated Skool Reference** | `RealFreeCommunitySeeder` / `skool-real-communities.json` |
| 15 | `the-ai-advantage` | The AI Advantage | FREE | null | 9 | 93 | **Group C: Curated Skool Reference** | `RealFreeCommunitySeeder` / `skool-real-communities.json` |
| 16 | `cliefnotes` | Clief Notes | FREE | null | 18 | 249 | **Group C: Curated Skool Reference** | `RealFreeCommunitySeeder` / `skool-real-communities.json` |

### 2.2 Content Group Allocation & Disjoint Union (AC-03)
* **Baseline Core (2 communities, 2 courses, 4 lessons)**:
  * Golden-flow verification for free admission and SePay Test Mode paid activation.
* **Group A: Technical Curated (4 communities, 4 courses, 30 lessons)**:
  * `networking-learning`, `cloud-master`, `docker-engineering`, `ai-engineer`.
  * Comprehensive multi-section syllabus, structured lab assignments, local cover storage.
* **Group B: Applied Demonstrations (5 communities, 5 courses, 20 lessons)**:
  * `python-developers`, `ai-video-bootcamp`, `maker-school-ai-automation`, `agentic-ai-founders`, `tattoo-masterclass`.
  * Outlines locked in `applied-catalog-baselines.json`; verified through `AppliedCatalogUpgradeSeeder`.
* **Group C: Realistic Large-Scale References (5 communities, 58 courses, 862 lessons)**:
  * Realistic dataset crawled from public Skool communities to stress-test pagination, search ranking, and tenant boundaries without risking proprietary creator IP.
* **Exclusions**:
  * Synthetic automated test tenants generated by `RefreshDatabase` in `tests/` are excluded from the demo catalog.
  * No test fixture or personal developer sandbox data is exposed in the production/demo catalog.
  * No development data or volume was deleted during inventory.

---

## 3. Preservation & Idempotency Safeguards

1. **Non-destructive Seeding**:
   `LocalDemoSyncSeeder` uses `firstOrCreate` / `firstOrNew` matching on slug, preserving user passwords, membership states, and customized user inputs.
2. **Deterministic Upgrades**:
   `AppliedCatalogUpgradeSeeder` will not overwrite content if modifications are detected (marks `CONFLICT` for human review instead of overwriting).
3. **Media Safety**:
   `SkoolMediaSyncSeeder` generates deterministically hashed local paths in `storage/app/community-media/` and sets `0644`/`0755` permissions for Nginx/FPM access without exposing public storage directory listings.
