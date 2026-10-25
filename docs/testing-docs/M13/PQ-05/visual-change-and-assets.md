# Visual Change & Assets Provenance: PQ-05 (EUR-71)

## 1. Scope & Region Overview

| Landing Region | State | Details |
|---|---|---|
| Navigation & Header | Preserved | Standard brand logo, guest/auth navigation, help link. |
| Hero Headline & Engine Switcher | Preserved & Enhanced | Kept typography, set pill switcher (`Engine v2.0`: Community, Classroom, Verification), CTA buttons. |
| Hero Artwork & Visuals (AC-01, AC-02) | Changed / Added | Original symbolic knowledge constellation SVG (`hero-symbolic-mesh.svg`) layered with genuine, uncropped product preview cards linked to the active set. |
| Logo Strip / Marquee (AC-03, AC-04) | Changed / Enhanced | Added accessible manual Pause/Resume button (`:aria-pressed`, `:aria-label`), Alpine state tracking, pointer hover/leave persistence, and static wrapping reduced-motion fallback. |
| Lifecycle Section & Rest of Landing | Preserved | Preserved lifecycle tabs, code snippets, customer scenarios, and footer. |

---

## 2. Original Symbolic Concept (AC-01)

- **Asset Path**: `public/images/landing/hero-symbolic-mesh.svg`
- **Concept**: Engraving-style intricate knowledge & community constellation.
- **Visual Elements**:
  - Central S-cool core monogram surrounded by multi-layered concentric harmonic rings and glowing gradient auras.
  - Orbiting polyhedral lattice connecting 4 primary domain nodes:
    1. **Peer Community** (top node, `#0F766E` deep teal) — collaborative student dialogue.
    2. **Structured Courses** (right node, `#0D9488` teal) — syllabus, video modules, and milestones.
    3. **Proof of Work** (bottom node, `#D97706` amber) — deterministic submissions, peer rubrics, and verified credentials.
    4. **Shared Codex** (left node, `#334155` slate) — compounding community repository.
- **Color Tokens**:
  - Primary Teal: `#0F766E`, `#0D9488`
  - Accent Amber: `#D97706`, `#F59E0B`
  - Ink / Slate: `#0F172A`, `#1E293B`, `#475569`
  - Canvas / Surface: `#F8FAFC`, `#FFFFFF`
- **Graceful Failure Fallback**:
  - Image element has `onerror="this.style.display='none'"` with semantic text captions; if images fail or are blocked, the hero structure and headline/CTA retain complete readability and layout integrity.

---

## 3. Genuine Product Visuals (AC-02)

Historical submission states that screenshots were captured from the running local application (`http://127.0.0.1:8080`) using real seeded demo data without mock data, fake metrics, or artificial scaling:

| Visual | File Path | Origin / Source Route | Description |
|---|---|---|---|
| Community Feed | `public/images/landing/product/product-community-feed.png` | `http://127.0.0.1:8080/communities/the-ai-advantage` | Real conversational feed with pinned notices, likes, comments, and creator role badge. |
| Classroom Outline | `public/images/landing/product/product-classroom-outline.png` | `http://127.0.0.1:8080/communities/the-ai-advantage/classroom` | Structured syllabus modules, video lessons, and completion markers. |
| Leaderboard preview | `public/images/landing/product/product-proof-of-work.png` | `http://127.0.0.1:8080/communities/the-ai-advantage/leaderboard` | Real community leaderboard showing student rankings, points, and 7-day challenge milestones. |

---

## 4. Marquee Logos & Meaning (AC-03, AC-06)

Logos represent educational topics, open learning resources, and community reference links — **not commercial partners, sponsors, or client endorsements**:

- **MIT OCW**: Open learning resource for computer science curricula.
- **Vietnam National University (VNU)**: Higher education institution in Vietnam.
- **Stanford Online**: Video lecture and course materials.
- **HUST (Đại học Bách Khoa Hà Nội)**: Engineering and technology student community.
- **RMIT University Vietnam**: Technology and business curriculum resource.
- **Harvard Online**: Independent educational video content.
- **FPT University**: Practical technology and software training.
- **MindX Technology**: Coding bootcamp and project-based learning.
- **GitHub Education**: Developer tooling and open-source learning.
- **Google Developer Student Clubs (GDSC)**: Student tech workshops.
- **42 Network**: Peer-to-peer coding school methodology.
- **VietAI**: Community AI and machine learning study groups.
- **AWS Cloud Clubs**: Cloud computing educational resources.
- **Microsoft Learn**: Technical documentation and certifications.
- **CoderSchool**: Full-stack engineering curriculum.
- **HackerRank**: Algorithmic problem solving and skills verification.

---

## 5. Licensing & Provenance

- **Original SVG Illustration**: Created specifically for the S-cool project under repository license.
- **Product Screenshots**: S-cool user interface captured under local testing environment with sanitized demo seed content.
- **Logos / Trademarks**: Third-party marks used as educational references. Specific permission/license review is pending; this document does not establish fair use or an endorsement.
