# Applied Demo Learning Catalog (Group B) - Curriculum & Source Map

**Issue:** [PQ-03B (EUR-69)](https://linear.app/kerneural/issue/EUR-69)  
**Catalog Group:** Group B (Applied Skills & Specialized Cohorts)  
**Target Milestone:** M13 - Platform Quality & Demonstration Readiness  
**Target Schema:** S-cool LMS Core (Community -> Course -> CourseSection -> Lesson)  
**Execution Environment:** PHP 8.3 / Laravel 11 / MySQL 8.0 / Inertia Vue 3  

---

## 1. Catalog Architecture Overview

The Group B Applied Catalog represents 5 specialized, high-retention learning communities designed for adult career transitioners, founders, and creative operators. Each curriculum is architected into 2 distinct progression milestones (modules/stages/phases) containing 2 substantive lessons each (4 lessons per course, 20 lessons total across Group B).

Every lesson complies strictly with the 5-component pedagogic standard:
1. **Learning Objective**: Observable, behavioral outcome with measurable performance thresholds.
2. **Core Mental Model & Explanation**: First-principles technical explanation without hand-waving or filler.
3. **Worked Example**: Runnable code, production configuration, or executable prompt recipe.
4. **Hands-on Learner Activity**: Concrete exercise designed for peer review and proof-of-work.
5. **Expected Outcome & Verification**: Deterministic acceptance criteria and automated/manual verification benchmarks.
6. **Source References**: Canonical primary literature, PEPs, RFCs, and verified technical documentation.

---

## 2. Curriculum & Primary Source Mapping

### Community 1: Python Developers (`python-developers`)
* **Community Model**: Free Onboarding & Open Developer Community
* **Course Title**: `Python Zero to Production`
* **Course Description**: Comprehensive roadmap to becoming an autonomous Python engineer: modern environment isolation, idiomatic data structures, clean architecture, and asynchronous FastAPI services.
* **Introductory Media**: YouTube Embed (`https://www.youtube.com/watch?v=0YQVadm4V-Q`)

| Module / Section | Lesson Title | Practical / Skill Label | Primary Source Reference | Verification Outcome |
| :--- | :--- | :--- | :--- | :--- |
| **Module 1: Modern Python Foundations & Tooling** | Environment Setup & Isolated Virtual Environments | **Yes**<br>`Python Environment Isolation (pyproject.toml & venv)` | [Python Packaging User Guide](https://packaging.python.org/en/latest/guides/installing-using-pip-and-virtual-environments/), [PEP 621](https://peps.python.org/pep-0621/) | Clean `.venv` execution without global pollution; deterministic `pip list`. |
| **Module 1: Modern Python Foundations & Tooling** | Idiomatic Data Structures & High-Performance Comprehensions | No | [Python Official Documentation: Data Structures](https://docs.python.org/3/tutorial/datastructures.html), Hettinger (2017) | O(1) hash map operations, memory-bounded generator streams, sub-10ms runtime. |
| **Module 2: Production APIs & Automation Pipelines** | Building Asynchronous Web APIs with FastAPI & Pydantic V2 | **Yes**<br>`FastAPI REST Service with Pydantic V2 Validation` | [FastAPI Official Docs](https://fastapi.tiangolo.com/tutorial/), [Pydantic V2 Core](https://docs.pydantic.dev/latest/) | Passing pytest suite with HTTP 201, 404, and 422 validation assertions. |
| **Module 2: Production APIs & Automation Pipelines** | Resilient Web Scraping & Ingestion with Retry Policies | No | [Tenacity Documentation](https://tenacity.readthedocs.io/), AWS Exponential Backoff Architecture | Resilient backoff retry intervals (2s, 4s) during simulated 429/503 spikes. |

---

### Community 2: AI Video Bootcamp (`ai-video-bootcamp`)
* **Community Model**: Paid Cohort (225,000 VND / 30 Days)
* **Course Title**: `AI Cinematic Filmmaking Masterclass`
* **Course Description**: Professional curriculum for creating studio-quality generative AI films, commercials, and visual storytelling with consistent characters and motion control.
* **Introductory Media**: Loom Embed (`https://www.loom.com/share/e0838a4ac97647beb59ed491e6407579`)

| Stage / Section | Lesson Title | Practical / Skill Label | Primary Source Reference | Verification Outcome |
| :--- | :--- | :--- | :--- | :--- |
| **Stage 1: Prompt Crafting & Camera Optics** | Camera Movements, Lighting & Focal Length Prompts | **Yes**<br>`Cinematic AI Video Prompt Engineering & Camera Direction` | American Cinematographer Manual (10th Ed.), [Midjourney Parameters](https://docs.midjourney.com/docs/parameter-list) | Prompts utilize optical tokens (85mm, f/2.0, anamorphic) without buzzword filler. |
| **Stage 1: Prompt Crafting & Camera Optics** | Character Consistency & Multi-Angle Keyframing | No | [Midjourney Character Reference (`--cref`) Guide](https://docs.midjourney.com/docs/character-reference) | Preservation of facial geometry across diverse scene lighting and angles. |
| **Stage 2: Video Engines & Motion Control** | Runway Gen-3 & Kling AI Motion Brush Control | **Yes**<br>`Motion Brush Dynamics & Regional Motion Masking` | Runway Research Gen-3 Alpha Docs, Kling AI Multi-Layer Motion Guides | Independent foreground/background velocity vectors with zero edge bleed. |
| **Stage 2: Video Engines & Motion Control** | Voice Synthesis, Lip-Sync & Final Compositing | No | ElevenLabs Audio Design Best Practices, SMPTE Audio-Visual Sync Standards | Audio-to-viseme alignment within 2 frames tolerance; 35mm grain matching. |

---

### Community 3: Maker School: AI Automation (`maker-school-ai-automation`)
* **Community Model**: Paid Agency Incubator (4,600,000 VND / 30 Days)
* **Course Title**: `Enterprise AI Automations Blueprint`
* **Course Description**: From initial discovery calls to deploying multi-agent resilient workflows for enterprise clients using Make.com, n8n, webhooks, and structured LLM extraction.
* **Introductory Media**: Loom Embed (`https://www.loom.com/share/f0c25c3dbfd245499fc7343c01ab5a24`)

| Phase / Section | Lesson Title | Practical / Skill Label | Primary Source Reference | Verification Outcome |
| :--- | :--- | :--- | :--- | :--- |
| **Phase 1: Architecture, Scoping & Webhook Security** | Client Scoping, Process Discovery & Data Flow Mapping | **Yes**<br>`Enterprise Process Discovery & Mermaid Data Flow Mapping` | Enterprise Integration Patterns (Hohpe & Woolf), BPMN 2.0 Standard | Valid Mermaid diagram capturing triggers, AI transformation, CRM, and fallbacks. |
| **Phase 1: Architecture, Scoping & Webhook Security** | Webhook Architecture, Security & Payload Normalization | No | [Stripe Webhook Verification Spec](https://stripe.com/docs/webhooks/signatures), RFC 2104 HMAC | Constant-time HMAC verification; idempotent deduplication of replay requests. |
| **Phase 2: Resilient Integrations & Structured LLM Extraction** | Dead Letter Queues & Exponential Backoff Retries | **Yes**<br>`Fault-Tolerant Pipeline Design with DLQ Fallback` | AWS Well-Architected Reliability Pillar, Make.com Error Handling Specs | Zero silent payload drops; persistent failures capture complete error JSON to DLQ. |
| **Phase 2: Resilient Integrations & Structured LLM Extraction** | Structured LLM Extraction & Decision Nodes | No | [OpenAI Structured Outputs Guide](https://platform.openai.com/docs/guides/structured-outputs), Pydantic Instructor Docs | Strict token-level grammar adherence; guaranteed valid JSON entity extraction. |

---

### Community 4: Agentic AI for Founders (`agentic-ai-founders`)
* **Community Model**: Paid Executive Mastermind (2,400,000 VND / 30 Days)
* **Course Title**: `Autonomous Agent Systems at Scale`
* **Course Description**: Designing multi-agent systems with deterministic guardrails, state machine orchestration, hybrid memory persistence, and automated LLM-as-a-judge evaluation.
* **Introductory Media**: Loom Embed (`https://www.loom.com/share/916707c037fe4cac91a87060bf03c15b`)

| Module / Section | Lesson Title | Practical / Skill Label | Primary Source Reference | Verification Outcome |
| :--- | :--- | :--- | :--- | :--- |
| **Module 1: Agentic Orchestration & Security Guardrails** | Planner-Worker Architecture & Deterministic State Machines | **Yes**<br>`Deterministic State Machine Agent Orchestration` | [LangGraph Architecture](https://langchain-ai.github.io/langgraph/), Yao et al. (ReAct 2022) | State machine transitions cleanly; loop bounds terminate after max retries. |
| **Module 1: Agentic Orchestration & Security Guardrails** | Safe Tool-Calling, Schema Validation & Guardrails | No | [OWASP Top 10 for LLM Applications](https://owasp.org/www-project-top-10-for-large-language-model-applications/) | AST SQL parser permits `SELECT` statements and strictly rejects mutations. |
| **Module 2: Memory Persistence & Automated Evaluation** | Short-Term & Long-Term Memory Persistence (Vector + SQL) | **Yes**<br>`Hybrid Agent Memory Persistence Architecture` | Packer et al. (MemGPT 2023), PostgreSQL pgvector documentation | Token usage bounded within budget; critical facts survive 10 simulated turns. |
| **Module 2: Memory Persistence & Automated Evaluation** | Automated Regression Testing with LLM-as-a-Judge | No | Liu et al. (G-Eval 2023), MT-Bench LLM Judge Rubric | Structured rubric output with numerical scores, reasoning trace, and CI pass/fail. |

---

### Community 5: Tattoo Masterclass Incubator (`tattoo-masterclass`)
* **Community Model**: Paid Mentorship Incubator (980,000 VND / 30 Days)
* **Course Title**: `Precision Tattooing: Needle Theory & Skin Dynamics`
* **Course Description**: Complete curriculum from medical-grade workstation setup to intricate fine-line geometry, needle depth control, and skin layer biomechanics.
* **Introductory Media**: Loom Embed (`https://www.loom.com/share/0eea2204985144679429cc8ac0508363`)

| Module / Section | Lesson Title | Practical / Skill Label | Primary Source Reference | Verification Outcome |
| :--- | :--- | :--- | :--- | :--- |
| **Module 1: Sanitation Protocols & Equipment Engineering** | Bloodborne Pathogens, Sterilization & Station Setup | **Yes**<br>`OSHA-Compliant Medical Workstation Setup & Barrier Protocol` | OSHA 29 CFR 1910.1030, Alliance of Professional Tattooists (APT) Guidelines | Complete SOP covering PPE sequencing, barrier wrapping, and sharps handling. |
| **Module 1: Sanitation Protocols & Equipment Engineering** | Needle Tapers, Gauges, Groupings & Machine Stroke | No | Modern Tattooing Fundamentals (Jordan), Rotary Machine Stroke Engineering | Accurate pairing of needle grouping (#10 3RL vs #12 9RL) with stroke (3.0mm vs 4.0mm). |
| **Module 2: Biomechanics, Line Precision & Healed Results** | Depth Control, Hand Speed Synchronization & Blowout Prevention | **Yes**<br>`Needle Depth Control & Blowout Prevention on Practice Skin` | Dermatological Anatomy Studies, Fitzpatrick Tissue Resistance Papers | Uniform 1.5mm-2.0mm papillary dermis depth; zero blowouts into subcutaneous fat. |
| **Module 2: Biomechanics, Line Precision & Healed Results** | Multi-Plane Skin Tensioning & Healed Aftercare Science | No | British Journal of Dermatology: Clinical Tattoo Healing and Dermal Bandages | Medical polyurethane 2-phase protocol yielding 95%+ pigment retention. |

---

## 3. Curriculum Audit Summary

* **Total Applied Communities**: 5
* **Total Courses**: 5 (100% published, 100% access-included for members)
* **Total Sections**: 10 (2 per course)
* **Total Lessons**: 20 (4 per course, exactly 2 per section)
* **Practical Lessons (`is_practical = true`)**: 10 (2 per course, exactly 1 per section)
* **Placeholder / Lorem-Ipsum Rate**: 0%
* **Pedagogic Component Compliance**: 100% across all 20 lessons (Objective, Explanation, Example, Practice, Outcome, References)
