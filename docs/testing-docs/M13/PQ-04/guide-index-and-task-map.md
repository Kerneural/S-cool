# EUR-70 (PQ-04) — Public Guide Index and Task Map

**Date**: 2026-11-01  
**Authority**: Linear Issue [EUR-70 (PQ-04)](https://linear.app/eurusdevsec/issue/EUR-70/pq-04-publish-public-member-and-creator-task-guides-with-verified)  
**Milestone**: M13 — Product quality & content completion  
**Branch**: `hadangkhoasss/eur-70-pq-04-publish-public-member-and-creator-task-guides-with`  
**Base Commit**: `1ce19a23905c0f0b398b24f7e17b9ba3767ce11b` (origin/main)  
**Canonical Public Entry**: `http://127.0.0.1:8080/help` (`route('help.index')`)

---

## 1. Information Architecture & Public Navigation (AC-01, AC-05)

The S-cool documentation hub at `/help` is engineered as a clean, public-facing portal accessible to visitors without mandatory login or authentication wall. It reuses the core S-cool design tokens (slate-900, teal-500, amber-500, rounded-2xl/3xl, Alpine.js client-side tabs) and supports responsive viewports down to 320px with real 200% zoom compatibility.

### Public Navigation Routes

| Route Name | Public URL Path | Auth Required? | Purpose & Target Persona |
|---|---|---|---|
| `help.index` | `http://127.0.0.1:8080/help` | **No** (Public / Guest accessible) | Main Documentation & Step-by-Step Task Guides Hub |
| `welcome` | `http://127.0.0.1:8080/` | **No** (Public) | Landing Page with symbolic constellation & product preview |
| `communities.index` | `http://127.0.0.1:8080/dashboard` | Yes (redirects to `/login` if unauthenticated) | Communities Directory & Dashboard |
| `login` | `http://127.0.0.1:8080/login` | **No** (Public) | Secure User Authentication |

---

## 2. Topic & Task Mapping Matrix (AC-02, AC-06)

| Task ID | Topic Title | Target Role | Prerequisites | Documented Steps | Expected UI & Persisted Result | Recovery & Troubleshooting |
|---|---|---|---|---|---|---|
| **M1** | Joining a Community & Account Verification | Learner / Member | Valid invitation link or public free community | 1. Open invitation URL<br>2. Sign in or register<br>3. Verify email via Mailpit signed link<br>4. Click Accept Invitation | Member row created in `community_memberships` with status `ACTIVE`; redirects to Community Feed | If invitation token invalid/expired, request new invitation from Creator |
| **M2** | Finding Permitted Content via Live Search | Member / Visitor | Session on Dashboard or Classroom | 1. Navigate to `/dashboard` or Classroom<br>2. Type query (e.g. `dokcer`)<br>3. Inspect debounced live cards or press Enter | URL updates with `?q=dokcer`; relevant cards filtered without full page reload; exact-match priority | If empty state appears, check spelling or reset active category filters |
| **M3** | Classroom Progression & Proof of Work | Active Member | Active membership in community | 1. Open course outline in Classroom<br>2. Read lesson syllabus<br>3. Locate Submit Proof of Work card<br>4. Paste repository or terminal log | `lesson_progresses` and `lesson_proof_of_works` rows saved; status `SUBMITTED` queued for review | If submission rejected, inspect creator feedback and resubmit revised evidence |
| **M4** | Paid Access & Simulated Checkout | Invited Member | Paid community or standalone paid course | 1. Click 'Continue to checkout'<br>2. Inspect whole-VND price & SePay reference code<br>3. Automated IPN runner delivers test webhook | Order transitions from `pending` to `succeeded`; access granted without real money transfer | **DO NOT send real money**; test mode simulated QR code only |
| **C1** | Community Setup & Guidelines | Community Creator | Verified creator account | 1. Click Create Community<br>2. Enter name, slug, description, whole-VND price<br>3. Upload cover image<br>4. Configure conduct rules | Community row inserted with `ACTIVE` status and default post categories | If slug taken, choose a unique alphanumeric slug |
| **C2** | Course Structuring & Publishing Flow | Community Creator | Community Creator permissions | 1. Enter Classroom > Create Course<br>2. Add Course Sections (modules)<br>3. Add lessons with Loom/video URL and Markdown<br>4. Switch status to PUBLISHED | Course visible to active members; draft items remain strictly hidden | If video doesn't embed, ensure Loom/YouTube URL format is valid |
| **C3** | Creator Review Mode & Learner Feedback | Community Creator | Submissions awaiting evaluation | 1. Open lesson in Creator Review Mode<br>2. Review student GitHub/log evidence<br>3. Choose Approve or Request Changes | Student awarded lesson credit; community points & leaderboard updated | If evidence link broken, leave comment requesting student resubmission |
| **C4** | Member Lifecycle & Invitation Management | Community Creator | Community Creator permissions | 1. Issue 7-day single-use invitation tokens<br>2. Monitor member list and contribution points<br>3. Suspend disruptive accounts with required reason | Suspended account immediately barred from community feed and classroom access | Reinstate member when appropriate; points and history safely restored |

---

## 3. Linked Manual Cases in M13 Master Test Suite

* `M1` &rarr; `TC-M13-002`, `TC-M13-003`
* `M2` &rarr; `TC-M13-006`, `TC-M13-007`, `TC-M13-008`
* `M3` &rarr; `TC-M13-003`, `TC-M13-011`
* `M4` &rarr; `TC-M13-004`, `TC-M13-005`
* `C1` &rarr; `TC-M13-012`
* `C2` &rarr; `TC-M13-003`
* `C3` &rarr; `TC-M13-011`
* `C4` &rarr; `TC-M13-012`
