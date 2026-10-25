<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use LogicException;

class RealCommunitySampleSeeder extends Seeder
{
    /**
     * Run the database seeds for real crawled communities (Group B: Applied Catalog).
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new LogicException('Real community sample content may only be seeded in local or testing environments.');
        }

        $creator = User::query()->where('email', 'creator@scool.local')->firstOrFail();
        $member = User::query()->where('email', 'member@scool.local')->firstOrFail();

        foreach ($this->catalog() as $data) {
            DB::transaction(function () use ($creator, $member, $data): void {
                $community = Community::query()->where('slug', $data['slug'])->first();

                if ($community !== null && (int) $community->creator_id !== (int) $creator->id) {
                    throw new LogicException("Community slug '{$data['slug']}' is already owned by another user.");
                }

                // Preserve existing records, including edits, publication and access state.
                if ($community !== null) {
                    return;
                }

                $community ??= new Community;
                if (! $community->exists) {
                    $community->forceFill([
                        'creator_id' => $creator->id,
                        'name' => $data['name'],
                        'slug' => $data['slug'],
                        'description' => $data['description'],
                        'rules' => $data['rules'],
                        'intro_video_url' => $data['intro_video_url'],
                        'onboarding_enabled' => true,
                        'visibility' => 'PRIVATE',
                        'access_mode' => $data['access_mode'],
                        'price_vnd' => $data['price_vnd'],
                        'billing_period' => $data['billing_period'],
                        'referral_commission_rate' => 15,
                        'status' => 'ACTIVE',
                    ])->save();

                    PostCategory::initializeDefaultsFor($community);

                    $membership = CommunityMembership::query()->firstOrNew([
                        'community_id' => $community->id,
                        'user_id' => $member->id,
                    ]);
                    $membership->forceFill([
                        'status' => 'ACTIVE',
                        'access_kind' => CommunityMembership::ACCESS_LIFETIME,
                        'expires_at' => null,
                    ])->save();
                }

                // Setup Course
                $courseSpec = $data['course'];
                $course = $community->courses()->where('title', $courseSpec['title'])->first();
                $course ??= new Course;
                $course->community_id = $community->id;
                $course->forceFill([
                    'title' => $courseSpec['title'],
                    'description' => $courseSpec['description'],
                    'access_mode' => Course::ACCESS_MODE_INCLUDED,
                    'price' => null,
                    'status' => 'PUBLISHED',
                    'order' => 1,
                    'published_revision' => max(1, (int) $course->published_revision),
                ])->save();

                // Setup Sections & Substantive 5-Part Lessons
                foreach ($courseSpec['sections'] as $secIndex => $secSpec) {
                    $section = $course->sections()->where('title', $secSpec['title'])->first();
                    $section ??= new CourseSection;
                    $section->course_id = $course->id;
                    $section->forceFill([
                        'title' => $secSpec['title'],
                        'order' => $secIndex + 1,
                    ])->save();

                    foreach ($secSpec['lessons'] as $lesIndex => $lesSpec) {
                        $lesson = $section->lessons()->where('title', $lesSpec['title'])->first();
                        $lesson ??= new Lesson;
                        $lesson->course_section_id = $section->id;
                        $lesson->forceFill([
                            'title' => $lesSpec['title'],
                            'content' => $this->buildLessonContent($lesSpec),
                            'video_url' => $lesSpec['video_url'] ?? null,
                            'status' => 'PUBLISHED',
                            'order' => $lesIndex + 1,
                            'is_practical' => $lesSpec['is_practical'] ?? false,
                            'skill_label' => $lesSpec['skill_label'] ?? null,
                        ])->save();
                    }
                }

                // Setup Welcome Announcement Post
                $announcementsCat = $community->categories()->where('slug', 'announcements')->first();
                $post = Post::query()->firstOrCreate(
                    [
                        'community_id' => $community->id,
                        'title' => $data['post_title'],
                    ],
                    [
                        'user_id' => $creator->id,
                        'category_id' => $announcementsCat?->id,
                        'body' => $data['post_body'],
                        'pinned_at' => CarbonImmutable::now('UTC'),
                    ]
                );

                // Add sample member comment
                Comment::query()->firstOrCreate(
                    [
                        'post_id' => $post->id,
                        'user_id' => $member->id,
                    ],
                    [
                        'body' => 'Excited to be here! Looking forward to working through the modules and submitting proof of work.',
                    ]
                );
            }, 3);
        }
    }

    /**
     * Build standardized 5-part lesson content compliant with EUR-69 AC-02.
     *
     * @param  array<string, mixed>  $spec
     */
    private function buildLessonContent(array $spec): string
    {
        return implode("\n\n", [
            "🎯 LEARNING OBJECTIVE\n{$spec['objective']}",
            "📖 CORE MENTAL MODEL & EXPLANATION\n{$spec['explanation']}",
            "💡 WORKED EXAMPLE & IMPLEMENTATION\n{$spec['example']}",
            "🛠️ HANDS-ON LEARNER ACTIVITY\n{$spec['practice']}",
            "✅ EXPECTED OUTCOME & VERIFICATION CRITERIA\n{$spec['expected_outcome']}",
            "📚 REFERENCES & FURTHER READING\n{$spec['source_reference']}",
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function catalog(): array
    {
        return [
            $this->pythonCatalog(),
            $this->aiVideoCatalog(),
            $this->automationCatalog(),
            $this->agenticCatalog(),
            $this->tattooCatalog(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function pythonCatalog(): array
    {
        return [
            'name' => 'Python Developers',
            'slug' => 'python-developers',
            'description' => "Welcome to Python Developers! A collaborative learning community for Python engineers, automation builders, and backend developers.\nMaster modern Python 3.12+ tooling, idiomatic structures, FastAPI services, and production deployment.",
            'rules' => "1. Be kind, constructive, and helpful to beginners.\n2. When asking questions, provide minimal reproducible code examples.\n3. Format your code properly with markdown syntax.\n4. No low-effort self-promotion or affiliate spam.\n5. Keep debates focused on technical merit and facts.",
            'intro_video_url' => 'https://www.youtube.com/watch?v=0YQVadm4V-Q',
            'access_mode' => 'FREE',
            'price_vnd' => null,
            'billing_period' => 'ONE_TIME',
            'post_title' => 'Welcome to Python Developers! Introduce yourself and share your goals',
            'post_body' => 'Welcome to the community! Please drop a quick introduction below: what Python projects are you building, what stack do you use, and what is your primary learning goal for this sprint?',
            'course' => [
                'title' => 'Python Zero to Production',
                'description' => 'Comprehensive roadmap to becoming an autonomous Python engineer: modern environment isolation, idiomatic data structures, clean architecture, and asynchronous FastAPI services.',
                'sections' => [
                    [
                        'title' => 'Module 1: Modern Python Foundations & Tooling',
                        'lessons' => [
                            [
                                'title' => 'Environment Setup & Isolated Virtual Environments',
                                'is_practical' => true,
                                'skill_label' => 'Python Environment Isolation (pyproject.toml & venv)',
                                'objective' => 'Configure a reproducible, isolated Python 3.12+ development workspace using pyproject.toml, standard venv, and deterministic dependency pinning without global environment contamination.',
                                'explanation' => "Global package installations via 'pip install' cause conflicting dependency trees across projects. PEP 518 and PEP 621 standardized 'pyproject.toml' as the canonical project specification. Virtual environments ('venv') isolate the Python executable and 'site-packages' directory per workspace, ensuring build reproducibility and predictable containerized deployment.",
                                'example' => "[pyproject.toml]\n[project]\nname = \"scool-python-service\"\nversion = \"0.1.0\"\ndescription = \"Production learning service\"\nrequires-python = \">=3.12\"\ndependencies = [\n    \"pydantic>=2.7.0\",\n    \"httpx>=0.27.0\",\n]\n\n[Terminal execution commands]\npython -m venv .venv\nsource .venv/bin/activate  (On Windows: .venv\\Scripts\\Activate.ps1)\npython -m pip install --upgrade pip\npip install -e .\npython -c \"import sys; print(sys.prefix)\"",
                                'practice' => "1. Initialize a new folder named 'python-lab'.\n2. Create 'pyproject.toml' specifying Python >= 3.12 and dependencies 'pydantic' and 'httpx'.\n3. Create and activate a dedicated virtual environment '.venv'.\n4. Install the package in editable mode and run a one-line verification asserting 'sys.prefix' points to your local '.venv' directory.\n5. Submit your terminal output as proof of completion.",
                                'expected_outcome' => "Terminal prints the absolute path ending with '.venv', 'pip list' outputs only the pinned packages without global system libraries, and exit code is 0.",
                                'source_reference' => 'Python Packaging User Guide (https://packaging.python.org/en/latest/guides/installing-using-pip-and-virtual-environments/), PEP 621 Metadata Standard (https://peps.python.org/pep-0621/).',
                            ],
                            [
                                'title' => 'Idiomatic Data Structures & High-Performance Comprehensions',
                                'is_practical' => false,
                                'skill_label' => null,
                                'objective' => 'Analyze time/space complexity tradeoffs across list, dict, set, and tuple, and write idiomatic dictionary/set comprehensions with structural pattern matching.',
                                'explanation' => 'Python dictionaries utilize a compact hash table offering average O(1) time complexity for insertions and lookups. In contrast, linear scans over lists require O(N) comparisons. Generator expressions yield items lazily, avoiding allocating large intermediate lists in memory when processing streaming data.',
                                'example' => "from collections import defaultdict\n\nraw_events = [\n    {\"user_id\": 101, \"amount\": 250, \"status\": \"CONFIRMED\"},\n    {\"user_id\": 102, \"amount\": 0, \"status\": \"REJECTED\"},\n    {\"user_id\": 101, \"amount\": 150, \"status\": \"CONFIRMED\"},\n]\n\n# Efficient O(N) aggregation\ntotals: dict[int, int] = defaultdict(int)\nfor event in raw_events:\n    if event[\"status\"] == \"CONFIRMED\":\n        totals[event[\"user_id\"]] += event[\"amount\"]\n\n# Filtered dictionary comprehension\nhigh_value_users = {uid: total for uid, total in totals.items() if total >= 200}\nprint(high_value_users)  # Output: {101: 400}",
                                'practice' => "1. Given a list of 500 mock user log records containing fields 'user_id', 'action', and 'duration_ms'.\n2. Use 'defaultdict' and a dictionary comprehension to compute the total duration per user for 'action == \"login\"'.\n3. Filter for users with total login duration > 1,000ms.\n4. Measure execution time with the 'timeit' module to confirm sub-10ms performance.",
                                'expected_outcome' => 'A filtered dictionary containing aggregated durations exceeding 1,000ms, executed cleanly with zero KeyError exceptions and runtime under 10ms.',
                                'source_reference' => 'Python Official Documentation: Data Structures (https://docs.python.org/3/tutorial/datastructures.html), Raymond Hettinger: Modern Dictionaries By Example.',
                            ],
                        ],
                    ],
                    [
                        'title' => 'Module 2: Production APIs & Automation Pipelines',
                        'lessons' => [
                            [
                                'title' => 'Building Asynchronous Web APIs with FastAPI & Pydantic V2',
                                'is_practical' => true,
                                'skill_label' => 'FastAPI REST Service with Pydantic V2 Validation',
                                'objective' => 'Construct, validate, and document a non-blocking asynchronous REST API using FastAPI and Pydantic V2 schemas with custom validators.',
                                'explanation' => "FastAPI runs on ASGI servers (such as Uvicorn) utilizing Python's asyncio event loop. By using 'async def' for I/O-bound endpoints (database queries, network requests), the worker thread is released during I/O wait, dramatically increasing concurrency compared to synchronous WSGI frameworks. Pydantic V2 leverages a Rust-compiled core (pydantic-core) for 5-10x faster serialization and strict type enforcement.",
                                'example' => "from fastapi import FastAPI, HTTPException, status\nfrom pydantic import BaseModel, EmailStr, Field\nimport uuid\n\napp = FastAPI(title=\"Student Enrollment API\")\n\nclass EnrollmentRequest(BaseModel):\n    email: EmailStr\n    course_id: int = Field(gt=0, description=\"Course ID must be positive\")\n    referral_code: str | None = Field(default=None, max_length=20)\n\nclass EnrollmentResponse(BaseModel):\n    receipt_id: str\n    status: str\n\n@app.post(\"/api/v1/enrollments\", status_code=status.HTTP_201_CREATED)\nasync def enroll_student(payload: EnrollmentRequest) -> EnrollmentResponse:\n    receipt_id = f\"REC-{uuid.uuid4().hex[:8].upper()}\"\n    return EnrollmentResponse(receipt_id=receipt_id, status=\"ENROLLED\")",
                                'practice' => "1. Implement the FastAPI endpoint with input validation for email and positive course ID.\n2. Add an error condition: if 'course_id == 999', raise an HTTPException(404, 'Course not found').\n3. Use 'httpx.AsyncClient' in a pytest function to test both valid (201) and invalid (422 / 404) requests.\n4. Submit the passing pytest output.",
                                'expected_outcome' => 'Pytest executes 3 test cases: valid request returns HTTP 201 with receipt_id; malformed email returns HTTP 422; course_id=999 returns HTTP 404. All 3 pass.',
                                'source_reference' => 'FastAPI Tutorial (https://fastapi.tiangolo.com/tutorial/), Pydantic V2 Documentation (https://docs.pydantic.dev/latest/).',
                            ],
                            [
                                'title' => 'Resilient Web Scraping & Ingestion with Retry Policies',
                                'is_practical' => false,
                                'skill_label' => null,
                                'objective' => 'Architect automated web scraping and data ingestion pipelines with exponential backoff, rate limiting, and structured logging.',
                                'explanation' => 'Automated ingestion pipelines frequently encounter transient HTTP 429 (Too Many Requests) or 503 (Service Unavailable) errors. Implementing exponential backoff with randomized jitter prevents retry storms from overwhelming servers. Using context managers ensures HTTP connection pools and file handles are safely released upon unexpected exceptions.',
                                'example' => "import asyncio\nimport httpx\nfrom tenacity import retry, stop_after_attempt, wait_exponential, retry_if_exception_type\n\nclass TransientNetworkError(Exception):\n    pass\n\n@retry(\n    stop=stop_after_attempt(3),\n    wait=wait_exponential(multiplier=1, min=2, max=10),\n    retry=retry_if_exception_type(TransientNetworkError),\n    reraise=True\n)\nasync def fetch_community_metrics(slug: str) -> dict:\n    async with httpx.AsyncClient(timeout=5.0) as client:\n        resp = await client.get(f\"https://api.example.test/v1/communities/{slug}\")\n        if resp.status_code in (429, 502, 503):\n            raise TransientNetworkError(f\"Server busy: {resp.status_code}\")\n        resp.raise_for_status()\n        return resp.json()",
                                'practice' => "1. Write a script simulating transient network failures using Tenacity retry decorators.\n2. Verify that transient errors trigger exponential retry delays (2s, 4s) before logging success or final failure.\n3. Output structured JSON logs recording attempt count, response time, and outcome.",
                                'expected_outcome' => 'Script logs retry attempts with exponential delays, recovers automatically on transient failures, and outputs clean structured JSON.',
                                'source_reference' => 'Tenacity Documentation (https://tenacity.readthedocs.io/), AWS Architecture Blog: Exponential Backoff And Jitter.',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function aiVideoCatalog(): array
    {
        return [
            'name' => 'AI Video Bootcamp',
            'slug' => 'ai-video-bootcamp',
            'description' => "Master generative AI video workflows and cinematic visual storytelling.\nLearn Runway Gen-3 Alpha, Kling AI 1.5, Midjourney v6.1 keyframing, Luma Dream Machine, and video compositing for commercial productions.",
            'rules' => "1. Respect intellectual property and original artists.\n2. Always share the prompts or key techniques when sharing experiments.\n3. Give actionable, constructive critiques.\n4. No spamming referral or promo links.",
            'intro_video_url' => 'https://www.loom.com/share/e0838a4ac97647beb59ed491e6407579',
            'access_mode' => 'PAID',
            'price_vnd' => 225000,
            'billing_period' => 'TERM_30_DAYS',
            'post_title' => 'Weekly Showcase: Share your 10-second AI Video clips & prompt recipes',
            'post_body' => 'Drop your latest AI video generations in the thread! Include the model used (Runway Gen-3, Kling 1.5, or Luma), your base keyframe image, and motion prompt recipe.',
            'course' => [
                'title' => 'AI Cinematic Filmmaking Masterclass',
                'description' => 'Professional curriculum for creating studio-quality generative AI films, commercials, and visual storytelling with consistent characters and motion control.',
                'sections' => [
                    [
                        'title' => 'Stage 1: Prompt Crafting & Camera Optics',
                        'lessons' => [
                            [
                                'title' => 'Camera Movements, Lighting & Focal Length Prompts',
                                'is_practical' => true,
                                'skill_label' => 'Cinematic AI Video Prompt Engineering & Camera Direction',
                                'objective' => 'Formulate deterministic text prompts controlling camera optics (anamorphic, 35mm, 85mm), volumetric lighting, and camera motion vectors.',
                                'explanation' => "Diffusion models respond predictably to optical and cinematography tokens rather than generic buzzwords like 'hyperrealistic'. Specifying camera lens focal length, aperture (f/1.8), lighting setup (chiaroscuro, golden hour rim light), and directional motion vectors (dolly in slow, low-angle pedestal up) yields reproducible cinematic aesthetics without visual artifacts.",
                                'example' => "[Production Prompt Structure]\n[Subject & Action] + [Camera Lens & Angle] + [Lighting & Environment] + [Color Grading & Film Stock] + [Motion Vector]\n\n[Worked Example]\nCinematic medium close-up of a cybernetics researcher inspecting a holographic circuit board, 85mm anamorphic lens, f/2.0 shallow depth of field, moody volumetric teal and amber rim lighting, subtle film grain, Kodak Vision3 500T color palette, slow orbital camera pan 45 degrees left to right --ar 16:9 --style raw --v 6.1",
                                'practice' => "1. Draft 3 distinct prompt recipes following the 5-part structure:\n   a) An urban street tracking shot at night.\n   b) A close-up character dialogue portrait.\n   c) An aerial landscape establishing shot.\n2. Ensure each prompt specifies exact focal length, lighting style, and camera vector.\n3. Submit your prompt recipes with rationales for each optical parameter.",
                                'expected_outcome' => "Prompts contain zero filler words (e.g. '4K', 'photorealistic'), strictly incorporate standard cinematography tokens, and yield coherent visual framing.",
                                'source_reference' => 'American Cinematographer Manual (10th Edition), Midjourney Parameter Guide (https://docs.midjourney.com/docs/parameter-list).',
                            ],
                            [
                                'title' => 'Character Consistency & Multi-Angle Keyframing',
                                'is_practical' => false,
                                'skill_label' => null,
                                'objective' => 'Generate consistent character keyframes across varying angles, lighting conditions, and emotional expressions using character reference tags (--cref).',
                                'explanation' => "A primary bottleneck in AI filmmaking is character facial drift between shots. By establishing a neutral master character portrait and passing its image URL via the '--cref' parameter with weight controls ('--cw 80'), diffusion latent seeds preserve facial geometry, eye shape, and hair texture across multiple camera angles and scenes.",
                                'example' => "[Step 1: Generate Master Reference Portrait]\nStudio portrait of a 30-year-old female architect, sharp jawline, hazel eyes, auburn hair tied in a low bun, neutral expression, white backdrop, 85mm portrait lens --ar 1:1 --v 6.1\n\n[Step 2: Generate Narrative Scene with Consistent Face]\nClose-up of the architect looking stressed over blueprints in a rain-streaked glass office, late night overhead desk lamp, --cref <REFERENCE_IMAGE_URL> --cw 85 --ar 16:9",
                                'practice' => "1. Generate or formulate a character master reference description.\n2. Write two derivative scene prompts placing the character in different emotional states and camera angles.\n3. Specify the appropriate character weight ('--cw') to allow clothing changes while locking facial landmarks.",
                                'expected_outcome' => 'The resulting prompts demonstrate how to lock identity landmarks while permitting costume and environmental variations across sequential narrative cuts.',
                                'source_reference' => 'Midjourney Character Reference Documentation (https://docs.midjourney.com/docs/character-reference).',
                            ],
                        ],
                    ],
                    [
                        'title' => 'Stage 2: Video Engines & Motion Control',
                        'lessons' => [
                            [
                                'title' => 'Runway Gen-3 & Kling AI Motion Brush Control',
                                'is_practical' => true,
                                'skill_label' => 'Motion Brush Dynamics & Regional Motion Masking',
                                'objective' => 'Direct regional motion speed, directional vectors, and camera dynamics using Runway Gen-3 Alpha and Kling AI motion brush masking tools.',
                                'explanation' => 'Image-to-video engines synthesize temporal motion by calculating vector fields between latent frames. The motion brush tool isolates specific pixel regions (e.g., flowing water, fluttering hair) and assigns independent vector velocity (X, Y, Z coordinates), preventing surrounding static structures from warping or morphing unnaturally.',
                                'example' => "[Motion Brush Configuration Guide]\nRegion 1 (Waterfall): Mask painted with soft edge; Velocity: Y = -4.5 (downward), Speed = 6.0\nRegion 2 (Cliff & Trees): Unmasked (Frozen anchor to prevent structural drift)\nCamera Movement: Fixed tripod with subtle zoom in (Scale: 1.05)\nDuration: 5 seconds @ 24fps",
                                'practice' => "1. Take a high-resolution landscape keyframe containing both moving elements (water/smoke) and rigid architecture.\n2. Define precise motion brush coordinates: specify which regions receive directional velocity and which remain pinned.\n3. Set camera pan speed to avoid rolling shutter distortion.\n4. Describe the motion parameters and submit verification rationale.",
                                'expected_outcome' => 'The motion configuration isolates fluid motion to designated regions while preserving architectural rigidity, achieving zero unwanted subject morphing.',
                                'source_reference' => 'Runway Gen-3 Alpha Motion Control Guide (https://help.runwayml.com), Kling AI Official Tutorials.',
                            ],
                            [
                                'title' => 'Voice Synthesis, Lip-Sync & Final Compositing',
                                'is_practical' => false,
                                'skill_label' => null,
                                'objective' => 'Synchronize AI-generated voice stems with character facial keyframes using ElevenLabs and LivePortrait, completing timeline compositing.',
                                'explanation' => 'Audio-visual realism requires accurate phoneme-to-viseme mapping. ElevenLabs generates expressive vocal audio with custom pacing and pauses. Lip-sync models map the audio waveform onto mouth and facial muscle deformation matrices. In timeline compositing (DaVinci Resolve / Premiere), adding subtle 35mm film grain unifies distinct AI renders into a coherent visual whole.',
                                'example' => "[Audio & Lip-Sync Pipeline Workflow]\n1. Audio Generation: Export 48kHz 24-bit WAV voiceover from ElevenLabs with stability=0.65, clarity=0.85.\n2. Viseme Mapping: Run character portrait + WAV file through LivePortrait / Hedra.\n3. Compositing: Import 4K video clips into timeline, apply LUT matching, add 1.5% 35mm organic film grain, cross-fade audio stems with -3dB sub-bass ducking under dialogue.",
                                'practice' => "1. Write a 15-second voiceover dialogue script for a dramatic teaser.\n2. Outline the step-by-step workflow to synchronize the dialogue with an existing character keyframe.\n3. Specify color grading, grain blending, and export codec settings (ProRes 422 or H.264 High Profile).",
                                'expected_outcome' => 'A production workflow document detailing voice synthesis, lip-sync alignment within 2 frames tolerance, and color grading parameters.',
                                'source_reference' => 'ElevenLabs Voice Design Best Practices, SMPTE Standards for Audio-Video Synchronization.',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function automationCatalog(): array
    {
        return [
            'name' => 'Maker School: AI Automation',
            'slug' => 'maker-school-ai-automation',
            'description' => "The premier community for building profitable AI automation agencies and autonomous client systems.\nMaster Make.com, n8n self-hosting, webhooks, OpenAI structured outputs, and enterprise integration patterns.",
            'rules' => "1. Execution over theory: Focus on shipping working automations.\n2. Maintain complete client privacy: Redact sensitive API keys and PII.\n3. Help fellow builders when you solve a hard bug.\n4. Quality over quantity.",
            'intro_video_url' => 'https://www.loom.com/share/f0c25c3dbfd245499fc7343c01ab5a24',
            'access_mode' => 'PAID',
            'price_vnd' => 4600000,
            'billing_period' => 'TERM_30_DAYS',
            'post_title' => 'Hot Seat: Architecture Review for Member Client Pipelines',
            'post_body' => 'Post your automation architecture diagrams and webhook data flows here for community peer review before deploying to client production.',
            'course' => [
                'title' => 'Enterprise AI Automations Blueprint',
                'description' => 'From initial discovery calls to deploying multi-agent resilient workflows for enterprise clients using Make.com, n8n, webhooks, and structured LLM extraction.',
                'sections' => [
                    [
                        'title' => 'Phase 1: Architecture, Scoping & Webhook Security',
                        'lessons' => [
                            [
                                'title' => 'Client Scoping, Process Discovery & Data Flow Mapping',
                                'is_practical' => true,
                                'skill_label' => 'Enterprise Process Discovery & Mermaid Data Flow Mapping',
                                'objective' => 'Conduct an operational bottleneck audit, calculate client ROI, and construct an unambiguous Mermaid Data Flow Diagram (DFD).',
                                'explanation' => 'Automating an inefficient process only produces automated waste. Process discovery audits human touchpoints, error rates, and turnaround latency. Mapping workflows into explicit Data Flow Diagrams isolates trigger events, data transformations, conditional routing, and failure states prior to writing integration code.',
                                'example' => "sequenceDiagram\n    autonumber\n    participant Client as Lead / Customer\n    participant Webhook as Webhook Listener\n    participant AI as LLM Parser (GPT-4o)\n    participant CRM as HubSpot CRM\n    participant Team as Slack Alerts\n\n    Client->>Webhook: Submit inquiry form (JSON)\n    Webhook->>AI: Extract budget, urgency, intent\n    alt Valid Enterprise Lead (Budget >= $5k)\n        AI->>CRM: Create Deal & Company Contact\n        CRM->>Team: Dispatch VIP alert with brief summary\n    else General or Low-Budget Lead\n        AI->>CRM: Save to nurture email sequence\n    end",
                                'practice' => "1. Read the case study of a logistics company receiving unstructured freight orders via email.\n2. Construct a complete Mermaid sequence diagram representing: Email Webhook -> Structured Extraction -> Inventory Database Query -> Dispatch Decision -> Slack Alert.\n3. Include an explicit error-handling branch for out-of-stock items.\n4. Submit your Mermaid diagram code.",
                                'expected_outcome' => 'A syntactically valid Mermaid diagram rendering trigger, transformation, decision branches, and error fallback states cleanly.',
                                'source_reference' => 'Enterprise Integration Patterns (Gregor Hohpe & Bobby Woolf), BPMN 2.0 Business Process Model and Notation Specification.',
                            ],
                            [
                                'title' => 'Webhook Architecture, Security & Payload Normalization',
                                'is_practical' => false,
                                'skill_label' => null,
                                'objective' => 'Implement secure webhook ingestion with HMAC-SHA256 signature verification, idempotency keys, and payload schema normalization.',
                                'explanation' => 'Public webhook endpoints face spoofing and replay attacks. Providers (Stripe, GitHub, SePay) sign HTTP payloads using a shared secret. The receiver must calculate the HMAC digest and perform a constant-time comparison to prevent timing attacks. Storing an idempotency key (such as transaction ID) in a fast key-value store ensures duplicate webhooks are acknowledged (HTTP 200) without executing duplicate business logic.',
                                'example' => "import hmac\nimport hashlib\n\ndef verify_webhook_signature(payload_bytes: bytes, signature_header: str, secret: str) -> bool:\n    \"\"\"Constant-time HMAC-SHA256 signature verification.\"\"\"\n    expected_signature = hmac.new(\n        key=secret.encode('utf-8'),\n        msg=payload_bytes,\n        digestmod=hashlib.sha256\n    ).hexdigest()\n    return hmac.compare_digest(expected_signature, signature_header)",
                                'practice' => "1. Write a Python or Node.js webhook verification function.\n2. Test the function against a valid payload with matching secret, an altered payload (tampering simulation), and a mismatched secret.\n3. Add an idempotency check checking whether the transaction ID has been processed in the last 24 hours.",
                                'expected_outcome' => 'Function returns True for authentic payloads, False for tampered data, and logs duplicate payloads as idempotent skips.',
                                'source_reference' => 'Stripe Webhook Signature Verification Guide (https://stripe.com/docs/webhooks/signatures), RFC 2104 HMAC Specification.',
                            ],
                        ],
                    ],
                    [
                        'title' => 'Phase 2: Resilient Integrations & Structured LLM Extraction',
                        'lessons' => [
                            [
                                'title' => 'Dead Letter Queues & Exponential Backoff Retries',
                                'is_practical' => true,
                                'skill_label' => 'Fault-Tolerant Pipeline Design with DLQ Fallback',
                                'objective' => 'Design resilient automation pipelines capable of surviving 3rd-party API outages using exponential backoff retries and Dead Letter Queue (DLQ) alerts.',
                                'explanation' => "Third-party APIs periodically experience downtime or rate limiting. A resilient pipeline distinguishes transient errors (HTTP 429, 502, 503) from fatal errors (HTTP 400, 401). Transient errors trigger exponential backoff retries ($2^n \times \text{delay}$). If all retries fail, the original payload and error context are written to a Dead Letter Queue (Airtable / database) and an alert is dispatched, preventing silent data loss.",
                                'example' => "// Standardized Dead Letter Queue Record Schema\n{\n  \"dlq_id\": \"DLQ-20261023-8842\",\n  \"source_pipeline\": \"Customer Onboarding Sync\",\n  \"failed_endpoint\": \"https://api.crm.example/v2/contacts\",\n  \"http_status\": 503,\n  \"retry_attempts\": 3,\n  \"error_message\": \"Service Unavailable: upstream database timeout\",\n  \"raw_payload\": { \"email\": \"lead@example.com\", \"name\": \"Jane Doe\" },\n  \"timestamp\": \"2026-10-23T14:30:00Z\",\n  \"status\": \"PENDING_MANUAL_REVIEW\"\n}",
                                'practice' => "1. Model an automation scenario in Make.com, n8n, or Python that simulates a failing CRM endpoint.\n2. Implement a 3-tier retry policy with delays of 2s, 4s, and 8s.\n3. Configure an error handler that catches terminal failures, captures the raw payload, writes to a DLQ table, and dispatches a notification.",
                                'expected_outcome' => 'Simulated transient failure retries 3 times; upon persistent failure, pipeline writes complete diagnostic JSON to DLQ table with zero data loss.',
                                'source_reference' => 'AWS Architecture: Reliable and Scalable Webhook Delivery with DLQ, Make.com Error Handling Directives.',
                            ],
                            [
                                'title' => 'Structured LLM Extraction & Decision Nodes',
                                'is_practical' => false,
                                'skill_label' => null,
                                'objective' => 'Integrate OpenAI / Claude Structured Outputs (JSON Schema) into automation pipelines to convert unstructured emails into validated database entities.',
                                'explanation' => 'Free-form text responses from LLMs often hallucinate formatting or wrap text in unwanted markdown code blocks. Structured Outputs enforce strict JSON Schema adherence at the token generation level (grammar-based decoding). The model cannot emit tokens that violate the specified schema, guaranteeing 100% parseable JSON without regex hacks.',
                                'example' => "from pydantic import BaseModel, Field\nfrom enum import Enum\n\nclass LeadPriority(str, Enum):\n    HIGH = \"HIGH\"\n    MEDIUM = \"MEDIUM\"\n    LOW = \"LOW\"\n\nclass ExtractedLead(BaseModel):\n    full_name: str\n    company_name: str\n    budget_estimate_usd: float = Field(ge=0)\n    urgency: LeadPriority\n    key_requirements: list[str] = Field(min_length=1)\n    contact_email: str",
                                'practice' => "1. Define a Pydantic schema for extracting customer ticket metadata (sentiment, category, order number, refund requested).\n2. Write a prompt passing an angry customer email to an LLM with structured output enabled.\n3. Validate that edge cases (missing order number, ambiguous sentiment) adhere to the schema defaults.",
                                'expected_outcome' => 'The model returns strictly conforming JSON matching the schema, zero markdown backticks, with correct enumeration and data types.',
                                'source_reference' => 'OpenAI Guide: Structured Outputs (https://platform.openai.com/docs/guides/structured-outputs), Instructor Library Documentation.',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function agenticCatalog(): array
    {
        return [
            'name' => 'Agentic AI for Founders',
            'slug' => 'agentic-ai-founders',
            'description' => "Executive masterclass for founders and engineering leaders deploying autonomous multi-agent pipelines.\nArchitecture patterns, deterministic state machines, tool-calling guardrails, vector memory, and automated regression evaluations.",
            'rules' => "1. Confidentiality: What is shared in this mastermind stays in this mastermind.\n2. Zero tolerance for cold DM pitching or spam.\n3. Share verified production benchmarks and real incident lessons.",
            'intro_video_url' => 'https://www.loom.com/share/916707c037fe4cac91a87060bf03c15b',
            'access_mode' => 'PAID',
            'price_vnd' => 2400000,
            'billing_period' => 'TERM_30_DAYS',
            'post_title' => 'Founder Roundtable: What is your production LLM cost per active workflow?',
            'post_body' => 'Share your architecture benchmarks: model mix (GPT-4o vs Claude 3.5 Sonnet vs open weights), caching strategies, latency percentiles, and unit economics.',
            'course' => [
                'title' => 'Autonomous Agent Systems at Scale',
                'description' => 'Designing multi-agent systems with deterministic guardrails, state machine orchestration, hybrid memory persistence, and automated LLM-as-a-judge evaluation.',
                'sections' => [
                    [
                        'title' => 'Module 1: Agentic Orchestration & Security Guardrails',
                        'lessons' => [
                            [
                                'title' => 'Planner-Worker Architecture & Deterministic State Machines',
                                'is_practical' => true,
                                'skill_label' => 'Deterministic State Machine Agent Orchestration',
                                'objective' => 'Design an autonomous agent system using finite state machines (FSM) to prevent infinite loops, non-deterministic drift, and ungrounded execution.',
                                'explanation' => "Naive ReAct loops where an LLM recursively chooses its next action frequently suffer from infinite loops, runaway API costs, and context drift. The Planner-Worker pattern enforces a strict separation of concerns: an architect model creates a validated step-by-step plan, a worker executes atomic steps, and a deterministic evaluator transitions between finite states ('PLAN', 'EXECUTE', 'VALIDATE', 'COMPLETE', 'ABORT').",
                                'example' => "from enum import Enum\nfrom dataclasses import dataclass\n\nclass AgentState(str, Enum):\n    PLANNING = \"PLANNING\"\n    EXECUTING = \"EXECUTING\"\n    VALIDATING = \"VALIDATING\"\n    COMPLETED = \"COMPLETED\"\n    FAILED = \"FAILED\"\n\n@dataclass\nclass WorkflowContext:\n    task_goal: str\n    current_step: int = 0\n    max_steps: int = 5\n    state: AgentState = AgentState.PLANNING\n    errors: list[str] = None\n\n    def transition(self, next_state: AgentState) -> None:\n        # Deterministic guard against infinite execution\n        if self.current_step >= self.max_steps and next_state == AgentState.EXECUTING:\n            self.state = AgentState.FAILED\n            return\n        self.state = next_state",
                                'practice' => "1. Implement a state machine class in Python with transitions for Planning -> Execution -> Evaluation.\n2. Enforce a guardrail: if the evaluation step fails 3 times consecutively, transition to 'ABORT' with diagnostic context rather than looping endlessly.\n3. Write a test asserting that a persistently failing tool transitions the workflow to 'ABORT' after exactly 3 retries.",
                                'expected_outcome' => 'Test suite confirms state transitions occur deterministically, loop bounds are enforced at max_retries, and state history is preserved.',
                                'source_reference' => 'LangGraph Concepts: State Machine Graphs (https://langchain-ai.github.io/langgraph/), Yao et al., ReAct: Synergizing Reasoning and Acting in Language Models (2022).',
                            ],
                            [
                                'title' => 'Safe Tool-Calling, Schema Validation & Guardrails',
                                'is_practical' => false,
                                'skill_label' => null,
                                'objective' => 'Implement secure tool-calling interfaces with strict argument schema validation, human-in-the-loop approvals, and fixed parameterized read operations.',
                                'explanation' => 'Giving LLM agents execution access to APIs, shell commands, or databases introduces significant security risks (prompt injection, data wiping). Tools must follow the principle of least privilege: strictly validate argument types with Pydantic, expose only fixed parameterized operations using database credentials restricted to required privileges; never rely on keyword matching to validate arbitrary SQL, and require cryptographic tokens or human confirmation for irreversible actions.',
                                'example' => "import sqlite3\n\ndb = sqlite3.connect(\":memory:\")\ndb.execute(\"CREATE TABLE accounts (id INTEGER PRIMARY KEY, balance INTEGER)\")\ndb.execute(\"INSERT INTO accounts VALUES (1, 100)\")\n\ndef account_balance(account_id: int) -> int | None:\n    # Expose one fixed, parameterized read operation, never arbitrary model SQL.\n    if type(account_id) is not int or account_id < 1:\n        raise ValueError(\"account_id must be a positive integer\")\n    row = db.execute(\"SELECT balance FROM accounts WHERE id = ?\", (account_id,)).fetchone()\n    return row[0] if row else None\n\nassert account_balance(1) == 100\ntry:\n    account_balance(\"1; DROP TABLE accounts\")\n    raise AssertionError(\"Expected invalid input rejection\")\nexcept ValueError:\n    pass",
                                'practice' => "1. Create a tool decorator `@guarded_tool(requires_approval=True)`.\n2. Write a tool `execute_db_query` that rejects any query modifying database state.\n3. Implement an approval hook that prompts for confirmation before executing any sensitive external action.",
                                'expected_outcome' => 'Attempted write queries raise a `SecurityGuardrailException`; read-only queries execute seamlessly with argument type validation.',
                                'source_reference' => 'OWASP Top 10 for Large Language Model Applications (https://owasp.org/www-project-top-10-for-large-language-model-applications/).',
                            ],
                        ],
                    ],
                    [
                        'title' => 'Module 2: Memory Persistence & Automated Evaluation',
                        'lessons' => [
                            [
                                'title' => 'Short-Term & Long-Term Memory Persistence (Vector + SQL)',
                                'is_practical' => true,
                                'skill_label' => 'Hybrid Agent Memory Persistence Architecture',
                                'objective' => 'Construct a hybrid memory persistence layer combining session working context (sliding window) with long-term semantic retrieval via vector embeddings.',
                                'explanation' => 'Raw chat history rapidly exceeds context windows and degrades reasoning quality. A production memory architecture separates short-term working context (the active scratchpad and last N turns) from long-term episodic memory (stored as vector embeddings in pgvector/Qdrant) and factual metadata (stored in relational SQL tables).',
                                'example' => "# Hybrid Memory Management Pattern\nclass HybridMemory:\n    def __init__(self, max_short_term_tokens: int = 2000):\n        self.short_term_turns: list[dict] = []\n        self.max_tokens = max_short_term_tokens\n\n    def add_turn(self, role: str, content: str) -> None:\n        self.short_term_turns.append({\"role\": role, \"content\": content})\n        self._summarize_if_exceeded()\n\n    def _summarize_if_exceeded(self) -> None:\n        # Compact oldest messages when context budget is reached\n        if len(self.short_term_turns) > 6:\n            # Retain first system prompt and last 4 interactions\n            self.short_term_turns = [self.short_term_turns[0]] + self.short_term_turns[-4:]",
                                'practice' => "1. Implement a hybrid memory class that tracks conversation turns.\n2. Add a summarization trigger that triggers when turn count > 5, replacing early turns with a concise summary string.\n3. Test that important entity facts remain accessible across 10 simulated conversation turns.",
                                'expected_outcome' => 'Short-term token usage remains bounded within budget; key factual attributes persist across turns without hallucination.',
                                'source_reference' => 'MemGPT: Towards LLMs as Operating Systems (Packer et al., 2023), pgvector Extension Guide.',
                            ],
                            [
                                'title' => 'Automated Regression Testing with LLM-as-a-Judge',
                                'is_practical' => false,
                                'skill_label' => null,
                                'objective' => 'Construct continuous integration evaluation suites measuring agent response quality, factual accuracy, and latency budgets using judge models.',
                                'explanation' => 'Software updates to system prompts or underlying LLM weights can cause subtle regressions. Traditional unit tests fail on non-deterministic outputs. LLM-as-a-Judge frameworks evaluate outputs against structured criteria (relevance, faithfulness, tone) using evaluation rubrics, outputting quantitative scores and traceable reasoning.',
                                'example' => "// LLM Evaluation Rubric Output Schema\n{\n  \"test_case_id\": \"TC-ORDER-REFUND-01\",\n  \"metrics\": {\n    \"factual_accuracy\": 5,\n    \"policy_compliance\": 5,\n    \"tone_empathy\": 4,\n    \"hallucination_detected\": false\n  },\n  \"overall_pass\": true,\n  \"reasoning\": \"The agent verified the order number, correctly cited the 30-day refund policy window, and did not make promises outside company policy.\"\n}",
                                'practice' => "1. Formulate a 4-point evaluation rubric for an agent task.\n2. Write a test harness script that runs 3 test inputs through an agent, evaluates outputs with a judge model, and compiles scores into a markdown report.\n3. Configure the test to fail if average score drops below 4.0 / 5.0.",
                                'expected_outcome' => 'Evaluation runner outputs a structured report with numerical metrics, detailed reasoning, and a binary pass/fail CI verdict.',
                                'source_reference' => 'G-Eval: NLG Evaluation using GPT-4 with Better Human Alignment (Liu et al., 2023).',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function tattooCatalog(): array
    {
        return [
            'name' => 'Tattoo Masterclass Incubator',
            'slug' => 'tattoo-masterclass',
            'description' => "Professional tattoo artistry and studio incubator developed by master tattooers.\nMaster bloodborne pathogen protocols, needle tapers, machine stroke geometry, skin penetration dynamics, and line precision.",
            'rules' => "1. Hygiene and bloodborne pathogen protocols are strictly enforced.\n2. Respect flash art ownership and original designers.\n3. Constructive mentorship critiques only.",
            'intro_video_url' => 'https://www.loom.com/share/0eea2204985144679429cc8ac0508363',
            'access_mode' => 'PAID',
            'price_vnd' => 980000,
            'billing_period' => 'TERM_30_DAYS',
            'post_title' => 'Apprentice Portfolio Review & Flash Critique',
            'post_body' => 'Upload high-resolution macro photos of your practice skin work or healed client pieces for master instructor feedback. Include needle grouping and machine voltage used.',
            'course' => [
                'title' => 'Precision Tattooing: Needle Theory & Skin Dynamics',
                'description' => 'Complete curriculum from medical-grade workstation setup to intricate fine-line geometry, needle depth control, and skin layer biomechanics.',
                'sections' => [
                    [
                        'title' => 'Module 1: Sanitation Protocols & Equipment Engineering',
                        'lessons' => [
                            [
                                'title' => 'Bloodborne Pathogens, Sterilization & Station Setup',
                                'is_practical' => true,
                                'skill_label' => 'OSHA-Compliant Medical Workstation Setup & Barrier Protocol',
                                'objective' => 'Establish an OSHA-compliant, sterile workstation to eliminate cross-contamination risks and handle contaminated sharps safely.',
                                'explanation' => 'Tattooing involves micro-punctures through the epidermis, creating exposure risks to bloodborne pathogens (Hepatitis B, Hepatitis C, HIV). Hospital-grade intermediate disinfectants with tuberculocidal claims (Cavicide) must be used. All touched surfaces (clip cord, machine body, power knob, squeeze bottles) require non-permeable barrier film wrapping, removed and disinfected in a strict sequence after every client session.',
                                'example' => "[Workstation Setup Sequence Checklist]\n1. Hand Hygiene: 20-second antiseptic hand wash up to forearms.\n2. Surface Disinfection: Spray Cavicide on Mayo stand, wait 3 minutes wet dwell time, wipe dry with single-use paper towel.\n3. Barrier Application: Cover Mayo stand with plastic dental bib (waterproof side down).\n4. Machine & Cord: Slide machine bag over tattoo machine; slide clip cord sleeve over clip cord; secure with cohesive bandage.\n5. Ink & Consumables: Pour ink caps using single-use tongue depressor with medical-grade petroleum jelly anchor. Never touch bottle tip to cap.",
                                'practice' => "1. Draft a complete Standard Operating Procedure (SOP) checklist for opening, setting up, and breaking down a tattoo station.\n2. Detail the exact order of PPE removal to prevent self-contamination.\n3. Specify contaminated sharps disposal protocols according to biohazard regulations.",
                                'expected_outcome' => 'An unambiguous SOP document covering hand sanitation, barrier wrapping, chemical contact times, PPE disposal, and biohazard sharps handling.',
                                'source_reference' => 'OSHA Bloodborne Pathogens Standard 29 CFR 1910.1030, Alliance of Professional Tattooists (APT) Safety Guidelines.',
                            ],
                            [
                                'title' => 'Needle Tapers, Gauges, Groupings & Machine Stroke',
                                'is_practical' => false,
                                'skill_label' => null,
                                'objective' => 'Select appropriate needle groupings (RL, RS, M1) and machine stroke lengths (2.5mm–4.2mm) tailored to specific line weights and skin resistance.',
                                'explanation' => 'Needle cartridges are defined by gauge (diameter, e.g., #08 = 0.25mm, #10 = 0.30mm, #12 = 0.35mm), taper length (short taper 1.5mm vs long taper 5.5mm), and grouping geometry. Machine stroke determines how far the needle bar moves forward and retracts per cycle. Short strokes (2.5mm) excel at soft black-and-grey shading; medium strokes (3.5mm) serve as all-around workhorses; long strokes (4.0mm+) deliver the mechanical force required to push bold liners into tough skin.',
                                'example' => "[Needle & Stroke Selection Chart]\n- Fine Line Detail (Script, Micro-Realism): 1RL or 3RL #10 Long Taper, 3.0mm stroke, 6.2 - 6.8 Volts, medium hand speed.\n- Bold Traditional Outline: 9RL or 11RL #12 Medium Taper, 4.0mm stroke, 7.5 - 8.2 Volts, steady hand speed.\n- Smooth Realism Shading: 15 Curved Magnum (15CM) #10 Long Taper, 3.5mm stroke, 6.5 - 7.0 Volts, rapid pendulum motion.",
                                'practice' => "1. Given a tattoo design containing single-needle micro-details, a bold 9RL outer outline, and soft gradient shading.\n2. Specify the exact needle configuration, machine stroke length, and estimated voltage range for each phase.\n3. Explain the physics of why a long stroke is required for bold multi-needle groupings.",
                                'expected_outcome' => 'Specifications accurately match needle groupings to stroke lengths and voltage, with correct mechanical justifications for skin penetration resistance.',
                                'source_reference' => 'Basic Fundamentals of Modern Tattooing (C.R. Jordan), Rotary Machine Stroke Geometry Engineering Manuals.',
                            ],
                        ],
                    ],
                    [
                        'title' => 'Module 2: Biomechanics, Line Precision & Healed Results',
                        'lessons' => [
                            [
                                'title' => 'Depth Control, Hand Speed Synchronization & Blowout Prevention',
                                'is_practical' => true,
                                'skill_label' => 'Needle Depth Control & Blowout Prevention on Practice Skin',
                                'objective' => 'Calibrate needle penetration strictly to the upper papillary dermis (1.5mm–2.0mm), synchronizing hand speed with machine frequency to eliminate blowouts.',
                                'explanation' => 'Human skin consists of the outer epidermis (sheds continuously), the dermis (holds pigment permanently), and the subcutaneous fat layer. An ink blowout occurs when a needle penetrates too deeply into subcutaneous fat, allowing liquid pigment to diffuse along fat cells into an unsightly blurry halo. Controlling penetration depth through tactile resistance and maintaining needle hang (running the tube off the needle tip) prevents over-penetration.',
                                'example' => "[Anatomy of Needle Depth]\n[Epidermis: 0.1 - 0.5mm] -> Needle passes through.\n[Dermis (Target): 1.5 - 2.0mm] -> Pigment particles captured by fibroblasts and macrophages. Crisp healed line.\n[Subcutaneous Fat: > 2.5mm] -> DANGER ZONE. Liquid pigment diffuses freely along adipose tissue = BLOWOUT.\n\n[Synchronization Formula]\nIf Machine Speed is high (8V = ~120 Hz) and Hand Speed is too slow -> Needle punches excessive ink into the same pore -> Skin trauma and blowouts.\nIdeal synchronization: Consistent needle strike spacing of 0.2mm per machine cycle.",
                                'practice' => "1. On synthetic practice skin, execute 10 parallel 50mm lines using a 5RL needle.\n2. Vary machine voltage (6.5V vs 7.5V vs 8.5V) while maintaining a steady hand speed to observe stroke density.\n3. Cut the synthetic skin horizontally with a razor blade to inspect cross-sectional ink depth consistency.\n4. Document line crispness and depth measurements.",
                                'expected_outcome' => 'Lines exhibit uniform 1.5mm–2.0mm depth across the cross-section, zero cutting or tearing of the synthetic surface, and no ragged edges.',
                                'source_reference' => 'Dermatological Anatomy for Tattoo Artists, Fitzpatrick Skin Type Scale and Tissue Resistance Studies.',
                            ],
                            [
                                'title' => 'Multi-Plane Skin Tensioning & Healed Aftercare Science',
                                'is_practical' => false,
                                'skill_label' => null,
                                'objective' => 'Execute 3-point skin stretching across curved anatomical planes and implement medical polyurethane dermal bandage protocols for optimal healing.',
                                'explanation' => 'Needles bounce or snag on loose, unstretched skin. The 3-point stretch uses the thumb and index finger of the non-dominant hand alongside the pinky finger of the machine hand to create a taut, drum-like surface. Modern aftercare utilizes semi-permeable polyurethane films (second skin) that lock in natural cellular fluids while allowing oxygen exchange, preventing scab formation and pigment shedding.',
                                'example' => "[Comparison of Healing Protocols]\n- Wet Healing (Dermal Bandage / Saniderm):\n  Leave first bandage 24 hours -> wash gently with unscented foam soap -> apply second bandage for 4-5 days -> skin regenerates in sterile plasma environment with zero scab formation -> 95%+ pigment retention.\n\n- Dry Healing (Traditional Plastic Wrap):\n  Wrap removed after 3 hours -> frequent washing and ointment -> scabs form as plasma dries -> risk of premature scab pulling and ink fallout -> variable healed saturation.",
                                'practice' => "1. Write a professional client aftercare instruction sheet explaining the 2-phase dermal bandage protocol.\n2. Detail emergency instructions for allergic reactions to medical adhesive or signs of infection (erythema, excessive heat, purulent discharge).\n3. Outline sun protection protocols (SPF 50 mineral sunscreen) for long-term pigment preservation.",
                                'expected_outcome' => 'A clear, medically accurate client aftercare guide preventing common complications and maximizing pigment retention.',
                                'source_reference' => 'British Journal of Dermatology: Clinical Evaluation of Tattoo Wound Healing and Protective Film Dressings.',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
