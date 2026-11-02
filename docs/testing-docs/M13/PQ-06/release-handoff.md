# EUR-72 — Reviewed QA evidence disposition

Review: 2026-11-02. Accepted app: `8a0982b1f3a374196354421c0887c7a933fb4174`.
Submitted PR #86: `a698ed75415fba766d716d5be8cdb84cf7342397`.
The submitted 1ce19a2 report is historical and does not certify this release.

## Verdict: NOT READY for final QA sign-off

This PR is an evidence correction, not proof that the M13 campaign is complete.
Current product functionality is not declared broken by these documentation findings.
The owner-approved five imported reference communities/862 lessons are excluded
from curriculum acceptance; nine A+B communities/fifty lessons remain in scope.

[Executed cases](executed-cases.csv) now preserve historical claims without certifying
them. [Defects](defect-and-retest-index.md), [bounded review](exploratory-sessions.md)
and [canonical/receiver proof](canonical-and-receiver-proof.md) state actual limits.
The [image manifest](chrome-evidence/manifest.csv) records pixels/hashes, not verified
CSS viewport, Chrome version, DPR or real 200% zoom.

Required before Done:
1. Resolve outstanding baseline, technical-content and public-guide AC.
2. Execute the current matrix on the identified candidate, including permission,
   persistence, private media, actual worker/mail/provider boundaries as applicable.
3. Supply measured Chrome/keyboard/real-zoom/motion evidence and independent receiver receipt.
4. Tie required tests/build/static/security/CI/review to the accepted candidate.
5. Hand over actual completed evidence; owner UAT remains the owner's decision.

PR workflow green badges currently represent Runner Preflight only where present.
They are not full application CI. No owner sign-off or exception is invented.
