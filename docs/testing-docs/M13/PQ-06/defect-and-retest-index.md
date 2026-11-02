# EUR-72 — Reviewed QA evidence disposition

Review: 2026-11-02. Accepted app: `8a0982b1f3a374196354421c0887c7a933fb4174`.
Submitted PR #86: `a698ed75415fba766d716d5be8cdb84cf7342397`.
The submitted 1ce19a2 report is historical and does not certify this release.

## Confirmed evidence defects

| Finding | Evidence | Correction / retest |
|---|---|---|
| Baseline counts include two absent old fixtures | LocalDemoSyncTest and read-only current catalog: 14 communities/67 courses/912 lessons | PR84 corrected inventory; 9 A+B/50 accepted-content lessons |
| Case roles/routes mismatch current policy | Guests cannot open dashboard; singular /community/.../classroom invalid | Corrected planned matrix in PR84; old executed-case claims not accepted |
| Removed landing Pause control and obsolete motion expectations | Accepted landing differs from submitted scenario | Cases 009/010 SUPERSEDED; rerun current accepted motion policy |
| Test-file references and screenshots treated as executed end-to-end PASS | No matching per-case run metadata; screenshot dimensions alone lack CSS/zoom proof | Mark remaining execution NOT_VERIFIED; preserve historical images |
| Independent receiver and full-suite claims unsupported | No receiver receipt; 74/515 cannot establish current full suite | Correct canonical proof; record only actual bounded tests |

Font resolution warnings are not accepted as resolved solely from text visibility.
An actual asset/network check is still required before asserting fonts are served.
No application defect was fixed in this evidence-only correction.
