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

class SearchPrivacyRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_typo_retrieval_matches_a_word_inside_a_title_on_all_surfaces(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $user->id, 'name' => 'Basic Docker Academy']);
        Course::factory()->create(['community_id' => $community->id, 'title' => 'Basic Docker Course', 'status' => 'PUBLISHED']);
        $this->actingAs($user);
        $this->get(route('communities.index', ['q' => 'dokcer']))->assertOk()->assertSee('Basic Docker Academy');
        $this->get(route('communities.classroom.index', [$community, 'q' => 'dokcer']))->assertOk()->assertSee('Basic Docker Course');
        $this->get(route('communities.search', [$community, 'type' => 'courses', 'q' => 'dokcer']))->assertOk()->assertSee('Basic Docker Course');
        Livewire::test(CommunitySearchTypeahead::class, ['community' => $community])
            ->set('query', 'dokcer')->assertSee('Basic Docker Course');
    }

    public function test_locked_description_does_not_influence_classroom_search_results(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->create();
        CommunityMembership::factory()->active()->create(['community_id' => $community->id, 'user_id' => $user->id]);
        Course::factory()->create([
            'community_id' => $community->id, 'title' => 'Paid Catalog Course',
            'description' => 'Secretneedle hidden description', 'status' => 'PUBLISHED',
            'access_mode' => Course::ACCESS_MODE_ONE_TIME, 'price' => 1000,
        ]);
        $this->actingAs($user)->get(route('communities.classroom.index', [$community, 'q' => 'secretneedle']))
            ->assertOk()->assertDontSee('Paid Catalog Course');
        $this->get(route('communities.classroom.index', [$community, 'q' => 'Paid Catalog']))
            ->assertOk()->assertSee('Paid Catalog Course')->assertDontSee('Secretneedle hidden description');
    }
}
