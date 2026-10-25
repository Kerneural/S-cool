# EUR-69 — Current curriculum and source inventory

2026-10-25 candidate: base `b24451869a6aefbbd14c0275cbe71cfee0a50dae` plus branch `eur-67-69-71-acceptance` working diff. Stack is PHP 8.2.35 / Laravel 12 / MySQL / Blade, Alpine and Livewire.

Inventory below is generated from `RealCommunitySampleSeeder::catalog()`: five courses, two sections each and twenty lessons. Field presence is static evidence only. It does not prove independent practice, media rights, external video availability or pedagogical acceptance.

Corrections in this follow-up: split Python shell versus TOML instructions; remove arbitrary timing guarantees; add missing API 404/test example; distinguish model-specific character reference flags; describe structured-output limitations; replace tattoo equipment/clinical instructions with paper/source-review exercises. Primary sources include Python/FastAPI/Pydantic documentation, Midjourney reference documentation, OpenAI structured outputs, FDA and OSHA links supplied in the lesson text.

External introductory videos, logos and third-party media retain their original candidate references. Their availability, relevance and permissions still require review. Source attribution alone is not permission to copy a work.

## Python Developers

Course: `Python Zero to Production`; slug: `python-developers`.

| Section | Lesson | Primary/source reference in content |
|---|---|---|
| Module 1: Modern Python Foundations & Tooling | Environment Setup & Isolated Virtual Environments | Python Packaging User Guide (https://packaging.python.org/en/latest/guides/installing-using-pip-and-virtual-environments/), PEP 621 Metadata Standard (https://peps.python.org/pep-0621/). |
| Module 1: Modern Python Foundations & Tooling | Idiomatic Data Structures & High-Performance Comprehensions | Python Official Documentation: Data Structures (https://docs.python.org/3/tutorial/datastructures.html), Raymond Hettinger: Modern Dictionaries By Example. |
| Module 2: Production APIs & Automation Pipelines | Building Asynchronous Web APIs with FastAPI & Pydantic V2 | FastAPI Tutorial (https://fastapi.tiangolo.com/tutorial/), Pydantic V2 Documentation (https://docs.pydantic.dev/latest/). |
| Module 2: Production APIs & Automation Pipelines | Resilient Web Scraping & Ingestion with Retry Policies | Tenacity Documentation (https://tenacity.readthedocs.io/), AWS Architecture Blog: Exponential Backoff And Jitter. |

## AI Video Bootcamp

Course: `AI Cinematic Filmmaking Masterclass`; slug: `ai-video-bootcamp`.

| Section | Lesson | Primary/source reference in content |
|---|---|---|
| Stage 1: Prompt Crafting & Camera Optics | Camera Movements, Lighting & Focal Length Prompts | American Cinematographer Manual (10th Edition), Midjourney Parameter Guide (https://docs.midjourney.com/docs/parameter-list). |
| Stage 1: Prompt Crafting & Camera Optics | Character Consistency & Multi-Angle Keyframing | Midjourney Character References (https://updates.midjourney.com/character-refs/); Omni Reference (https://docs.midjourney.com/hc/en-us/articles/36285124473997-Omni-Reference). |
| Stage 2: Video Engines & Motion Control | Runway Gen-3 & Kling AI Motion Brush Control | Runway Gen-3 Alpha Motion Control Guide (https://help.runwayml.com), Kling AI Official Tutorials. |
| Stage 2: Video Engines & Motion Control | Voice Synthesis, Lip-Sync & Final Compositing | ElevenLabs Voice Design Best Practices, SMPTE Standards for Audio-Video Synchronization. |

## Maker School: AI Automation

Course: `Enterprise AI Automations Blueprint`; slug: `maker-school-ai-automation`.

| Section | Lesson | Primary/source reference in content |
|---|---|---|
| Phase 1: Architecture, Scoping & Webhook Security | Client Scoping, Process Discovery & Data Flow Mapping | Enterprise Integration Patterns (Gregor Hohpe & Bobby Woolf), BPMN 2.0 Business Process Model and Notation Specification. |
| Phase 1: Architecture, Scoping & Webhook Security | Webhook Architecture, Security & Payload Normalization | Stripe Webhook Signature Verification Guide (https://stripe.com/docs/webhooks/signatures), RFC 2104 HMAC Specification. |
| Phase 2: Resilient Integrations & Structured LLM Extraction | Dead Letter Queues & Exponential Backoff Retries | AWS Architecture: Reliable and Scalable Webhook Delivery with DLQ, Make.com Error Handling Directives. |
| Phase 2: Resilient Integrations & Structured LLM Extraction | Structured LLM Extraction & Decision Nodes | OpenAI Guide: Structured Outputs (https://platform.openai.com/docs/guides/structured-outputs), Instructor Library Documentation. |

## Agentic AI for Founders

Course: `Autonomous Agent Systems at Scale`; slug: `agentic-ai-founders`.

| Section | Lesson | Primary/source reference in content |
|---|---|---|
| Module 1: Agentic Orchestration & Security Guardrails | Planner-Worker Architecture & Deterministic State Machines | LangGraph Concepts: State Machine Graphs (https://langchain-ai.github.io/langgraph/), Yao et al., ReAct: Synergizing Reasoning and Acting in Language Models (2022). |
| Module 1: Agentic Orchestration & Security Guardrails | Safe Tool-Calling, Schema Validation & Guardrails | OWASP Top 10 for Large Language Model Applications (https://owasp.org/www-project-top-10-for-large-language-model-applications/). |
| Module 2: Memory Persistence & Automated Evaluation | Short-Term & Long-Term Memory Persistence (Vector + SQL) | MemGPT: Towards LLMs as Operating Systems (Packer et al., 2023), pgvector Extension Guide. |
| Module 2: Memory Persistence & Automated Evaluation | Automated Regression Testing with LLM-as-a-Judge | G-Eval: NLG Evaluation using GPT-4 with Better Human Alignment (Liu et al., 2023). |

## Tattoo Masterclass Incubator

Course: `Precision Tattooing: Needle Theory & Skin Dynamics`; slug: `tattoo-masterclass`.

| Section | Lesson | Primary/source reference in content |
|---|---|---|
| Module 1: Sanitation Protocols & Equipment Engineering | Bloodborne Pathogens, Sterilization & Station Setup | OSHA tattoo-industry interpretation (https://www.osha.gov/laws-regs/standardinterpretations/2002-07-29); Bloodborne Pathogens standard (https://www.osha.gov/laws-regs/regulations/standardnumber/1910/1910.1030). |
| Module 1: Sanitation Protocols & Equipment Engineering | Needle Tapers, Gauges, Groupings & Machine Stroke | FDA Tattoos and Permanent Makeup fact sheet (https://www.fda.gov/cosmetics/cosmetic-products/tattoos-permanent-makeup-fact-sheet); use the specific equipment manufacturer manual as the exercise source. |
| Module 2: Biomechanics, Line Precision & Healed Results | Depth Control, Hand Speed Synchronization & Blowout Prevention | FDA Tattoos and Permanent Makeup fact sheet (https://www.fda.gov/cosmetics/cosmetic-products/tattoos-permanent-makeup-fact-sheet). |
| Module 2: Biomechanics, Line Precision & Healed Results | Multi-Plane Skin Tensioning & Healed Aftercare Science | FDA Tattoos and Permanent Makeup fact sheet (https://www.fda.gov/cosmetics/cosmetic-products/tattoos-permanent-makeup-fact-sheet). |
