# EUR-72 (PQ-06) — Canonical and Receiver Host Verification Proof

**Date**: 2026-11-01  
**Milestone**: M13 — Product quality & content completion  
**Authority**: Linear Issue [EUR-72 (PQ-06)](https://linear.app/eurusdevsec/issue/EUR-72/pq-06-execute-integrated-chrome-regression-and-deliver-the-owner-uat)  
**Evaluated Candidate**: `1ce19a23905c0f0b398b24f7e17b9ba3767ce11b` (origin/main)

---

## 1. Primary Canonical Host Environment (AC-06)

* **Host System**: Windows 11 Pro, Docker Desktop (WSL2 engine).
* **Tools**: Git 2.47+, Node.js v24.16.0 / npm 11.13.0, Docker Engine 28.4.0, Compose v2.39.4.
* **Canonical Local Origin**: `http://127.0.0.1:8080` (IPv4 loopback port binding).
* **Stack State**: 5 Services active and Healthy:
  - `scool_app` (PHP-FPM 8.2.35): Healthy.
  - `scool_nginx` (Nginx Alpine): Healthy, binding `127.0.0.1:8080:80`.
  - `scool_mysql` (MySQL 8.0): Healthy, binding `127.0.0.1:3306:3306`.
  - `scool_mailpit` (Mailpit): Healthy, binding `127.0.0.1:8025:8025` and `127.0.0.1:1025:1025`.
  - `scool_queue` (Laravel Worker daemon): Healthy.

### Health Probes & Endpoint Responses
* `curl.exe -I http://127.0.0.1:8080/up`: `HTTP/1.1 200 OK`.
* `curl.exe -I http://127.0.0.1:8080/help`: `HTTP/1.1 200 OK`.
* MySQL accessibility: `scool` (application database) and `scool_test` (isolated test runner) fully verified.

---

## 2. Receiving Teammate Independent Verification

* **Clean Checkout Execution**: Tested via `bash scripts/verify-fresh-setup.sh --mode verify`.
* **Test Suite Verification**: Complete test suite passed 74 tests / 515 assertions with zero failures across MySQL `scool_test`.
* **Catalog Synchronization**: `LocalDemoSyncSeeder` cleanly syncs without corrupting existing records or deleting user database volumes.
* **Service User Storage Permissions**: Media paths under `storage/app/community-media/` have `0755` directory and `0644` file permissions, enabling Nginx and FPM to serve authorized images without permission errors.

