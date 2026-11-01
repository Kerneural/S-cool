# EUR-66 (PQ-01) — Test Coverage & Peer Reproduction Map

**Date**: 2026-11-01  
**Milestone**: M13 — Product quality & content completion  
**Authority**: Linear Issue [EUR-66 (PQ-01)](https://linear.app/eurusdevsec/issue/EUR-66/pq-01-verify-integrated-baseline-freeze-the-full-demo-catalog-and)  
**Evaluated Revision**: `1ce19a23905c0f0b398b24f7e17b9ba3767ce11b`  

---

## 1. Automated vs. Manual Test Coverage Mapping (AC-05)

| Functional Area | Automated Test Suites (PHP / JS) | Assertions / Cases | Manual Case ID(s) | Status & Evidence |
|---|---|---|---|---|
| **Health & Stack Readiness** | `verify-fresh-setup.sh --mode verify`, `verify-runtime.php config` | Integrated checks | `TC-M13-001` | **PASS**: All 5 Docker services healthy, HTTP `/up` returns 200. |
| **Catalog Integrity & Upgrades** | `tests/Feature/AppliedCatalogUpgradeTest.php`, `LocalDemoSyncTest.php` | 8 tests, 75 assertions | `TC-M13-002`, `TC-M13-004` | **PASS**: Idempotent upgrade, `CURRENT` on matched hashes, `CONFLICT` on edits. |
| **Search (3 Surfaces)** | `ScopedLiveSearchFeatureTest.php`, `SearchNormalizationServiceTest.php`, `SearchMeasurementTest.php` | 11 tests, 89 assertions | `TC-M13-006`, `TC-M13-007`, `TC-M13-008` | **PASS**: Bounded SQL candidates (50/20), typo tolerance, exact-first ranking, evidence in `testing-docs/M13/PQ-02/`. |
| **Proof of Work & Classroom** | `ProofOfWorkUiFollowupTest.php`, `RealCommunityVerificationTest.php`, `LessonMarkdownTest.php` | 13 tests, 74 assertions | `TC-M13-003`, `TC-M13-011` | **PASS**: Safe markdown rendering, evidence submission, creator review mode. |
| **Commerce & SePay Webhook** | `tests/Feature/SePayPaymentTest.php`, `ReferralCommissionTest.php` | 22 tests, 128 assertions | `TC-M13-005` | **PASS**: Test mode IPN activation, tampered payload rejection, idempotent replay. |
| **Landing & Accessibility** | `tests/Feature/LandingArtworkMarqueeTest.php`, `tests/js/landing-motion.test.mjs` | 30 tests, 68 assertions | `TC-M13-009`, `TC-M13-010` | **PASS**: Manual pause button, `prefers-reduced-motion` flex wrapping, evidence in `docs/testing-docs/M13/PQ-05/`. |
| **Platform Admin** | Platform admin policies, audit retention regressions | Feature tests | `TC-M13-012` | **PASS**: Tenant action auditing, community suspension/activation. |

---

## 2. Peer Setup & Reproduction Instructions (AC-06)

To enable independent verification by another team member or reviewer without mutating test data:

### 2.1 Reproducing Search Typo-Tolerance & Sequence Invalidation (TC-M13-006)
1. Open Google Chrome at `http://127.0.0.1:8080/dashboard` and log in as `member@scool.local` (password: `password`).
2. On the **All Communities** view, focus the search input.
3. Type `dokcer`:
   * **Expected observation**: Debounced live filter runs without full page reload.
   * `Docker & Containerization` appears as the top matching card.
   * URL query parameter updates to `?q=dokcer`.
4. Quickly clear input and type `python`:
   * **Expected observation**: Previous in-flight request is aborted (sequence guard); results instantly switch to `Python Developers`.

### 2.2 Reproducing Creator-to-Member Course & Proof of Work Flow (TC-M13-003 & TC-M13-011)
1. In an incognito window, log in as `creator@scool.local` (password: `password`).
2. Navigate to `http://127.0.0.1:8080/community/demo-free-learning/classroom`.
3. In the regular window as `member@scool.local`, navigate to the same classroom, open Lesson 1, and submit a proof-of-work link/text.
4. Refresh the creator view:
   * **Expected observation**: The submission appears in the Creator Review card with options to review and approve.
   * Member's completion status updates deterministically upon approval.

---

## 3. Discovered Findings & Unverified Boundaries

1. **Vite Manifest Font Resolution Warning**:
   * Asset build emits non-fatal warnings for `/fonts/be-vietnam-pro/*` during `npm run build` because fonts are resolved at runtime via public path. Application styling and glyphs render correctly.
2. **Physical Mobile Devices vs. Emulation**:
   * Responsive verification completed via Chrome DevTools emulation at 320px, 375px, 768px, and 1280px with 200% zoom. Real physical iOS/Android device testing remains deferred per M13 contract.
3. **SePay Provider Rehearsal**:
   * Payment webhook logic is verified via local automated HTTP simulated IPNs; real bank transfer integration is gated to SePay Test Mode.
