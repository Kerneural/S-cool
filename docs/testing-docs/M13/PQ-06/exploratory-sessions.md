# EUR-72 (PQ-06) — Exploratory Testing Sessions Report

**Date**: 2026-11-01  
**Milestone**: M13 — Product quality & content completion  
**Authority**: Linear Issue [EUR-72 (PQ-06)](https://linear.app/eurusdevsec/issue/EUR-72/pq-06-execute-integrated-chrome-regression-and-deliver-the-owner-uat)  
**Evaluated Candidate**: `1ce19a23905c0f0b398b24f7e17b9ba3767ce11b` (origin/main)  
**Primary Test Host**: `http://127.0.0.1:8080` (Docker Compose stack)

---

## Charter 1: Visitor Discovery & Unauthenticated Entry (AC-01, AC-05)
* **Timebox**: 30 minutes
* **Target Personas**: Unauthenticated Visitor, Guest Learner.
* **Tested Surfaces**: Landing Page (`/`), Help Center (`/help`), Login (`/login`), Registration (`/register`).
* **Observations**:
  * Landing page hero illustration loads crisp SVG constellation with `#0F766E` token palette.
  * Controllable logo marquee pauses on manual click; button toggles `:aria-pressed` reliably.
  * In `/help`, role-based task tabs allow seamless browsing between Learner, Creator Studio, and Troubleshooting without requiring sign-in.
  * Attempting to navigate directly to `/dashboard` or `/communities/{slug}` gracefully redirects to `/login`.
* **Findings**: None. Responsive layout verified at 320px, 375px, 768px, and 1280px without horizontal scroll.

---

## Charter 2: Member Classroom Progression & Search Experience (AC-02, AC-03)
* **Timebox**: 45 minutes
* **Target Persona**: Active Member (`member@scool.local`).
* **Tested Surfaces**: Communities Dashboard, Classroom View, Proof of Work Submission.
* **Observations**:
  * Communities live search responds with debounced query updates. Query `dokcer` brings `Docker & Containerization` to rank 1.
  * In Classroom, lesson navigation cleanly renders Markdown headings, fenced code blocks, and structured notes.
  * Completed lessons retain their green checkmark indicator and progress bar updates accurately upon refresh.
  * Proof of Work submission card allows pasting repository link; submissions immediately transition to `SUBMITTED`.
* **Findings**: None. Tenant isolation strictly prevents viewing courses outside member communities.

---

## Charter 3: Creator Studio & Operations (AC-02, AC-04)
* **Timebox**: 30 minutes
* **Target Persona**: Verified Creator (`creator@scool.local`).
* **Tested Surfaces**: Community Settings, Classroom Authoring, Creator Review Mode.
* **Observations**:
  * Creator overview dashboard lists owned communities, active member counts, and recent discussions.
  * In Classroom, switching course status to `DRAFT` hides it from members; switching to `PUBLISHED` exposes it immediately.
  * Creator Review Mode card allows inspecting student submissions and recording approve/reject notes.
* **Findings**: None. All actions require authenticated creator ownership.

---

## Charter 4: SePay Test Mode & Security Boundary Verification (AC-04)
* **Timebox**: 30 minutes
* **Target Persona**: Invited Paid Member (`devops-demo@scool.local`).
* **Tested Surfaces**: Paid Community Checkout, Webhook Simulator.
* **Observations**:
  * Paid community indicates whole-VND price (e.g. 100,000 VND).
  * Checkout view renders simulated transfer details and test QR code with clear test-mode warning.
  * Simulated webhook IPN payload successfully triggers activation. Replay payloads are idempotent.
  * Anti-sniff guards on video routes block direct URL browser visits and enforce signed referers.
* **Findings**: None. No real payment credentials or personal data exposed.
