# EUR-72 (PQ-06) — Defect and Retest Index

**Date**: 2026-11-01  
**Milestone**: M13 — Product quality & content completion  
**Authority**: Linear Issue [EUR-72 (PQ-06)](https://linear.app/eurusdevsec/issue/EUR-72/pq-06-execute-integrated-chrome-regression-and-deliver-the-owner-uat)  
**Evaluated Candidate**: `1ce19a23905c0f0b398b24f7e17b9ba3767ce11b` (origin/main)

---

## 1. Defect Log & Retest Summary

| Defect ID | Severity | Category | Description | Root Cause | Fixing Commit / PR | Retest Status |
|---|---|---|---|---|---|---|
| **DEF-M13-01** | Low (Cosmetic) | Build / Assets | Non-fatal font resolution warning during Vite build for `be-vietnam-pro/*.woff2` | Fonts referenced via absolute web root path `/fonts/` rather than relative bundler imports | Verified benign: Fonts served via Nginx public static root; text glyphs render correctly in browser | **RESOLVED / ACCEPTED** |
| **DEF-M13-02** | Minor | Documentation | In `/help` view, breadcrumb initially used non-existent route `welcome` | Missing named route alias `welcome` in `routes/web.php` for root path `/` | Corrected to `url('/')` in EUR-70 branch | **RESOLVED** |

---

## 2. Regression Retest Verification

* **Search Typing & Sequence Invalidation**: Retested rapid keystrokes (`python` &rarr; `dokcer`) on Communities list and global typeahead. In-flight requests abort cleanly; no stale results rendered.
* **Test Mode Payment IPN**: Tested tampered currency, mismatched amounts, and replayed tokens. All invalid payloads safely rejected with 401/422 status codes.
* **Responsive Reflow & Reduced Motion**: Retested at 320px, 375px, 768px, and 1280px. Zero horizontal scroll overflow detected across tested views.
