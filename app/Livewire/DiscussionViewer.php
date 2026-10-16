<?php

namespace App\Livewire;

use App\Actions\CommunityContent\FeedMutation;
use App\Jobs\SendPostReplyNotification;
use App\Models\Comment;
use App\Models\Community;
use App\Models\OnboardingStepCompletion;
use App\Models\Post;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class DiscussionViewer extends Component
{
    private const COMMENTS_PAGE_SIZE = 20;

    #[Locked]
    public int $communityId;

    #[Locked]
    public ?int $postId = null;

    public bool $isOpen = false;

    // Post details
    public string $postTitle = '';

    public string $postBody = '';

    public string $authorName = '';

    public string $authorInitial = '';

    public bool $isAuthorCreator = false;

    public ?string $categoryName = null;

    public string $postCreatedAt = '';

    public bool $isPinned = false;

    public bool $isAuthor = false;

    public bool $canDeletePost = false;

    // Comments
    public array $comments = [];

    public int $commentsCount = 0;

    public int $commentsLimit = self::COMMENTS_PAGE_SIZE;

    public bool $hasEarlierComments = false;

    public string $newCommentBody = '';

    // Comment editing
    public ?int $editingCommentId = null;

    public string $editingCommentBody = '';

    // Feedback
    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    public function mount(Community $community, ?int $postId = null): void
    {
        $this->communityId = $community->id;

        if ($postId !== null) {
            $this->openPost($postId);
        }
    }

    #[On('open-discussion')]
    public function handleOpenDiscussion(int $postId): void
    {
        $this->openPost($postId);
    }

    public function openPost(int $postId): void
    {
        [$community, $post, $isCreator] = $this->visiblePost($postId);
        $user = auth()->user();

        $this->postId = $post->id;
        $this->postTitle = $post->title;
        $this->postBody = $post->body;
        $this->authorName = $post->author->name ?? __('Unknown');
        $this->authorInitial = strtoupper(substr($this->authorName, 0, 1));
        $this->isAuthorCreator = $post->author ? $community->isCreator($post->author) : false;
        $this->categoryName = $post->category->name ?? null;
        $this->postCreatedAt = $post->created_at->diffForHumans();
        $this->isPinned = $post->isPinned();
        $this->isAuthor = $user && (int) $post->user_id === (int) $user->id;
        $this->canDeletePost = $user && ($this->isAuthor || $isCreator);

        $this->commentsLimit = self::COMMENTS_PAGE_SIZE;
        $this->loadComments($post);

        $this->isOpen = true;
        $this->errorMessage = null;
        $this->successMessage = null;
        $this->editingCommentId = null;
        $this->editingCommentBody = '';
        $this->newCommentBody = '';

        $this->dispatch(
            'discussion-opened',
            postId: $post->id,
            url: route('communities.posts.show', [$community, $post]),
        );
    }

    protected function loadComments(Post $post): void
    {
        $user = auth()->user();
        $community = $post->community ?? Community::findOrFail($this->communityId);
        $isCreator = $user ? $community->isCreator($user) : false;

        $this->commentsCount = $post->comments()->count();

        $comments = $post->comments()
            ->with('author:id,name')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($this->commentsLimit)
            ->get()
            ->reverse()
            ->values();

        $this->comments = $comments->map(function ($comment) use ($user, $isCreator, $community) {
            $isCommentAuthor = $user && (int) $comment->user_id === (int) $user->id;

            return [
                'id' => $comment->id,
                'user_id' => $comment->user_id,
                'author_name' => $comment->author->name ?? __('Unknown'),
                'author_initial' => strtoupper(substr($comment->author->name ?? 'U', 0, 1)),
                'is_creator' => $comment->author ? $community->isCreator($comment->author) : false,
                'body' => $comment->body,
                'created_at' => $comment->created_at->diffForHumans(),
                'can_edit' => $isCommentAuthor,
                'can_delete' => $isCommentAuthor || $isCreator,
            ];
        })->toArray();

        $this->hasEarlierComments = $this->commentsCount > count($this->comments);
    }

    public function loadEarlierComments(): void
    {
        [, $post] = $this->visiblePost();
        $this->commentsLimit += self::COMMENTS_PAGE_SIZE;
        $this->loadComments($post);
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->postId = null;
        $this->editingCommentId = null;
        $this->editingCommentBody = '';
        $this->newCommentBody = '';
        $this->errorMessage = null;
        $this->successMessage = null;
        $this->comments = [];
        $this->commentsCount = 0;
        $this->hasEarlierComments = false;

        $this->dispatch('discussion-closed');
    }

    public function addComment(): void
    {
        $user = auth()->user();
        if (! $user) {
            $this->errorMessage = 'Please sign in to comment.';

            return;
        }

        $this->validate([
            'newCommentBody' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $community = Community::findOrFail($this->communityId);

            app(FeedMutation::class)->run($user, $community, $this->postId, null, function (Community $current, Post $currentPost) use ($user): void {
                Gate::authorize('create', [Comment::class, $currentPost]);

                $comment = $currentPost->comments()->create([
                    'user_id' => $user->id,
                    'body' => $this->newCommentBody,
                ]);

                if ((int) $currentPost->user_id !== (int) $user->id) {
                    SendPostReplyNotification::dispatch($comment->id);
                }

                OnboardingStepCompletion::markStep($current, $user, 'comment');
            });

            $this->newCommentBody = '';
            $this->errorMessage = null;
            $this->successMessage = 'Comment posted successfully.';

            $post = $community->posts()->findOrFail($this->postId);
            $this->loadComments($post);
        } catch (\Throwable $e) {
            report($e);
            $this->errorMessage = 'Unable to post comment. Please check your access and try again.';
        }
    }

    public function editComment(int $commentId): void
    {
        $comment = collect($this->comments)->firstWhere('id', $commentId);
        if ($comment && $comment['can_edit']) {
            $this->editingCommentId = $commentId;
            $this->editingCommentBody = $comment['body'];
            $this->errorMessage = null;
        }
    }

    public function cancelEditComment(): void
    {
        $this->editingCommentId = null;
        $this->editingCommentBody = '';
        $this->errorMessage = null;
    }

    public function updateComment(): void
    {
        $user = auth()->user();
        if (! $user || ! $this->editingCommentId) {
            return;
        }

        $this->validate([
            'editingCommentBody' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $community = Community::findOrFail($this->communityId);

            app(FeedMutation::class)->run($user, $community, $this->postId, $this->editingCommentId, function (Community $current, Post $currentPost, Comment $currentComment): void {
                Gate::authorize('update', $currentComment);
                $currentComment->update(['body' => $this->editingCommentBody]);
            });

            $this->editingCommentId = null;
            $this->editingCommentBody = '';
            $this->errorMessage = null;
            $this->successMessage = 'Comment updated.';

            $post = $community->posts()->findOrFail($this->postId);
            $this->loadComments($post);
        } catch (\Throwable $e) {
            report($e);
            $this->errorMessage = 'Unable to update comment. Please check your access and try again.';
        }
    }

    public function deleteComment(int $commentId): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        try {
            $community = Community::findOrFail($this->communityId);

            app(FeedMutation::class)->run($user, $community, $this->postId, $commentId, function (Community $current, Post $currentPost, Comment $currentComment): void {
                Gate::authorize('delete', $currentComment);
                $currentComment->delete();
            });

            $this->errorMessage = null;
            $this->successMessage = 'Comment deleted.';

            $post = $community->posts()->findOrFail($this->postId);
            $this->loadComments($post);
        } catch (\Throwable $e) {
            report($e);
            $this->errorMessage = 'Unable to delete comment. Please check your access and try again.';
        }
    }

    /**
     * @return array{0: Community, 1: Post, 2: bool}
     */
    private function visiblePost(?int $postId = null): array
    {
        $user = auth()->user();
        $community = Community::findOrFail($this->communityId);

        if (! $user || ! $community->isActive()) {
            abort(404);
        }

        $isCreator = $community->isCreator($user);
        $isMember = $community->memberships()->where('user_id', $user->id)->effective()->exists();

        if (! $isCreator && ! $isMember) {
            abort(404);
        }

        $resolvedPostId = $postId ?? $this->postId;
        abort_if($resolvedPostId === null, 404);

        $post = $community->posts()
            ->whereNull('deleted_at')
            ->with(['author:id,name', 'category'])
            ->findOrFail($resolvedPostId);

        return [$community, $post, $isCreator];
    }

    public function render(): View
    {
        return view('livewire.discussion-viewer');
    }
}
