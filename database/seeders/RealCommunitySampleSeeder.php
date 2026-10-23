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
     * Run the database seeds for real crawled communities.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new LogicException('Real community sample content may only be seeded in local or testing environments.');
        }

        $creator = User::query()->where('email', 'creator@scool.local')->firstOrFail();
        $member = User::query()->where('email', 'member@scool.local')->firstOrFail();

        $communitiesData = [
            [
                'name' => 'Python Developers',
                'slug' => 'python-developers',
                'description' => "Welcome to Python Developers! A collaborative learning community for Python engineers, data analysts, and automation developers.\nLearn syntax fundamentals, OOP, FastAPI, web scraping, and production automation workflows.",
                'rules' => "1. Be kind, constructive, and helpful to beginners.\n2. When asking questions, provide minimal reproducible code examples.\n3. Format your code properly with markdown syntax.\n4. No low-effort self-promotion or affiliate spam.\n5. Keep debates focused on technical merit and facts.",
                'intro_video_url' => 'https://www.youtube.com/watch?v=0YQVadm4V-Q',
                'access_mode' => 'FREE',
                'price_vnd' => null,
                'billing_period' => 'ONE_TIME',
                'course_title' => 'Python Zero to Production',
                'course_description' => 'Comprehensive roadmap to becoming an autonomous Python engineer.',
                'lessons' => [
                    [
                        'section' => 'Module 1: Core Python Foundations',
                        'title' => 'Environment Setup & Virtual Environments',
                        'content' => "Master uv, poetry, and venv for modern Python project isolation.\nUnderstand dependency resolution and lock files.",
                    ],
                    [
                        'section' => 'Module 1: Core Python Foundations',
                        'title' => 'Data Structures & List Comprehensions',
                        'content' => 'Deep dive into dicts, sets, tuples, and high-performance list/dict comprehensions in Python 3.12+.',
                    ],
                    [
                        'section' => 'Module 2: Automation & APIs',
                        'title' => 'Building Production APIs with FastAPI',
                        'content' => 'Pydantic validation, dependency injection, async request handling, and OpenAPI documentation.',
                    ],
                ],
                'post_title' => 'Welcome to Python Developers! Start here and introduce yourself',
                'post_body' => 'Welcome to the community! Please drop a quick introduction below: what Python projects are you building, what stack do you use, and what are your goals for this month?',
            ],
            [
                'name' => 'AI Video Bootcamp',
                'slug' => 'ai-video-bootcamp',
                'description' => "Master generative AI video workflows and cinematic storytelling.\nLearn Runway Gen-3, Kling AI, Midjourney v6, Luma Dream Machine, and Adobe Premiere compositing to create viral visual content.",
                'rules' => "1. Respect intellectual property and original artists.\n2. Always share the prompts or key techniques when sharing experiments.\n3. Give actionable, constructive critiques.\n4. No spamming referral or promo links.",
                'intro_video_url' => 'https://www.loom.com/share/e0838a4ac97647beb59ed491e6407579',
                'access_mode' => 'PAID',
                'price_vnd' => 225000,
                'billing_period' => 'TERM_30_DAYS',
                'course_title' => 'AI Cinematic Filmmaking Masterclass',
                'course_description' => 'Create studio-quality AI short films, commercials, and visual effects.',
                'lessons' => [
                    [
                        'section' => 'Stage 1: Prompt Crafting & Camera Direction',
                        'title' => 'Camera Movements & Lighting Prompts',
                        'content' => 'Master focal lengths, anamorphic lenses, lighting styles, and motion prompts for consistent visual storytelling.',
                    ],
                    [
                        'section' => 'Stage 2: Video Generation Engines',
                        'title' => 'Runway Gen-3 & Kling AI Motion Brush',
                        'content' => 'Controlling physics, camera arcs, character consistency, and lip sync with ElevenLabs.',
                    ],
                ],
                'post_title' => 'Weekly Showcase: Share your 10-second AI Video clips',
                'post_body' => 'Drop your latest AI video generations in the comments! Include the model used (Kling, Runway, or Luma) and your prompt approach.',
            ],
            [
                'name' => 'Maker School: AI Automation',
                'slug' => 'maker-school-ai-automation',
                'description' => "The premier community for building profitable AI automation agencies and autonomous client pipelines.\nCovers Make.com, Zapier, LangChain, OpenAI APIs, and agentic integrations.",
                'rules' => "1. Execution over theory: Focus on shipping working automations.\n2. Maintain complete client privacy: Redact sensitive API keys and PII.\n3. Help fellow builders when you solve a hard bug.\n4. Quality over quantity.",
                'intro_video_url' => 'https://www.loom.com/share/f0c25c3dbfd245499fc7343c01ab5a24',
                'access_mode' => 'PAID',
                'price_vnd' => 4600000,
                'billing_period' => 'TERM_30_DAYS',
                'course_title' => 'Enterprise AI Automations Blueprint',
                'course_description' => 'From initial discovery call to deployed multi-agent workflows for enterprise clients.',
                'lessons' => [
                    [
                        'section' => 'Phase 1: Architecture & Scoping',
                        'title' => 'Client Scoping & Workflow Blueprinting',
                        'content' => 'How to audit client manual tasks, map data flow diagrams, and price based on ROI instead of hours.',
                    ],
                    [
                        'section' => 'Phase 2: Complex Integrations',
                        'title' => 'Webhook Architecture & Error Handling',
                        'content' => 'Building resilient webhook listeners, exponential backoff retries, and dead letter queues in Make and n8n.',
                    ],
                ],
                'post_title' => 'Hot Seat: Reviewing Member Client Pipelines',
                'post_body' => 'Post your automation architecture diagrams here for peer review before pushing to client production.',
            ],
            [
                'name' => 'Agentic AI for Founders',
                'slug' => 'agentic-ai-founders',
                'description' => "Executive masterclass for founders and engineering leaders deploying autonomous agentic workflows.\nArchitecture patterns, tool-calling frameworks, evaluation pipelines, and security guardrails.",
                'rules' => "1. Confidentiality: What is shared in this mastermind stays in this mastermind.\n2. Zero tolerance for cold DM pitching or spam.\n3. Share verified production benchmarks and real incident lessons.",
                'intro_video_url' => 'https://www.loom.com/share/916707c037fe4cac91a87060bf03c15b',
                'access_mode' => 'PAID',
                'price_vnd' => 2400000,
                'billing_period' => 'TERM_30_DAYS',
                'course_title' => 'Autonomous Agent Systems at Scale',
                'course_description' => 'Designing multi-agent teams with deterministic guardrails and memory persistence.',
                'lessons' => [
                    [
                        'section' => 'Foundations: Agent Orchestration',
                        'title' => 'Planner-Worker Architecture & State Machines',
                        'content' => 'Structuring deterministic agent execution loops with state checkpoints and failure recovery.',
                    ],
                    [
                        'section' => 'Evaluation & Production Guardrails',
                        'title' => 'LLM-as-a-Judge & Automated Regression Eval',
                        'content' => 'Continuous benchmark testing for agent drift, latency budgets, and security jailbreak defenses.',
                    ],
                ],
                'post_title' => 'Founder Roundtable: What is your production LLM cost per active user?',
                'post_body' => 'Share your architecture benchmarks: model mix (GPT-4o vs Claude 3.5 Sonnet vs local open weights), caching strategies, and unit economics.',
            ],
            [
                'name' => 'Tattoo Masterclass Incubator',
                'slug' => 'tattoo-masterclass',
                'description' => "Professional tattoo artistry and studio business incubator.\nMaster fine-line technique, needle depth control, skin biomechanics, hygiene standards, and client consultation.",
                'rules' => "1. Hygiene and bloodborne pathogen protocols are strictly enforced.\n2. Respect flash art ownership and original designers.\n3. Constructive mentorship critiques only.",
                'intro_video_url' => 'https://www.loom.com/share/0eea2204985144679429cc8ac0508363',
                'access_mode' => 'PAID',
                'price_vnd' => 980000,
                'billing_period' => 'TERM_30_DAYS',
                'course_title' => 'Precision Tattooing: Needle Theory & Skin Dynamics',
                'course_description' => 'Complete curriculum from sanitation setup to intricate realism and fine line art.',
                'lessons' => [
                    [
                        'section' => 'Core Mechanics',
                        'title' => 'Needle Tapers, Gauges & Voltage Calibration',
                        'content' => 'Understanding needle groupings (RL, RS, M1), machine stroke length, and hand speed synchronization.',
                    ],
                    [
                        'section' => 'Skin Dynamics',
                        'title' => 'Depth Control & Preventing Blowouts',
                        'content' => 'Tactile feedback, skin tensioning techniques, and needle angles across different body placements.',
                    ],
                ],
                'post_title' => 'Apprentice Portfolio Review & Flash Critique',
                'post_body' => 'Upload high-resolution photos of your practice skin work or healed client pieces for instructor feedback.',
            ],
        ];

        foreach ($communitiesData as $data) {
            DB::transaction(function () use ($creator, $member, $data): void {
                $community = Community::query()->where('slug', $data['slug'])->first();

                if ($community !== null && (int) $community->creator_id !== (int) $creator->id) {
                    throw new LogicException("Community slug '{$data['slug']}' is already owned by another user.");
                }

                $community ??= new Community;
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

                // Initialize standard post categories
                PostCategory::initializeDefaultsFor($community);

                // Ensure demo member has active membership
                $membership = CommunityMembership::query()->firstOrNew([
                    'community_id' => $community->id,
                    'user_id' => $member->id,
                ]);
                $membership->forceFill([
                    'status' => 'ACTIVE',
                    'access_kind' => CommunityMembership::ACCESS_LIFETIME,
                    'expires_at' => null,
                ])->save();

                // Setup Course & Lessons
                $course = $community->courses()->where('title', $data['course_title'])->first();
                $course ??= new Course;
                $course->community_id = $community->id;
                $course->forceFill([
                    'title' => $data['course_title'],
                    'description' => $data['course_description'],
                    'access_mode' => Course::ACCESS_MODE_INCLUDED,
                    'price' => null,
                    'status' => 'PUBLISHED',
                    'order' => 1,
                    'published_revision' => max(1, (int) $course->published_revision),
                ])->save();

                // Add sections and lessons
                $sectionsMap = [];
                foreach ($data['lessons'] as $idx => $lessonSpec) {
                    $secTitle = $lessonSpec['section'];
                    if (! isset($sectionsMap[$secTitle])) {
                        $section = $course->sections()->where('title', $secTitle)->first();
                        $section ??= new CourseSection;
                        $section->course_id = $course->id;
                        $section->forceFill([
                            'title' => $secTitle,
                            'order' => count($sectionsMap) + 1,
                        ])->save();
                        $sectionsMap[$secTitle] = $section;
                    }

                    $sec = $sectionsMap[$secTitle];
                    $lesson = $sec->lessons()->where('title', $lessonSpec['title'])->first();
                    $lesson ??= new Lesson;
                    $lesson->course_section_id = $sec->id;
                    $lesson->forceFill([
                        'title' => $lessonSpec['title'],
                        'content' => $lessonSpec['content'],
                        'video_url' => null,
                        'status' => 'PUBLISHED',
                        'order' => $idx + 1,
                    ])->save();
                }

                // Setup Announcement Post
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

                // Add welcome comment from member
                Comment::query()->firstOrCreate(
                    [
                        'post_id' => $post->id,
                        'user_id' => $member->id,
                    ],
                    [
                        'body' => 'Excited to be here! Looking forward to learning and collaborating with everyone.',
                    ]
                );
            }, 3);
        }
    }
}
