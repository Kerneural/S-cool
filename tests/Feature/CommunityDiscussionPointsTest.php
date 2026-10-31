<?php

namespace Tests\Feature;

use App\Livewire\PostLikeButton;
use App\Models\Comment;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\PointContribution;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Livewire\Livewire;
use Tests\TestCase;

class CommunityDiscussionPointsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * AC-01: Given a member, creating a post and a reply shows Vietnamese confirmation
     * and content appears without reload confusion.
     */
    public function test_ac01_post_and_comment_creation_shows_vietnamese_confirmation(): void
    {
        App::setLocale('vi');

        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($member);

        // Create post
        $postResponse = $this->post(route('communities.posts.store', $community), [
            'title' => 'Thảo luận kiến trúc AI Agent',
            'body' => 'Nội dung chia sẻ về kiến trúc hệ thống và luồng tương tác.',
        ]);

        $postResponse->assertRedirect(route('communities.posts.index', $community));
        $postResponse->assertSessionHas('status', 'post-created');

        // Verify index view renders Vietnamese confirmation
        $feedView = $this->withSession(['status' => 'post-created'])
            ->get(route('communities.posts.index', $community));
        $feedView->assertOk();
        $feedView->assertSee('Bài viết đã được đăng thành công.');
        $feedView->assertSee('Thảo luận kiến trúc AI Agent');

        /** @var Post $createdPost */
        $createdPost = Post::where('title', 'Thảo luận kiến trúc AI Agent')->firstOrFail();

        // Create comment
        $commentResponse = $this->post(route('communities.posts.comments.store', [$community, $createdPost]), [
            'body' => 'Bình luận phản hồi thảo luận rất hữu ích!',
        ]);

        $commentResponse->assertRedirect(route('communities.posts.show', [$community, $createdPost]));
        $commentResponse->assertSessionHas('status', 'comment-created');

        // Verify show view renders Vietnamese confirmation
        $postDetailView = $this->withSession(['status' => 'comment-created'])
            ->get(route('communities.posts.show', [$community, $createdPost]));
        $postDetailView->assertOk();
        $postDetailView->assertSee('Bình luận đã được đăng thành công.');
        $postDetailView->assertSee('Bình luận phản hồi thảo luận rất hữu ích!');
    }

    /**
     * AC-02: Another member cannot edit or delete someone else's content; authorized moderation works.
     */
    public function test_ac02_authorization_boundaries_and_creator_moderation(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);

        $author = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $author->id,
            'status' => 'ACTIVE',
        ]);

        $otherMember = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $otherMember->id,
            'status' => 'ACTIVE',
        ]);

        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $author->id,
            'title' => 'Bài viết của tác giả',
            'body' => 'Nội dung nguyên bản',
        ]);

        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $author->id,
            'body' => 'Bình luận của tác giả',
        ]);

        // Unauthorized member cannot update or delete post (fail-closed denyAsNotFound)
        $this->actingAs($otherMember);
        $this->put(route('communities.posts.update', [$community, $post]), [
            'title' => 'Cố gắng sửa bài viết',
            'body' => 'Nội dung bị sửa trái phép',
        ])->assertNotFound();

        $this->delete(route('communities.posts.destroy', [$community, $post]))
            ->assertNotFound();

        // Unauthorized member cannot update or delete comment (fail-closed denyAsNotFound)
        $this->put(route('communities.posts.comments.update', [$community, $post, $comment]), [
            'body' => 'Cố gắng sửa bình luận',
        ])->assertNotFound();

        $this->delete(route('communities.posts.comments.destroy', [$community, $post, $comment]))
            ->assertNotFound();

        // Creator (Moderator) can delete comment and post
        $this->actingAs($creator);
        $deleteCommentResponse = $this->delete(route('communities.posts.comments.destroy', [$community, $post, $comment]));
        $deleteCommentResponse->assertRedirect(route('communities.posts.show', [$community, $post]));
        $this->assertSoftDeleted('comments', ['id' => $comment->id]);

        $deletePostResponse = $this->delete(route('communities.posts.destroy', [$community, $post]));
        $deletePostResponse->assertRedirect(route('communities.posts.index', $community));
        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    /**
     * AC-03: Repeated like/unlike follows the ledger without duplicate points or cross-community scoring.
     */
    public function test_ac03_repeated_like_unlike_idempotency_and_no_self_like(): void
    {
        App::setLocale('vi');

        $creator = User::factory()->create();
        $communityA = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $communityB = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);

        $author = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $communityA->id,
            'user_id' => $author->id,
            'status' => 'ACTIVE',
        ]);

        $liker = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $communityA->id,
            'user_id' => $liker->id,
            'status' => 'ACTIVE',
        ]);

        $post = Post::factory()->create([
            'community_id' => $communityA->id,
            'user_id' => $author->id,
        ]);

        // Self-like is prevented via HTTP controller with Vietnamese error
        $this->actingAs($author);
        $selfLikeResponse = $this->post(route('communities.posts.like', [$communityA, $post]));
        $selfLikeResponse->assertSessionHasErrors(['like' => 'Bạn không thể tự thích bài viết của chính mình.']);
        $this->assertEquals(0, PointContribution::getUserPoints($communityA, $author, 'lifetime'));

        // Self-like is prevented via Livewire PostLikeButton
        Livewire::actingAs($author)
            ->test(PostLikeButton::class, ['community' => $communityA, 'post' => $post])
            ->call('toggleLike')
            ->assertSet('errorMessage', 'Bạn không thể tự thích bài viết của chính mình.');

        // Liker likes author's post
        $this->actingAs($liker);
        Livewire::actingAs($liker)
            ->test(PostLikeButton::class, ['community' => $communityA, 'post' => $post])
            ->call('toggleLike')
            ->assertSet('isLiked', true)
            ->assertSet('likesCount', 1);

        $this->assertEquals(1, PointContribution::getUserPoints($communityA, $author, 'lifetime'));
        $this->assertEquals(0, PointContribution::getUserPoints($communityA, $liker, 'lifetime'));
        $this->assertEquals(0, PointContribution::getUserPoints($communityB, $author, 'lifetime'));

        // Liker unlikes
        Livewire::actingAs($liker)
            ->test(PostLikeButton::class, ['community' => $communityA, 'post' => $post])
            ->call('toggleLike')
            ->assertSet('isLiked', false)
            ->assertSet('likesCount', 0);

        $this->assertEquals(0, PointContribution::getUserPoints($communityA, $author, 'lifetime'));

        // Liker re-likes: exactly 1 point, no duplicate
        Livewire::actingAs($liker)
            ->test(PostLikeButton::class, ['community' => $communityA, 'post' => $post])
            ->call('toggleLike')
            ->assertSet('isLiked', true)
            ->assertSet('likesCount', 1);

        $this->assertEquals(1, PointContribution::getUserPoints($communityA, $author, 'lifetime'));
        $this->assertEquals(1, PointContribution::where('community_id', $communityA->id)->where('is_active', true)->count());
    }

    /**
     * AC-04: The leaderboard shows the real rules and level thresholds; no placeholder values.
     */
    public function test_ac04_leaderboard_shows_real_5_levels_and_scoring_clarity_no_placeholders(): void
    {
        App::setLocale('vi');

        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);

        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($member);

        $response = $this->get(route('communities.leaderboard', $community));
        $response->assertOk();

        // Must display 5 real levels with thresholds
        $response->assertSee('Cấp 1 - Thành viên mới');
        $response->assertSee('Cấp 2 - Thành viên tích cực');
        $response->assertSee('Cấp 3 - Thành viên nòng cốt');
        $response->assertSee('Cấp 4 - Dẫn dắt cộng đồng');
        $response->assertSee('Cấp 5 - Bậc thầy cộng đồng');

        // Point calculation card clarity
        $response->assertSee('Quy tắc tính điểm & Cấp độ');
        $response->assertSee('Nhận +1 điểm khi thành viên khác thích bài viết hoặc bình luận của bạn.');
        $response->assertSee('Không tính điểm khi tự thích nội dung của chính mình. Bỏ thích sẽ trừ điểm.');

        // NO placeholder values or fake 9-level strings
        $response->assertDontSee('Not measured');
        $response->assertDontSee('Distribution not measured');
        $response->assertDontSee('AI Explorer');
        $response->assertDontSee('AI Grandmaster');
        $response->assertDontSee('AI Visionary');
    }

    /**
     * AC-05: Feed, members, leaderboard and events are fully Vietnamese.
     */
    public function test_ac05_feed_members_leaderboard_and_events_are_fully_vietnamese(): void
    {
        App::setLocale('vi');

        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);

        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($member);

        // Feed view in Vietnamese
        $feedResponse = $this->get(route('communities.posts.index', $community));
        $feedResponse->assertOk();
        $feedResponse->assertSee('Cộng Đồng');
        $feedResponse->assertSee('Lọc:');
        $feedResponse->assertSee('Tất cả');
        $feedResponse->assertSee('Viết bài');

        // Members directory in Vietnamese
        $directoryResponse = $this->get(route('communities.directory.index', $community));
        $directoryResponse->assertOk();
        $directoryResponse->assertSee('Thành viên cộng đồng');
        $directoryResponse->assertSee('Tìm kiếm thành viên theo tên');

        // Leaderboard view in Vietnamese
        $leaderboardResponse = $this->get(route('communities.leaderboard', $community));
        $leaderboardResponse->assertOk();
        $leaderboardResponse->assertSee('Bảng xếp hạng');
        $leaderboardResponse->assertSee('Điểm tương tác');
        $leaderboardResponse->assertSee('Toàn thời gian');
        $leaderboardResponse->assertSee('Bảng xếp hạng & Cách tính điểm');

        // Events view in Vietnamese
        $eventsResponse = $this->get(route('communities.events.index', $community));
        $eventsResponse->assertOk();
        $eventsResponse->assertSee('Múi giờ hiển thị');
        $eventsResponse->assertSee('Tháng');
        $eventsResponse->assertSee('Danh sách');
    }
}
