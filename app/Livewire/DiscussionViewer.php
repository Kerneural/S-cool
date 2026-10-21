<?php

namespace App\Livewire;

use App\Actions\CommunityContent\FeedMutation;
use App\Jobs\SendPostReplyNotification;
use App\Models\Comment;
use App\Models\Community;
use App\Models\OnboardingStepCompletion;
use App\Models\Post;
use App\Services\HelpfulAnswerService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DiscussionViewer extends Component
{
    private const COMMENTS_PAGE_SIZE = 20;

    #[Locked]
    public int $communityId;

    #[Locked]
    public ?int $postId = null;

    public ?int $acceptedCommentId = null;

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

    // Learning Context
    public bool $hasLearningContext = false;

    public bool $isLearningContextAccessible = false;

    public ?int $courseId = null;

    public ?string $courseTitle = null;

    public ?string $courseUrl = null;

    public ?int $lessonId = null;

    public ?string $lessonTitle = null;

    public ?string $lessonUrl = null;

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
        $this->acceptedCommentId = $post->accepted_comment_id;
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

        $this->loadLearningContext($post);

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

    private function loadLearningContext(Post $post): void
    {
        $user = auth()->user();
        $community = $post->community;
        $this->hasLearningContext = $post->hasLearningContext();
        $this->isLearningContextAccessible = $post->isLearningContextAccessibleFor($user);

        if ($this->hasLearningContext && $this->isLearningContextAccessible && $post->course) {
            $this->courseId = $post->course_id;
            $this->courseTitle = $post->course->title;
            $this->courseUrl = route('communities.courses.show', [$community, $post->course]);
            if ($post->lesson) {
                $this->lessonId = $post->lesson_id;
                $this->lessonTitle = $post->lesson->title;
                $this->lessonUrl = route('communities.lessons.show', [$community, $post->course, $post->lesson]);
            } else {
                $this->lessonId = null;
                $this->lessonTitle = null;
                $this->lessonUrl = null;
            }
        } else {
            $this->courseId = null;
            $this->courseTitle = null;
            $this->courseUrl = null;
            $this->lessonId = null;
            $this->lessonTitle = null;
            $this->lessonUrl = null;
        }
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

        $eligibleAcceptedId = Post::query()->whereKey($post->id)->eligibleHelpfulAnswer()->value('posts.accepted_comment_id');
        $eligibleAuthorIds = collect();
        if ($isCreator && Post::query()->whereKey($post->id)->accessibleLearningContext($user)->exists()) {
            $eligibleAuthorIds = $community->memberships()->effective()
                ->whereIn('user_id', $comments->pluck('user_id'))->pluck('user_id');
            $course = $post->course;
            if ($course?->isOneTime()) {
                $eligibleAuthorIds = $course->entitlements()->whereIn('user_id', $eligibleAuthorIds)->pluck('user_id');
            }
        }

        $this->comments = $comments->map(function ($comment) use ($user, $isCreator, $community, $post, $eligibleAcceptedId, $eligibleAuthorIds) {
            $isCommentAuthor = $user && (int) $comment->user_id === (int) $user->id;
            $isAccepted = (int) $post->accepted_comment_id === (int) $comment->id;
            $canAccept = $isCreator && $eligibleAuthorIds->contains($comment->user_id)
                && (int) $comment->user_id !== (int) ($user?->id ?? 0);

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
                'is_helpful' => (int) $eligibleAcceptedId === (int) $comment->id,
                'can_accept' => $canAccept && ! $isAccepted,
                'can_unaccept' => $isCreator && $isAccepted,
            ];
        })->toArray();

        $this->hasEarlierComments = $this->commentsCount > count($this->comments);

        // Reuse the authorized, bounded comment page to refresh the Feed card.
        $latest = $comments->last();
        $this->dispatch('discussion-comments-updated',
            communityId: $community->id,
            postId: $post->id,
            count: $this->commentsCount,
            latestComment: $latest ? [
                'id' => $latest->id,
                'author' => $latest->author->name ?? __('Unknown'),
                'body' => Str::limit($latest->body, 140),
                'datetime' => $latest->created_at->toIso8601String(),
                'time' => $latest->created_at->diffForHumans(),
            ] : null,
        );
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

        $this->reset(['hasLearningContext', 'isLearningContextAccessible', 'courseId', 'courseTitle', 'courseUrl', 'lessonId', 'lessonTitle', 'lessonUrl']);

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

    public function acceptHelpfulAnswer(int $commentId, HelpfulAnswerService $service): void
    {
        $this->errorMessage = null;
        $this->successMessage = null;
        $user = auth()->user();
        if (! $user) {
            $this->errorMessage = 'Please sign in to accept answers.';

            return;
        }

        try {
            [$community, $post, $isCreator] = $this->visiblePost();
            if (! $isCreator) {
                $this->errorMessage = 'Only the community creator can accept helpful answers.';

                return;
            }

            $comment = $post->comments()->findOrFail($commentId);

            $service->accept($community, $post, $comment, $user);
            $this->successMessage = __('Helpful answer accepted.');
            $post->refresh();
            $this->acceptedCommentId = $post->accepted_comment_id;
            $this->loadComments($post);
        } catch (\Throwable $e) {
            $this->errorMessage = 'Unable to accept this answer. Refresh, check access and try again.';
        }
    }

    public function revokeHelpfulAnswer(int $commentId, HelpfulAnswerService $service): void
    {
        $this->errorMessage = null;
        $this->successMessage = null;
        $user = auth()->user();
        if (! $user) {
            $this->errorMessage = 'Please sign in to revoke answers.';

            return;
        }

        try {
            [$community, $post, $isCreator] = $this->visiblePost();
            if (! $isCreator) {
                $this->errorMessage = 'Only the community creator can revoke helpful answers.';

                return;
            }

            $comment = $post->comments()->findOrFail($commentId);

            $service->revoke($community, $post, $comment, $user);
            $this->successMessage = __('Helpful answer revoked.');
            $post->refresh();
            $this->acceptedCommentId = $post->accepted_comment_id;
            $this->loadComments($post);
        } catch (\Throwable $e) {
            $this->errorMessage = 'Unable to revoke this answer. Refresh, check access and try again.';
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
            ->with(['author:id,name', 'category', 'course', 'lesson.section'])
            ->findOrFail($resolvedPostId);

        $post->setRelation('community', $community);

        return [$community, $post, $isCreator];
    }

    public function render(): View
    {
        if ($this->isOpen && $this->postId !== null) {
            try {
                [, $post] = $this->visiblePost();
                $this->loadLearningContext($post);
            } catch (ModelNotFoundException|HttpException) {
                $this->reset([
                    'postTitle', 'postBody', 'authorName', 'authorInitial', 'categoryName',
                    'comments', 'commentsCount', 'hasEarlierComments', 'hasLearningContext',
                    'isLearningContextAccessible', 'courseId', 'courseTitle', 'courseUrl',
                    'lessonId', 'lessonTitle', 'lessonUrl',
                ]);
                $this->isOpen = false;
                $this->errorMessage ??= __('This discussion is no longer available.');
                $this->dispatch('discussion-closed');
            }
        }

        return view('livewire.discussion-viewer');
    }
}
