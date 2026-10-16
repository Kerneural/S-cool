<?php

namespace App\Http\Controllers;

use App\Actions\CommunityContent\FeedMutation;
use App\Models\Community;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\OnboardingStepCompletion;
use App\Models\Post;
use App\Models\User;
use App\Services\LearningFocusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PostController extends Controller
{
    /**
     * Display a listing of posts for the community feed.
     */
    public function index(Request $request, Community $community): View
    {
        Gate::authorize('viewAny', [Post::class, $community]);

        $currentCategorySlug = $request->query('category');
        $currentSort = $request->query('sort', 'newest');
        $contextQuery = $request->validate([
            'course_id' => ['nullable', 'integer', 'min:1'],
            'lesson_id' => ['nullable', 'integer', 'min:1'],
        ]);
        $currentCourseId = isset($contextQuery['course_id']) ? (int) $contextQuery['course_id'] : null;

        if ($currentSort !== null && ! in_array($currentSort, ['newest', 'most_commented'], true)) {
            throw ValidationException::withMessages([
                'sort' => 'The selected sort is invalid.',
            ]);
        }

        $user = $request->user();
        $query = $community->posts()
            ->with([
                'author:id,name,avatar_path',
                'latestVisibleComment' => fn ($q) => $q->select(['comments.id', 'comments.post_id', 'comments.user_id', 'comments.body', 'comments.created_at']),
                'latestVisibleComment.author:id,name',
                'category',
                'course',
                'lesson.section',
                'likes' => fn ($q) => $user ? $q->where('is_active', true)->where('user_id', $user->id) : $q->whereRaw('0 = 1'),
                'bookmarks' => fn ($q) => $user ? $q->where('user_id', $user->id) : $q->whereRaw('0 = 1'),
            ])
            ->withCount([
                'comments',
                'likes as active_likes_count' => fn ($q) => $q->where('is_active', true),
            ]);

        if ($currentCourseId !== null) {
            $query->where('course_id', $currentCourseId);
        }

        if (is_string($currentCategorySlug) && $currentCategorySlug !== '' && $currentCategorySlug !== 'all') {
            if ($currentCategorySlug === 'uncategorized') {
                $query->whereNull('category_id');
            } else {
                $category = $community->categories()->where('slug', $currentCategorySlug)->first();
                if (! $category) {
                    throw ValidationException::withMessages([
                        'category' => 'The selected category does not exist in this community.',
                    ]);
                }
                $query->where('category_id', $category->id);
            }
        }

        // Pinned posts always appear first, followed by requested sort with id tie-breaker
        $query->orderByRaw('pinned_at IS NULL, pinned_at DESC');

        if ($currentSort === 'most_commented') {
            $query->orderByDesc('comments_count')
                ->orderByDesc('created_at')
                ->orderByDesc('id');
        } else {
            $query->orderByDesc('created_at')
                ->orderByDesc('id');
        }

        $posts = $query->paginate(15)->withQueryString();
        $categories = $community->categories()->get();

        return view('communities.posts.index', [
            'community' => $community,
            'posts' => $posts,
            'categories' => $categories,
            'currentCategory' => $currentCategorySlug,
            'currentSort' => $currentSort,
            'currentCourseId' => $currentCourseId ? (int) $currentCourseId : null,
            'isCreator' => $community->isCreator($request->user()),
            'learningFocus' => LearningFocusService::resolve($community, $user),
        ]);
    }

    /**
     * Show the direct form for creating a new post.
     */
    public function create(Request $request, Community $community): View
    {
        Gate::authorize('create', [Post::class, $community]);

        $categories = $community->categories()->get();
        $context = $request->validate([
            'course_id' => ['nullable', 'integer', 'min:1'],
            'lesson_id' => ['nullable', 'integer', 'min:1'],
        ]);
        $courseId = isset($context['course_id']) ? (int) $context['course_id'] : null;
        $lessonId = isset($context['lesson_id']) ? (int) $context['lesson_id'] : null;

        return view('communities.posts.create', compact('community', 'categories', 'courseId', 'lessonId'));
    }

    /**
     * Store a newly created post in storage.
     */
    public function store(Request $request, Community $community): RedirectResponse
    {
        Gate::authorize('create', [Post::class, $community]);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('post_categories', 'id')->where('community_id', $community->id),
            ],
            'course_id' => [
                'nullable',
                'integer',
                Rule::exists('courses', 'id')->where('community_id', $community->id),
            ],
            'lesson_id' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ]);

        if (! empty($validated['category_id'])) {
            $category = $community->categories()->findOrFail($validated['category_id']);
            if ($category->isAnnouncements() && ! $community->isCreator($request->user())) {
                throw ValidationException::withMessages([
                    'category_id' => 'Only the community creator can post in Announcements.',
                ]);
            }
        }

        app(FeedMutation::class)->run($request->user(), $community, null, null, function (Community $current) use ($request, $validated): void {
            Gate::authorize('create', [Post::class, $current]);
            $this->validateLearningContext(
                $current, $request->user(),
                isset($validated['course_id']) ? (int) $validated['course_id'] : null,
                isset($validated['lesson_id']) ? (int) $validated['lesson_id'] : null,
            );
            $post = $current->posts()->create(['user_id' => $request->user()->id, ...$validated]);

            $introCategory = $current->getIntroductionCategory();
            if ($introCategory && (int) $post->category_id === (int) $introCategory->id) {
                OnboardingStepCompletion::markStep($current, $request->user(), 'post');
            }
        });

        return redirect()->route('communities.posts.index', $community)->with('status', 'post-created');
    }

    /**
     * Display the specified post.
     */
    public function show(Community $community, Post $post): View
    {
        Gate::authorize('view', $post);

        $user = auth()->user();
        $post->load([
            'author:id,name',
            'category',
            'course',
            'lesson.section',
            'likes' => fn ($q) => $user ? $q->where('is_active', true)->where('user_id', $user->id) : $q->whereRaw('0 = 1'),
            'bookmarks' => fn ($q) => $user ? $q->where('user_id', $user->id) : $q->whereRaw('0 = 1'),
        ]);
        $post->loadCount(['likes as active_likes_count' => fn ($q) => $q->where('is_active', true)]);

        $comments = $post->comments()->with('author:id,name')->orderBy('created_at')->orderBy('id')->paginate(20);
        $comments->each(fn ($comment) => $comment->setRelation('post', $post));

        return view('communities.posts.show', [
            'community' => $community,
            'post' => $post,
            'comments' => $comments,
        ]);
    }

    /**
     * Show the form for editing the specified post.
     */
    public function edit(Community $community, Post $post): View
    {
        Gate::authorize('update', $post);

        $categories = $community->categories()->get();

        return view('communities.posts.edit', [
            'community' => $community,
            'post' => $post,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified post in storage.
     */
    public function update(Request $request, Community $community, Post $post): RedirectResponse
    {
        Gate::authorize('update', $post);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('post_categories', 'id')->where('community_id', $community->id),
            ],
            'course_id' => [
                'nullable',
                'integer',
                Rule::exists('courses', 'id')->where('community_id', $community->id),
            ],
            'lesson_id' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'remove_learning_context' => ['nullable', 'boolean'],
        ]);

        $removeContext = $request->boolean('remove_learning_context');
        unset($validated['remove_learning_context']);

        if (! empty($validated['category_id'])) {
            $category = $community->categories()->findOrFail($validated['category_id']);
            if ($category->isAnnouncements() && ! $community->isCreator($request->user())) {
                throw ValidationException::withMessages([
                    'category_id' => 'Only the community creator can post in Announcements.',
                ]);
            }
        }

        app(FeedMutation::class)->run($request->user(), $community, $post->id, null, function (Community $current, Post $currentPost) use ($request, $validated, $removeContext): void {
            Gate::authorize('update', $currentPost);
            $attributes = $validated + ['course_id' => $currentPost->course_id, 'lesson_id' => $currentPost->lesson_id];
            if ($removeContext) {
                $attributes['course_id'] = null;
                $attributes['lesson_id'] = null;
            }
            $this->validateLearningContext(
                $current, $request->user(),
                isset($attributes['course_id']) ? (int) $attributes['course_id'] : null,
                isset($attributes['lesson_id']) ? (int) $attributes['lesson_id'] : null,
            );
            $currentPost->update($attributes);
        });

        return redirect()->route('communities.posts.index', $community)->with('status', 'post-updated');
    }

    /**
     * Validate the learning context (course and lesson) against tenant, entitlement, and published status.
     */
    protected function validateLearningContext(Community $community, ?User $user, ?int $courseId, ?int $lessonId): void
    {
        if ($courseId === null && $lessonId !== null) {
            throw ValidationException::withMessages([
                'lesson_id' => 'A course must be selected when specifying a lesson context.',
            ]);
        }

        if ($courseId !== null) {
            /** @var Course|null $course */
            $course = $community->courses()->lockForUpdate()->find($courseId);
            if (! $course) {
                throw ValidationException::withMessages([
                    'course_id' => 'The selected course does not exist in this community.',
                ]);
            }

            $isCreator = $user && $community->isCreator($user);
            $course->setRelation('community', $community);

            if (! $isCreator) {
                if (! $course->isPublished() || ! $course->hasMemberAccess($user)) {
                    throw ValidationException::withMessages([
                        'course_id' => 'You do not have access to this course context.',
                    ]);
                }
            }

            if ($lessonId !== null) {
                /** @var Lesson|null $lesson */
                $lesson = Lesson::whereHas('section', fn ($query) => $query->where('course_id', $course->id))
                    ->lockForUpdate()->find($lessonId);
                if (! $lesson || ! $lesson->section || (int) $lesson->section->course_id !== (int) $course->id) {
                    throw ValidationException::withMessages([
                        'lesson_id' => 'The selected lesson does not belong to this course.',
                    ]);
                }

                if (! $isCreator && ! $lesson->isPublished()) {
                    throw ValidationException::withMessages([
                        'lesson_id' => 'You do not have access to this lesson context.',
                    ]);
                }
            }
        }
    }

    /**
     * Remove the specified post from storage (soft delete).
     */
    public function destroy(Request $request, Community $community, Post $post): RedirectResponse
    {
        Gate::authorize('delete', $post);

        app(FeedMutation::class)->run($request->user(), $community, $post->id, null, function (Community $current, Post $currentPost): void {
            Gate::authorize('delete', $currentPost);
            $currentPost->delete();
        });

        return redirect()->route('communities.posts.index', $community)->with('status', 'post-deleted');
    }

    /**
     * Pin the specified post (creator only, idempotent).
     */
    public function pin(Request $request, Community $community, Post $post): RedirectResponse
    {
        if (! $community->isActive()) {
            abort(404);
        }

        if (! $community->isCreator($request->user())) {
            abort(403);
        }

        app(FeedMutation::class)->run($request->user(), $community, $post->id, null, function (Community $current, Post $currentPost) use ($request): void {
            if (! $current->isActive()) {
                abort(404);
            }

            if (! $current->isCreator($request->user())) {
                abort(403);
            }

            if (! $currentPost->pinned_at) {
                $currentPost->update(['pinned_at' => now()]);
            }
        });

        return back()->with('status', 'post-pinned');
    }

    /**
     * Unpin the specified post (creator only, idempotent).
     */
    public function unpin(Request $request, Community $community, Post $post): RedirectResponse
    {
        if (! $community->isActive()) {
            abort(404);
        }

        if (! $community->isCreator($request->user())) {
            abort(403);
        }

        app(FeedMutation::class)->run($request->user(), $community, $post->id, null, function (Community $current, Post $currentPost) use ($request): void {
            if (! $current->isActive()) {
                abort(404);
            }

            if (! $current->isCreator($request->user())) {
                abort(403);
            }

            if ($currentPost->pinned_at) {
                $currentPost->update(['pinned_at' => null]);
            }
        });

        return back()->with('status', 'post-unpinned');
    }
}
