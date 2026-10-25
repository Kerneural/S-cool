<?php

namespace Tests\Feature;

use App\Livewire\CommunitySearchTypeahead;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ScopedLiveSearchFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_surface1_communities_list_search_with_word_reordering_and_typo_tolerance(): void
    {
        $user = User::factory()->create();
        $c1 = Community::factory()->create(['name' => 'Docker Mastery Academy', 'status' => 'ACTIVE', 'creator_id' => $user->id]);
        $c2 = Community::factory()->create(['name' => 'Advanced Kubernetes Cluster', 'status' => 'ACTIVE', 'creator_id' => $user->id]);

        $this->actingAs($user);

        // 1. Prefix match
        $resPrefix = $this->get(route('communities.index', ['q' => 'dock']));
        $resPrefix->assertOk();
        $resPrefix->assertSee('Docker Mastery Academy');
        $resPrefix->assertDontSee('Advanced Kubernetes Cluster');

        // 2. Reordered words
        $resReordered = $this->get(route('communities.index', ['q' => 'academy docker']));
        $resReordered->assertOk();
        $resReordered->assertSee('Docker Mastery Academy');

        // 3. Typo transposition: dokcer -> docker
        $resTypo = $this->get(route('communities.index', ['q' => 'dokcer']));
        $resTypo->assertOk();
        $resTypo->assertSee('Docker Mastery Academy');

        // 4. Completely unrelated noise query
        $resUnrelated = $this->get(route('communities.index', ['q' => 'unrelated flutter mobile']));
        $resUnrelated->assertOk();
        $resUnrelated->assertDontSee('Docker Mastery Academy');
    }

    public function test_surface2_classroom_course_search_with_ranking_and_tenant_isolation(): void
    {
        $creator = User::factory()->create();
        $member = User::factory()->create();

        $community1 = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $community2 = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);

        CommunityMembership::factory()->create([
            'community_id' => $community1->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        // In community 1
        $dockerCourse = Course::factory()->create([
            'community_id' => $community1->id,
            'title' => 'Basic Docker Course',
            'status' => 'PUBLISHED',
            'access_mode' => 'INCLUDED',
            'order' => 1,
        ]);

        $pythonCourse = Course::factory()->create([
            'community_id' => $community1->id,
            'title' => 'Python Zero to Production',
            'status' => 'PUBLISHED',
            'access_mode' => 'INCLUDED',
            'order' => 2,
        ]);

        // In community 2 (foreign tenant)
        $foreignCourse = Course::factory()->create([
            'community_id' => $community2->id,
            'title' => 'Basic Docker in Other Community',
            'status' => 'PUBLISHED',
            'access_mode' => 'INCLUDED',
        ]);

        $this->actingAs($member);

        // 1. Search by 'dock'
        $response = $this->get(route('communities.classroom.index', [$community1, 'q' => 'dock']));
        $response->assertOk();
        $response->assertSee('Basic Docker Course');
        $response->assertDontSee('Python Zero to Production');
        $response->assertDontSee('Basic Docker in Other Community');

        // 2. Search by reordered 'docker basic'
        $responseReordered = $this->get(route('communities.classroom.index', [$community1, 'q' => 'docker basic']));
        $responseReordered->assertOk();
        $responseReordered->assertSee('Basic Docker Course');

        // 3. Search by typo 'dokcer'
        $responseTypo = $this->get(route('communities.classroom.index', [$community1, 'q' => 'dokcer']));
        $responseTypo->assertOk();
        $responseTypo->assertSee('Basic Docker Course');

        // 4. Foreign tenant isolation: searching in community 1 never leaks foreignCourse
        $this->assertFalse(str_contains($responseTypo->getContent(), 'Basic Docker in Other Community'));
    }

    public function test_surface3_typeahead_and_full_search_with_exact_ranking_ahead_of_approximate(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);

        // Course 1: exact match for "Docker"
        $exactCourse = Course::factory()->create([
            'community_id' => $community->id,
            'title' => 'Docker',
            'status' => 'PUBLISHED',
            'access_mode' => 'INCLUDED',
        ]);

        // Course 2: approximate/contains match for "Docker"
        $approxCourse = Course::factory()->create([
            'community_id' => $community->id,
            'title' => 'Complete Cloud and Docker Architecture Masterclass',
            'status' => 'PUBLISHED',
            'access_mode' => 'INCLUDED',
        ]);

        $this->actingAs($creator);

        // Livewire Typeahead
        $component = Livewire::test(CommunitySearchTypeahead::class, ['community' => $community])
            ->set('query', 'Docker');

        $component->assertSet('isOpen', true);
        $results = $component->get('results');

        $this->assertNotEmpty($results['courses_lessons']);
        // Exact match should be ranked first
        $this->assertSame('course-'.$exactCourse->id, $results['courses_lessons'][0]['id']);

        // Typo search in Livewire: 'dokcer' should find 'Docker'
        $componentTypo = Livewire::test(CommunitySearchTypeahead::class, ['community' => $community])
            ->set('query', 'dokcer');

        $resultsTypo = $componentTypo->get('results');
        $foundIds = array_column($resultsTypo['courses_lessons'], 'id');
        $this->assertContains('course-'.$exactCourse->id, $foundIds);

        // Full Page Search (/search?type=courses&q=dokcer)
        $fullPageSearch = $this->get(route('communities.search', [$community, 'type' => 'courses', 'q' => 'dokcer']));
        $fullPageSearch->assertOk();
        $fullPageSearch->assertSee('Docker');
    }

    public function test_input_validation_and_escapes_sql_wildcards_without_leakage(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);

        Course::factory()->create([
            'community_id' => $community->id,
            'title' => 'Special 100% Guaranteed Course',
            'status' => 'PUBLISHED',
            'access_mode' => 'INCLUDED',
        ]);

        $this->actingAs($creator);

        // Literal % search does not dump all courses, matches literal %
        $response = $this->get(route('communities.classroom.index', [$community, 'q' => '100%']));
        $response->assertOk();
        $response->assertSee('Special 100% Guaranteed Course');

        // Wildcard '%' alone is too short or treated safely
        $responseWildcard = $this->get(route('communities.classroom.index', [$community, 'q' => '%%%']));
        $responseWildcard->assertOk();
        $responseWildcard->assertDontSee('Special 100% Guaranteed Course');
    }
}
