<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\User;
use App\Support\LessonMarkdown;
use Database\Seeders\RealCommunitySampleSeeder;
use Database\Seeders\RealFreeCommunitySeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RealCommunityVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_crawled_communities_render_correct_embed_videos_and_rules(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(RealCommunitySampleSeeder::class);
        $this->seed(RealFreeCommunitySeeder::class);

        $creator = User::where('email', 'creator@scool.local')->firstOrFail();
        $member = User::where('email', 'member@scool.local')->firstOrFail();

        $expectations = [
            'python-developers' => [
                'name' => 'Python Developers',
                'video_embed' => 'https://www.youtube.com/embed/0YQVadm4V-Q',
                'rule_snippet' => '1. Be kind, constructive, and helpful to beginners.',
            ],
            'ai-automation-society' => [
                'name' => 'AI Automation Society',
                'video_embed' => 'https://www.loom.com/embed/11479f0c65594512af79a04f5f45672f',
                'rule_snippet' => '1. Be helpful and constructive. No low-effort self-promotion.',
            ],
            'learn-ai' => [
                'name' => 'AI Automation Agency Hub',
                'video_embed' => 'https://www.youtube.com/embed/vFepZE_wrfg',
                'rule_snippet' => '1. Provide real value first before requesting feedback.',
            ],
            'max-business-school' => [
                'name' => 'Max Business School™',
                'video_embed' => 'https://www.youtube.com/embed/cY3aYETEGaI',
                'rule_snippet' => '1. Post in the correct categories.',
            ],
            'the-ai-advantage' => [
                'name' => 'The AI Advantage',
                'video_embed' => 'https://www.youtube.com/embed/956DPSPX4wg',
                'rule_snippet' => '1. Zero hate speech or negative toxicity.',
            ],
            'cliefnotes' => [
                'name' => 'Clief Notes',
                'video_embed' => 'https://www.youtube.com/embed/UDJmO7xyCSo',
                'rule_snippet' => '1. Search before asking common questions.',
            ],
        ];

        foreach ($expectations as $slug => $data) {
            $community = Community::where('slug', $slug)->firstOrFail();

            // Verify Zero-Cost / Free community attributes
            $this->assertSame('FREE', $community->access_mode);
            $this->assertNull($community->price_vnd);

            // 1. Member views About / Show tab
            $response = $this->actingAs($member)->get(route('communities.show', $community));
            $response->assertOk();
            $response->assertSee($data['name']);
            $response->assertSee($data['rule_snippet']);
            $response->assertSee($data['video_embed'], false);

            // 2. Member views Classroom index
            $classroomResponse = $this->actingAs($member)->get(route('communities.classroom.index', $community));
            $classroomResponse->assertOk();
            /** @var Course $firstCourse */
            $firstCourse = $community->courses()->firstOrFail();
            $classroomResponse->assertSee($firstCourse->title);

            // 3. Member views first lesson with secure video player
            /** @var CourseSection $firstSection */
            $firstSection = $firstCourse->sections()->firstOrFail();
            /** @var Lesson $firstLesson */
            $firstLesson = $firstSection->lessons()->firstOrFail();
            $lessonResponse = $this->actingAs($member)->get(route('communities.lessons.show', [
                'community' => $community,
                'course' => $firstCourse,
                'lesson' => $firstLesson,
            ]));
            $lessonResponse->assertOk();
            $lessonResponse->assertSee($firstLesson->title);
            if ($firstLesson->embed_url) {
                $lessonResponse->assertSee('S-cool Protected Stream');
            } else {
                // Lesson Markdown is rendered (not echoed raw): compare the rendered text of its first line.
                $firstLine = str_contains($firstLesson->content, "\n") ? explode("\n", $firstLesson->content)[0] : $firstLesson->content;
                $this->assertStringContainsString(
                    trim(html_entity_decode(strip_tags(LessonMarkdown::toHtml($firstLine)))),
                    html_entity_decode(strip_tags($lessonResponse->getContent())),
                );
            }
        }
    }

    public function test_watch_agentic_ai_lesson_renders_full_content_and_video(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(RealCommunitySampleSeeder::class);
        $this->seed(RealFreeCommunitySeeder::class);

        $member = User::where('email', 'member@scool.local')->firstOrFail();
        $community = Community::where('slug', 'ai-automation-society')->firstOrFail();

        $lesson = Lesson::where('title', 'Watch: Agentic AI')->firstOrFail();
        $section = $lesson->section;
        $course = $section->course;

        $response = $this->actingAs($member)->get(route('communities.lessons.show', [
            'community' => $community,
            'course' => $course,
            'lesson' => $lesson,
        ]));

        $response->assertOk();
        $response->assertSee('Watch: Agentic AI');
        $response->assertSee('S-cool Protected Stream');
        $response->assertSee('The agentic AI market is going from $7 billion to $93 billion in the next few years');
        $response->assertSee('The WAT Framework - the system behind every workflow Nate builds');
        // CommonMark leaves apostrophes unescaped, unlike Blade's e().
        $response->assertSee("In this video, you'll see:", false);
        $response->assertSee('Day 1: First Workflow');
        $response->assertSee('Lesson: The WAT Framework');
        $response->assertSee('Build: Newsletter Automation');
        $response->assertSee('Day 2: Using an MCP Server');
        $response->assertSee('Day 7: Executive Assistant');
    }
}
