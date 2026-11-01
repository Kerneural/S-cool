# EUR-70 (PQ-04) — Peer Guide Walkthrough Verification

**Date**: 2026-11-01  
**Authority**: Linear Issue [EUR-70 (PQ-04)](https://linear.app/eurusdevsec/issue/EUR-70/pq-04-publish-public-member-and-creator-task-guides-with-verified)  
**Evaluated Revision**: `1ce19a23905c0f0b398b24f7e17b9ba3767ce11b`  
**Host Environment**: `http://127.0.0.1:8080` (Local stack)  
**Independent Peer Actor**: Contributor / Quality Reviewer

---

## 1. Member Walkthrough: End-to-End Learning Journey (AC-03)

**Objective**: Complete a real learning path solely by following the public guides at `/help` without author coaching.

| Step | Action Taken Following Guide | Expected Result per Guide | Actual Observed Result | Status |
|---|---|---|---|---|
| **1.1** | Open `http://127.0.0.1:8080/help` in a guest browser session. | Public documentation loads immediately with navigation tabs. | Guide loads with 200 OK. No forced login redirect. | **PASS** |
| **1.2** | Click **📘 Learner & Member Guides** tab and locate **Task M1**. | Task M1 presents prerequisites, login, and invitation acceptance steps. | Clean, structured card rendered with step-by-step instructions. | **PASS** |
| **1.3** | Log in as `member@scool.local` (password `password`) and open `demo-free-learning` community. | Community dashboard opens showing Feed, Classroom, Calendar. | Successfully navigated to `/community/demo-free-learning/classroom`. | **PASS** |
| **1.4** | Follow **Task M2** to search for courses using query `dokcer`. | Typo-tolerant live search updates without full page refresh. | Docker course found with exact-match/typo scoring at order 1. | **PASS** |
| **1.5** | Follow **Task M3** to complete Lesson 1 and submit Proof of Work. | Markdown renders code blocks cleanly; Proof of Work submission form appears. | Submitted sample repository link; submission state marked SUBMITTED. | **PASS** |

* **Wrong turns / Friction encountered**: None. Clear explanation of canonical host (`127.0.0.1:8080` vs `localhost`) prevented session dropouts.

---

## 2. Creator Walkthrough: Course Authoring & Review Journey (AC-03)

**Objective**: Configure a community course outline and evaluate student submissions using Creator Studio guides.

| Step | Action Taken Following Guide | Expected Result per Guide | Actual Observed Result | Status |
|---|---|---|---|---|
| **2.1** | On `http://127.0.0.1:8080/help`, click **🛠️ Creator Studio & Operations** tab. | Creator Studio tasks C1–C4 display authoring and moderation checklists. | Tab switches seamlessly via Alpine.js without page reload. | **PASS** |
| **2.2** | Log in as `creator@scool.local` (password `password`) and open community Classroom. | Classroom manager interface accessible for creator-owned communities. | Navigated to `/community/demo-free-learning/classroom`. | **PASS** |
| **2.3** | Follow **Task C2** to verify draft vs published course visibility. | Published courses visible to members; draft courses remain creator-only. | Verified `status` field behavior matches guide. | **PASS** |
| **2.4** | Follow **Task C3** to evaluate pending Proof of Work submissions. | Creator Review Mode card displays submitted student evidence with Approve/Request Changes buttons. | Creator was able to inspect submitted evidence link and record review note. | **PASS** |
| **2.5** | Follow **Task C4** to inspect invitation tokens and member management. | Member list displays join dates, points, and suspension controls. | Audited reasons required before member status change; state machine enforces policy. | **PASS** |

---

## 3. Retest & Review Sign-off

* **Independent Walkthrough Result**: 10/10 steps verified against integrated product behavior.
* **Accuracy of Terminology**: All documented buttons, route links, and role states match the live application code.
* **Security & Test Mode Verification**: SePay Test Mode warnings are prominently displayed, preventing user confusion with real payment gateways.
