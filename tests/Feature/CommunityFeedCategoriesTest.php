<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityFeedCategoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_categories_seeded_and_posts_filter_by_category_and_uncategorized(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'slug' => 'science-hub',
            'status' => 'ACTIVE',
        ]);

        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        // Default categories seeded: General, Announcements, Questions, Introductions
        PostCategory::seedDefaultsFor($community);
        $questionsCategory = $community->categories()->where('slug', 'questions')->firstOrFail();

        // 1. Post in Questions category
        $questionsPost = Post::factory()->create([
            'community_id' => $community->id,
            'category_id' => $questionsCategory->id,
            'user_id' => $member->id,
            'title' => 'How does quantum entanglement work?',
            'body' => 'Need explanation on bell state.',
        ]);

        // 2. Uncategorized post (e.g. legacy post)
        $legacyPost = Post::factory()->create([
            'community_id' => $community->id,
            'category_id' => null,
            'user_id' => $creator->id,
            'title' => 'Old Legacy Notice without category',
            'body' => 'Notice body content.',
        ]);

        $this->actingAs($member);

        // All filter shows both
        $allResponse = $this->get(route('communities.posts.index', $community));
        $allResponse->assertOk()
            ->assertSee('How does quantum entanglement work?')
            ->assertSee('Old Legacy Notice without category');

        // Questions filter shows only Questions post
        $questionsResponse = $this->get(route('communities.posts.index', [$community, 'category' => 'questions']));
        $questionsResponse->assertOk()
            ->assertSee('How does quantum entanglement work?')
            ->assertDontSee('Old Legacy Notice without category');

        // Uncategorized filter shows only legacy post
        $uncategorizedResponse = $this->get(route('communities.posts.index', [$community, 'category' => 'uncategorized']));
        $uncategorizedResponse->assertOk()
            ->assertSee('Old Legacy Notice without category')
            ->assertDontSee('How does quantum entanglement work?');
    }

    public function test_cross_tenant_category_rejected_and_non_creator_cannot_post_announcements_or_pin(): void
    {
        $creator = User::factory()->create();
        $communityA = Community::factory()->create(['creator_id' => $creator->id, 'slug' => 'comm-a', 'status' => 'ACTIVE']);
        $communityB = Community::factory()->create(['creator_id' => $creator->id, 'slug' => 'comm-b', 'status' => 'ACTIVE']);

        $member = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $communityA->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);

        PostCategory::seedDefaultsFor($communityA);
        PostCategory::seedDefaultsFor($communityB);

        $catB = $communityB->categories()->where('slug', 'general')->firstOrFail();
        $announcementsA = $communityA->categories()->where('slug', 'announcements')->firstOrFail();

        $this->actingAs($member);

        // 1. Cross-tenant category submission (B's category in A's feed) -> Rejected with validation error
        $crossTenantResponse = $this->post(route('communities.posts.store', $communityA), [
            'title' => 'Cross-tenant attempt',
            'body' => 'Should fail',
            'category_id' => $catB->id,
        ]);
        $crossTenantResponse->assertSessionHasErrors('category_id');
        $this->assertDatabaseMissing('posts', ['title' => 'Cross-tenant attempt']);

        // 2. Member attempts to post in Announcements -> Rejected
        $announcementAttempt = $this->post(route('communities.posts.store', $communityA), [
            'title' => 'Fake Announcement',
            'body' => 'Member trying to announce',
            'category_id' => $announcementsA->id,
        ]);
        $announcementAttempt->assertSessionHasErrors('category_id');
        $this->assertDatabaseMissing('posts', ['title' => 'Fake Announcement']);

        // 3. Creator CAN post in Announcements
        $this->actingAs($creator);
        $creatorAnnouncement = $this->post(route('communities.posts.store', $communityA), [
            'title' => 'Official Creator Announcement',
            'body' => 'Welcome all members.',
            'category_id' => $announcementsA->id,
        ]);
        $creatorAnnouncement->assertSessionHasNoErrors();
        $this->assertDatabaseHas('posts', [
            'title' => 'Official Creator Announcement',
            'category_id' => $announcementsA->id,
        ]);

        // 4. Non-creator member cannot pin or unpin
        $post = Post::where('title', 'Official Creator Announcement')->firstOrFail();

        $this->actingAs($member);
        $this->post(route('communities.posts.pin', [$communityA, $post]))
            ->assertForbidden();

        $this->post(route('communities.posts.unpin', [$communityA, $post]))
            ->assertForbidden();
    }

    public function test_pin_and_unpin_is_idempotent_and_pinned_posts_appear_first(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'slug' => 'pin-hub', 'status' => 'ACTIVE']);

        $normalPost = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
            'title' => 'Normal Post Newer',
            'created_at' => now()->addMinutes(10),
        ]);

        $pinnedPost = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
            'title' => 'Important Pinned Post Older',
            'created_at' => now()->subDays(2),
        ]);

        $this->actingAs($creator);

        // 1. Pin post
        $pinResponse = $this->post(route('communities.posts.pin', [$community, $pinnedPost]));
        $pinResponse->assertSessionHasNoErrors();

        $pinnedPost->refresh();
        $this->assertTrue($pinnedPost->isPinned());
        $firstPinnedTime = $pinnedPost->pinned_at;

        // Repeated pin (idempotency: does not overwrite existing pinned_at)
        $this->post(route('communities.posts.pin', [$community, $pinnedPost]));
        $pinnedPost->refresh();
        $this->assertSame($firstPinnedTime->toDateTimeString(), $pinnedPost->pinned_at->toDateTimeString());

        // Feed order: pinned post appears before normal newer post
        $feedResponse = $this->get(route('communities.posts.index', $community));
        $feedResponse->assertOk();

        $feedPosts = $feedResponse->viewData('posts')->items();
        $this->assertSame($pinnedPost->id, $feedPosts[0]->id);
        $this->assertSame($normalPost->id, $feedPosts[1]->id);

        // 2. Unpin post
        $this->post(route('communities.posts.unpin', [$community, $pinnedPost]));
        $pinnedPost->refresh();
        $this->assertFalse($pinnedPost->isPinned());

        // Repeated unpin is idempotent
        $this->post(route('communities.posts.unpin', [$community, $pinnedPost]))
            ->assertSessionHasNoErrors();
    }

    public function test_deleting_category_preserves_posts_and_comments(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'slug' => 'custom-cats', 'status' => 'ACTIVE']);

        // Creator creates custom category
        $this->actingAs($creator);
        $createCatResponse = $this->post(route('communities.categories.store', $community), [
            'name' => 'Book Club',
        ]);
        $createCatResponse->assertSessionHasNoErrors();

        $bookClub = $community->categories()->where('slug', 'book-club')->firstOrFail();

        // Create post and comments in Book Club
        $post = Post::factory()->create([
            'community_id' => $community->id,
            'category_id' => $bookClub->id,
            'user_id' => $creator->id,
            'title' => 'Reading Dune this month',
            'body' => 'Chapter 1 discussion.',
        ]);

        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $creator->id,
            'body' => 'Loved the spice guild lore.',
        ]);

        // Delete category
        $deleteCatResponse = $this->delete(route('communities.categories.destroy', [$community, $bookClub]));
        $deleteCatResponse->assertSessionHasNoErrors();

        // Category is deleted
        $this->assertDatabaseMissing('post_categories', ['id' => $bookClub->id]);

        // Post still exists, category_id became NULL (uncategorized)
        $post->refresh();
        $this->assertNull($post->category_id);
        $this->assertSame('Reading Dune this month', $post->title);

        // Comment still exists
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }

    public function test_sorting_supports_newest_and_most_commented_and_rejects_invalid_inputs(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'slug' => 'sort-hub', 'status' => 'ACTIVE']);

        $postWithFewComments = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
            'title' => 'Recent Post Low Comments',
            'created_at' => now()->addMinutes(5),
        ]);

        $postWithManyComments = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $creator->id,
            'title' => 'Older Post High Comments',
            'created_at' => now()->subDay(),
        ]);
        Comment::factory()->count(3)->create(['post_id' => $postWithManyComments->id, 'user_id' => $creator->id]);

        $this->actingAs($creator);

        // Sort: newest (default)
        $newestResponse = $this->get(route('communities.posts.index', [$community, 'sort' => 'newest']));
        $newestResponse->assertOk();
        $newestPosts = $newestResponse->viewData('posts')->items();
        $this->assertSame($postWithFewComments->id, $newestPosts[0]->id);

        // Sort: most_commented
        $mostCommentedResponse = $this->get(route('communities.posts.index', [$community, 'sort' => 'most_commented']));
        $mostCommentedResponse->assertOk();
        $mostCommentedPosts = $mostCommentedResponse->viewData('posts')->items();
        $this->assertSame($postWithManyComments->id, $mostCommentedPosts[0]->id);

        // Invalid sort parameter -> Validation exception
        $invalidSortResponse = $this->get(route('communities.posts.index', [$community, 'sort' => 'evil_sql_injection']));
        $invalidSortResponse->assertSessionHasErrors('sort');

        // Invalid category slug -> Validation exception
        $invalidCatResponse = $this->get(route('communities.posts.index', [$community, 'category' => 'non_existent_category_slug']));
        $invalidCatResponse->assertSessionHasErrors('category');
    }

    public function test_suspended_or_archived_community_denies_category_and_pin_mutations_with_404(): void
    {
        $creator = User::factory()->create();

        foreach (['SUSPENDED', 'ARCHIVED'] as $status) {
            $community = Community::factory()->create([
                'creator_id' => $creator->id,
                'status' => $status,
            ]);

            PostCategory::seedDefaultsFor($community);
            $category = $community->categories()->firstOrFail();

            $post = Post::factory()->create([
                'community_id' => $community->id,
                'user_id' => $creator->id,
            ]);

            $this->actingAs($creator);

            // 1. Create category in locked community -> 404
            $this->post(route('communities.categories.store', $community), [
                'name' => 'Forbidden Category',
            ])->assertNotFound();

            // 2. Delete category in locked community -> 404
            $this->delete(route('communities.categories.destroy', [$community, $category]))
                ->assertNotFound();

            // 3. Pin post in locked community -> 404
            $this->post(route('communities.posts.pin', [$community, $post]))
                ->assertNotFound();

            // 4. Unpin post in locked community -> 404
            $this->post(route('communities.posts.unpin', [$community, $post]))
                ->assertNotFound();
        }
    }

    public function test_get_feed_is_strictly_read_only_and_does_not_seed_categories_when_empty(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
        ]);

        // Explicitly remove all categories
        $community->categories()->delete();
        $this->assertSame(0, $community->categories()->count());

        $this->actingAs($creator);

        // GET feed request must be strictly read-only
        $response = $this->get(route('communities.posts.index', $community));
        $response->assertOk();

        // Must still be 0, no side-effect writing or auto-seeding occurred
        $this->assertSame(0, $community->categories()->count());
    }

    public function test_creator_sees_category_management_ui_while_non_creator_does_not(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
        ]);
        PostCategory::seedDefaultsFor($community);

        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        // 1. Creator sees category management controls
        $this->actingAs($creator);
        $creatorResponse = $this->get(route('communities.posts.index', $community));
        $creatorResponse->assertOk()
            ->assertSee('Manage Categories')
            ->assertSee('Add Category');

        // 2. Member does NOT see category management controls
        $this->actingAs($member);
        $memberResponse = $this->get(route('communities.posts.index', $community));
        $memberResponse->assertOk()
            ->assertDontSee('Manage Categories')
            ->assertDontSee('Add Category');
    }
}
