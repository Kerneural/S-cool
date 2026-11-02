<?php

namespace Tests\Feature;

use App\Livewire\DiscussionViewer;
use App\Livewire\PostPoll;
use App\Models\Comment;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\SocialAttachment;
use App\Models\User;
use App\Services\SocialLinkPreview;
use App\Support\SocialContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SocialCommunityUsabilityTest extends TestCase
{
    use RefreshDatabase;

    private function member(Community $community): User
    {
        $user = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $user->id, 'status' => 'ACTIVE']);

        return $user;
    }

    public function test_rich_post_persists_media_link_poll_and_preserves_old_plain_content(): void
    {
        Storage::fake('community_media');
        $community = Community::factory()->create(['status' => 'ACTIVE']);
        $member = $this->member($community);
        $old = Post::factory()->create(['community_id' => $community->id, 'body' => '**literal** <hello>']);
        $this->actingAs($member)->postJson(route('communities.posts.store', $community), [
            'title' => 'Planning together', 'body' => '**Hello**', 'content_format' => 'markdown',
            'images' => [UploadedFile::fake()->image('hello.png')],
            'link_url' => 'https://example.com/resource', 'link_title' => 'Resource',
            'video_url' => 'https://youtu.be/dQw4w9WgXcQ',
            'poll_enabled' => true, 'poll_options' => ['Morning', 'Evening'],
        ])->assertCreated()->assertJsonStructure(['redirect']);
        $post = $community->posts()->where('title', 'Planning together')->firstOrFail();
        $this->assertSame('markdown', $post->content_format);
        $this->assertSame(2, $post->poll->options()->count());
        $file = $post->attachments->sole();
        Storage::disk('community_media')->assertExists($file->path);
        $this->get(route('communities.posts.show', [$community, $post]))->assertOk()
            ->assertSee('<strong>Hello</strong>', false)->assertSee('Resource')->assertSee('Morning');
        $this->assertSame('**literal** <hello>', $old->fresh()->body);
        $this->assertSame('plain', $old->fresh()->content_format);
        $this->assertSame('**literal** &lt;hello&gt;', SocialContent::html($old->body, 'plain'));
    }

    public function test_media_denies_foreign_community_revoked_member_and_deleted_parent(): void
    {
        Storage::fake('community_media');
        $community = Community::factory()->create(['status' => 'ACTIVE']);
        $member = $this->member($community);
        $this->actingAs($member)->post(route('communities.posts.store', $community), [
            'title' => 'Photo', 'body' => 'Picture', 'images' => [UploadedFile::fake()->image('photo.png')],
        ])->assertRedirect();
        $file = SocialAttachment::sole();
        $url = route('communities.social-media.show', [$community, $file]);
        $this->get($url)->assertOk()->assertHeader('Cache-Control', 'no-store, private');
        $foreign = Community::factory()->create(['status' => 'ACTIVE']);
        $this->get(route('communities.social-media.show', [$foreign, $file]))->assertNotFound();
        $this->actingAs(User::factory()->create())->get($url)->assertNotFound();
        $community->memberships()->where('user_id', $member->id)->update(['status' => 'SUSPENDED']);
        $this->actingAs($member)->get($url)->assertNotFound();
        $this->actingAs($community->creator)->get($url)->assertOk();
        $file->post->delete();
        $this->get($url)->assertNotFound();
    }

    public function test_invalid_files_links_and_duplicate_choices_leave_no_partial_publication(): void
    {
        Storage::fake('community_media');
        $community = Community::factory()->create(['status' => 'ACTIVE']);
        $base = ['title' => 'Reject', 'body' => 'Text'];
        $this->actingAs($community->creator)->post(route('communities.posts.store', $community), $base + [
            'images' => [UploadedFile::fake()->createWithContent('evil.svg', '<svg onload="alert(1)"/>')],
        ])->assertSessionHasErrors('images.0');
        $this->post(route('communities.posts.store', $community), $base + ['link_url' => 'javascript:alert(1)'])->assertSessionHasErrors('link_url');
        $this->post(route('communities.posts.store', $community), $base + ['poll_enabled' => true, 'poll_options' => ['Yes', 'Yes']])->assertSessionHasErrors();
        $this->assertSame(0, $community->posts()->count());
        $this->assertSame([], Storage::disk('community_media')->allFiles());
    }

    public function test_poll_vote_changes_are_unique_and_closed_foreign_or_revoked_votes_fail(): void
    {
        $community = Community::factory()->create(['status' => 'ACTIVE']);
        $member = $this->member($community);
        $post = Post::factory()->create(['community_id' => $community->id, 'user_id' => $community->creator_id]);
        $poll = $post->poll()->create();
        $one = $poll->options()->create(['label' => 'One', 'position' => 0]);
        $two = $poll->options()->create(['label' => 'Two', 'position' => 1]);
        $url = route('communities.posts.poll.vote', [$community, $post]);
        $this->actingAs($member)->post($url, ['option_id' => $one->id])->assertRedirect();
        $this->post($url, ['option_id' => $two->id])->assertRedirect();
        $this->assertSame(1, $poll->votes()->count());
        $this->assertSame($two->id, $poll->votes()->sole()->post_poll_option_id);
        $otherPost = Post::factory()->create(['community_id' => $community->id]);
        $foreignOption = $otherPost->poll()->create()->options()->create(['label' => 'Foreign', 'position' => 0]);
        $this->post($url, ['option_id' => $foreignOption->id])->assertNotFound();
        $this->post(route('communities.posts.poll.close', [$community, $post]))->assertNotFound();
        $this->actingAs($community->creator)->post(route('communities.posts.poll.close', [$community, $post]))->assertRedirect();
        $this->actingAs($member)->post($url, ['option_id' => $one->id])->assertSessionHasErrors('poll');
        $this->assertSame($two->id, $poll->votes()->sole()->post_poll_option_id);
        $community->memberships()->where('user_id', $member->id)->update(['status' => 'REMOVED']);
        $this->post($url, ['option_id' => $one->id])->assertNotFound();
    }

    public function test_replies_are_one_level_same_post_with_private_comment_media(): void
    {
        Storage::fake('community_media');
        $community = Community::factory()->create(['status' => 'ACTIVE']);
        $member = $this->member($community);
        $post = Post::factory()->create(['community_id' => $community->id]);
        $parent = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $community->creator_id]);
        $url = route('communities.posts.comments.store', [$community, $post]);
        $this->actingAs($member)->post($url, [
            'body' => '**Reply**', 'content_format' => 'markdown', 'parent_id' => $parent->id,
            'images' => [UploadedFile::fake()->image('reply.png')],
        ])->assertRedirect();
        $reply = $post->comments()->where('parent_id', $parent->id)->sole();
        $this->assertSame(1, $reply->attachments()->count());
        $file = $reply->attachments->sole();
        $this->get(route('communities.social-media.show', [$community, $file]))->assertOk();
        $this->post($url, ['body' => 'Too deep', 'parent_id' => $reply->id])->assertSessionHasErrors('parent_id');
        $foreign = Comment::factory()->create();
        $this->post($url, ['body' => 'Wrong post', 'parent_id' => $foreign->id])->assertSessionHasErrors('parent_id');
        $reply->delete();
        $this->get(route('communities.social-media.show', [$community, $file]))->assertNotFound();
    }

    public function test_livewire_comment_renders_immediately_and_retains_draft_on_invalid_media(): void
    {
        Storage::fake('community_media');
        $community = Community::factory()->create(['status' => 'ACTIVE']);
        $member = $this->member($community);
        $post = Post::factory()->create(['community_id' => $community->id]);
        Livewire::actingAs($member)->test(DiscussionViewer::class, ['community' => $community])
            ->call('openPost', $post->id)->set('newCommentBody', '**Hello**')
            ->set('newCommentFormat', 'markdown')->call('addComment')
            ->assertSet('newCommentBody', '')->assertSeeHtml('<strong>Hello</strong>');
        $test = Livewire::actingAs($member)->test(DiscussionViewer::class, ['community' => $community]);
        $test->call('openPost', $post->id)->set('newCommentBody', 'Keep this')
            ->set('commentImages', [UploadedFile::fake()->createWithContent('bad.svg', '<svg/>')])
            ->call('addComment')->assertSet('newCommentBody', 'Keep this');
        $this->assertSame(1, $post->comments()->count());
    }

    public function test_safe_rendering_link_preview_and_author_links_preserve_boundaries(): void
    {
        $this->assertStringNotContainsString('<script', SocialContent::html('<script>bad()</script> **Good**', 'markdown'));
        $this->assertStringNotContainsString('<img', SocialContent::html('![tracker](https://example.com/image.jpg)', 'markdown'));
        $this->assertStringContainsString('href="https://example.com"', SocialContent::html('https://example.com'));
        foreach (['127.0.0.1', '10.0.0.1', '169.254.169.254', '100.64.0.1', '::1'] as $ip) {
            $this->assertFalse(SocialLinkPreview::publicAddress($ip));
        }
        $community = Community::factory()->create(['status' => 'ACTIVE']);
        $post = Post::factory()->create(['community_id' => $community->id, 'user_id' => $community->creator_id, 'link_url' => 'https://example.com']);
        $this->actingAs($community->creator)->get(route('communities.posts.index', $community))->assertOk()
            ->assertSee(route('communities.directory.show', [$community, $community->creator]), false)
            ->assertSee('data-social-composer', false);
        $this->actingAs(User::factory()->create())->get(route('communities.posts.link-preview', [$community, $post]))->assertNotFound();
    }

    public function test_pdf_download_and_explicit_attachment_removal_preserve_other_files(): void
    {
        Storage::fake('community_media');
        $community = Community::factory()->create(['status' => 'ACTIVE']);
        $actor = $community->creator;
        $this->actingAs($actor)->post(route('communities.posts.store', $community), [
            'title' => 'Documents', 'body' => 'Read this',
            'documents' => [UploadedFile::fake()->createWithContent('notes.pdf', "%PDF-1.7\n1 0 obj\n<<>>\nendobj\n%%EOF")],
        ])->assertRedirect();
        $file = SocialAttachment::sole();
        $url = route('communities.social-media.show', [$community, $file]);
        $this->get($url)->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->actingAs($this->member($community))->delete(route('communities.social-media.destroy', [$community, $file]))->assertNotFound();
        $this->actingAs($actor)->delete(route('communities.social-media.destroy', [$community, $file]))->assertRedirect();
        $this->get($url)->assertNotFound();
        $this->assertSame(1, $community->posts()->count());
    }

    public function test_transaction_rejection_discards_staged_uploads_and_keeps_existing_post_unchanged(): void
    {
        Storage::fake('community_media');
        $community = Community::factory()->create(['status' => 'ACTIVE']);
        $post = Post::factory()->create(['community_id' => $community->id, 'user_id' => $community->creator_id]);
        // Validation passes the missing course_id, then the locked learning-context check rejects lesson-only context.
        $this->actingAs($community->creator)->put(route('communities.posts.update', [$community, $post]), [
            'title' => 'Should not save', 'body' => 'New draft', 'course_id' => null, 'lesson_id' => 1,
            'images' => [UploadedFile::fake()->image('draft.png')],
        ])->assertSessionHasErrors('lesson_id');
        $this->assertNotSame('Should not save', $post->fresh()->title);
        $this->assertSame([], Storage::disk('community_media')->allFiles());
    }

    public function test_pasted_links_get_cards_and_rich_comment_edits_keep_their_format(): void
    {
        $community = Community::factory()->create(['status' => 'ACTIVE']);
        $member = $this->member($community);
        $this->actingAs($member)->post(route('communities.posts.store', $community), [
            'title' => 'Useful link', 'body' => 'Read [this resource](https://example.com/docs)', 'content_format' => 'markdown',
        ])->assertRedirect();
        $post = $community->posts()->sole();
        $this->assertSame('https://example.com/docs', $post->link_url);
        $comment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $member->id, 'body' => '**Original**', 'content_format' => 'markdown']);
        Livewire::actingAs($member)->test(DiscussionViewer::class, ['community' => $community, 'postId' => $post->id])
            ->call('editComment', $comment->id)->assertSet('editingCommentFormat', 'markdown')
            ->set('editingCommentBody', '**Edited**')->call('updateComment')->assertSeeHtml('<strong>Edited</strong>');
        $this->assertSame('markdown', $comment->fresh()->content_format);
        $this->assertNull(SocialContent::firstUrl(chr(96).'https://example.com/code'.chr(96), 'markdown'));
    }

    public function test_builtin_categories_are_localized_without_renaming_owner_content_or_slugs(): void
    {
        app()->setLocale('vi');
        $community = Community::factory()->create(['status' => 'ACTIVE']);
        PostCategory::seedDefaultsFor($community);
        $categories = PostCategory::where('community_id', $community->id)->get()->keyBy('slug');
        $this->assertSame('Thảo luận chung', $categories['general']->display_name);
        $this->assertSame('Thông báo', $categories['announcements']->display_name);
        $this->assertSame('Giới thiệu', $categories['introductions']->display_name);
        $categories['general']->update(['name' => 'Owner custom label']);
        $this->assertSame('Owner custom label', $categories['general']->fresh()->display_name);
        $this->assertSame('Announcements', $categories['announcements']->fresh()->name);
        $member = $this->member($community);
        $this->actingAs($member)->get(route('communities.posts.index', $community))
            ->assertOk()->assertSee('Thông báo')->assertSee('Owner custom label')->assertSee('Giới thiệu');
    }

    public function test_livewire_poll_changes_notify_other_instances_and_refresh_with_authorization(): void
    {
        $community = Community::factory()->create(['status' => 'ACTIVE']);
        $actor = $community->creator;
        $this->actingAs($actor)->post(route('communities.posts.store', $community), [
            'title' => 'Poll refresh', 'body' => 'Choose one', 'poll_enabled' => true,
            'poll_options' => ['First', 'Second'],
        ])->assertRedirect();
        $post = $community->posts()->sole();
        $option = $post->poll->options()->firstOrFail();
        $component = Livewire::actingAs($actor)->test(PostPoll::class, ['community' => $community, 'post' => $post]);
        $component->call('vote', $option->id)->assertDispatched('social-poll-updated.'.$post->id);
        $component->call('close')->assertDispatched('social-poll-updated.'.$post->id);
        $component->dispatch('social-poll-updated.'.$post->id)->assertSee(__('Closed'));
        $this->assertNotNull($post->poll->fresh()->closed_at);
        $this->assertSame(1, $post->poll->votes()->count());
    }
}
