# EUR-70 — Reviewed public-guide delivery

Review: 2026-11-02. Accepted application: `8a0982b1f3a374196354421c0887c7a933fb4174`.
Original PR #85: `8addb37db876f3b1a12bbb2fdeec7baa688a0a3c`.

## Scope decision

The submitted wholesale Help replacement is excluded. Keep the accepted Help
view byte-for-byte: current Vietnamese shell, account/preferences/payment-history
links, event/timezone guidance and reporting/moderation guidance must not regress.
No hardcoded loopback login URL belongs in a deployed public user guide.

Guest GET /help on accepted source returns HTTP 200. It provides general help;
this alone does not prove delivery of all EUR-70 task-specific public guides.

## Correct task contracts

| Task | Actual entry / prerequisites | Expected outcome and recovery |
|---|---|---|
| M1 Account/invitation | Current site's register/login; intended recipient; verify real inbox; open intended invitation | Free acceptance grants access; paid invitation may require Test Mode checkout. Expired/wrong-recipient link needs a new invitation |
| M2 Search | /communities plus /communities/{slug}/courses and that community's navigation search | Only permitted results; zero communities in expert mode is expected without access; retry on request failure |
| M3 Learning/practice | Effective membership and any paid-course entitlement; published lesson | Mark complete persists personal progress. Only explicit practical lessons expose private evidence submission/review |
| M4 Paid/expired access | Intended admission or course checkout; My access for eligible renewal | Test Mode only: no real transfer. Pending checkout grants no access. Separately paid course still requires community access |
| C1 Community | Authorized Creator or expert owner; Quản trị website or owned community management | Create/configure community, cover and rules; validate actual input limits; no forced auto-admission |
| C2 Authoring | Community management → courses → selected chapter/lesson | Existing visual editor/Markdown source, authorized images, preview, independent course/lesson publication; drafts remain protected |
| C3 Review | Management → Duyệt bài nộp; submitted private practical evidence | Actual review status/feedback persists; no certificate, automatic personal completion or invented leaderboard reward |
| C4 Members | Community management → Lời mời / Quản lý thành viên | Correct recipient/expiry and permitted suspension/reactivation; history/access retained under existing policy |
| Recovery | Current site origin, intended session, inbox and access state | Retry/renew/contact owner as appropriate. Explain missing/invalid media without exposing private storage paths |

For public presentation use route helpers/current-site links, readable Vietnamese
labels, native anchors/details or fully accessible navigation, no hidden no-JS
content and no removed account links. This is a corrected task map, not a claim
that the new public task pages have been shipped.

## Acceptance status

AC-01: existing public Help access confirmed; new task navigation not delivered.
AC-02/06: inaccurate task claims corrected in this map; public task content still pending.
AC-03: no accepted independent Member+Creator walkthrough.
AC-04: accepted route guards retained; no new public images are introduced.
AC-05: preserve accepted design; no new browser viewport/keyboard/real-zoom certification.
