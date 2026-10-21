<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\User;
use Database\Seeders\RealCommunitySampleSeeder;
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

        $creator = User::where('email', 'creator@scool.local')->firstOrFail();
        $member = User::where('email', 'member@scool.local')->firstOrFail();

        $expectations = [
            'python-developers' => [
                'name' => 'Python Developers',
                'video_embed' => 'https://www.youtube.com/embed/0YQVadm4V-Q',
                'rule_snippet' => '1. Be kind, constructive, and helpful to beginners.',
            ],
            'ai-video-bootcamp' => [
                'name' => 'AI Video Bootcamp',
                'video_embed' => 'https://www.loom.com/embed/e0838a4ac97647beb59ed491e6407579',
                'rule_snippet' => '1. Respect intellectual property and original artists.',
            ],
            'maker-school-ai-automation' => [
                'name' => 'Maker School: AI Automation',
                'video_embed' => 'https://www.loom.com/embed/f0c25c3dbfd245499fc7343c01ab5a24',
                'rule_snippet' => '1. Execution over theory: Focus on shipping working automations.',
            ],
            'agentic-ai-founders' => [
                'name' => 'Agentic AI for Founders',
                'video_embed' => 'https://www.loom.com/embed/916707c037fe4cac91a87060bf03c15b',
                'rule_snippet' => '1. Confidentiality: What is shared in this mastermind stays in this mastermind.',
            ],
            'tattoo-masterclass' => [
                'name' => 'Tattoo Masterclass Incubator',
                'video_embed' => 'https://www.loom.com/embed/0eea2204985144679429cc8ac0508363',
                'rule_snippet' => '1. Hygiene and bloodborne pathogen protocols are strictly enforced.',
            ],
        ];

        foreach ($expectations as $slug => $data) {
            $community = Community::where('slug', $slug)->firstOrFail();

            // 1. Member views About / Show tab
            $response = $this->actingAs($member)->get(route('communities.show', $community));
            $response->assertOk();
            $response->assertSee($data['name']);
            $response->assertSee($data['rule_snippet']);
            $response->assertSee('src="'.$data['video_embed'].'"', false);

            // 2. Member views Classroom index
            $classroomResponse = $this->actingAs($member)->get(route('communities.classroom.index', $community));
            $classroomResponse->assertOk();
            $classroomResponse->assertSee($community->courses()->first()->title);
        }
    }
}
