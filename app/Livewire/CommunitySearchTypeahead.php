<?php

namespace App\Livewire;

use App\Models\Community;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CommunitySearchTypeahead extends Component
{
    #[Locked]
    public int $communityId;

    #[Locked]
    public string $communitySlug;

    public string $query = '';

    public int $requestId = 0;

    public int $lastProcessedRequestId = 0;

    public bool $isOpen = false;

    public int $selectedIndex = -1;

    /**
     * @var array{
     *     posts: list<array{id: string, title: string, subtitle: string, badge: string, url: string}>,
     *     courses_lessons: list<array{id: string, title: string, subtitle: string, badge: string, url: string}>,
     *     members: list<array{id: string, title: string, subtitle: string, badge: string, avatar_url: string, url: string}>,
     *     events: list<array{id: string, title: string, subtitle: string, badge: string, url: string}>
     * }
     */
    public array $results = [
        'posts' => [],
        'courses_lessons' => [],
        'members' => [],
        'events' => [],
    ];

    public int $totalResultsCount = 0;

    public ?string $errorMessage = null;

    public function mount(Community $community): void
    {
        $this->communityId = $community->id;
        $this->communitySlug = $community->slug;
    }

    public function updatedQuery(): void
    {
        $this->requestId++;
        $this->executeSearch($this->requestId, $this->query);
    }

    public function executeSearch(int $forRequestId, ?string $query = null): void
    {
        // Stale response guard: discard any request that is older than the latest initiated request
        // or older than what has already been processed.
        if ($forRequestId < $this->requestId || $forRequestId < $this->lastProcessedRequestId) {
            return;
        }

        $this->selectedIndex = -1;
        $this->errorMessage = null;

        $targetQuery = $query ?? $this->query;
        $trimmed = trim($targetQuery);
        $length = mb_strlen($trimmed);

        if ($length < 2) {
            $this->resetResults();
            $this->isOpen = false;
            $this->lastProcessedRequestId = $forRequestId;

            return;
        }

        if ($length > 100) {
            $this->resetResults();
            $this->errorMessage = 'Search query must be 100 characters or fewer.';
            $this->isOpen = true;
            $this->lastProcessedRequestId = $forRequestId;

            return;
        }

        $community = $this->getAuthorizedCommunity();
        if (! $community) {
            $this->resetResults();
            $this->errorMessage = 'Unauthorized or inactive community.';
            $this->isOpen = true;
            $this->lastProcessedRequestId = $forRequestId;

            return;
        }

        $user = auth()->user();
        if (! $user) {
            $this->resetResults();
            $this->errorMessage = 'Please sign in to search.';
            $this->isOpen = true;
            $this->lastProcessedRequestId = $forRequestId;

            return;
        }

        $isCreator = $community->isCreator($user);
        $escaped = addcslashes($trimmed, '%_\\');

        try {
            // 1. Posts (Tenant-scoped, excluding soft-deleted, max 4)
            $posts = $community->posts()
                ->where(function ($q) use ($escaped): void {
                    $q->where('title', 'like', "%{$escaped}%")
                        ->orWhere('body', 'like', "%{$escaped}%");
                })
                ->with(['author:id,name', 'category:id,name'])
                ->orderByDesc('id')
                ->limit(4)
                ->get()
                ->map(fn ($post) => [
                    'id' => 'post-'.$post->id,
                    'title' => $post->title,
                    'subtitle' => $post->category?->name ?? 'Discussion',
                    'badge' => 'Post',
                    'url' => route('communities.posts.show', [$community, $post]),
                ])
                ->all();

            // 2. Courses (Published & Entitled for non-creators, max 3)
            $coursesQuery = $community->courses()
                ->where(function ($q) use ($escaped): void {
                    $q->where('title', 'like', "%{$escaped}%")
                        ->orWhere('description', 'like', "%{$escaped}%");
                });

            if (! $isCreator) {
                $coursesQuery->published()->entitledTo($user);
            }

            $courses = $coursesQuery->orderBy('order')
                ->orderByDesc('id')
                ->limit(3)
                ->get()
                ->map(fn ($c) => [
                    'id' => 'course-'.$c->id,
                    'title' => $c->title,
                    'subtitle' => $c->isOneTime() ? ($c->isEntitledFor($user) ? 'Purchased' : $c->formattedPrice()) : 'Included',
                    'badge' => 'Course',
                    'url' => route('communities.courses.show', [$community, $c]),
                ])
                ->all();

            // 3. Lessons (Published for non-creators, ancestry-checked, max 3)
            $lessonsQuery = Lesson::query()
                ->whereHas('courseSection.course', function ($q) use ($community, $isCreator, $user): void {
                    $q->where('community_id', $community->id);
                    if (! $isCreator) {
                        $q->where('status', 'PUBLISHED');
                        $q->entitledTo($user);
                    }
                })
                ->where(function ($q) use ($escaped): void {
                    $q->where('title', 'like', "%{$escaped}%")
                        ->orWhere('content', 'like', "%{$escaped}%");
                });

            if (! $isCreator) {
                $lessonsQuery->published();
            }

            $lessons = $lessonsQuery->with(['courseSection.course'])
                ->orderBy('id')
                ->limit(3)
                ->get()
                ->map(function ($l) use ($community) {
                    $course = $l->courseSection?->course;

                    return [
                        'id' => 'lesson-'.$l->id,
                        'title' => $l->title,
                        'subtitle' => $course ? $course->title : 'Lesson',
                        'badge' => 'Lesson',
                        'url' => $course ? route('communities.lessons.show', [$community, $course, $l]) : '#',
                    ];
                })
                ->all();

            $coursesLessons = array_merge($courses, $lessons);

            // 4. Members (Active community members & creator only, max 4)
            $members = User::query()
                ->where(function ($query) use ($community): void {
                    $query->where('id', $community->creator_id)
                        ->orWhereHas('memberships', function ($q) use ($community): void {
                            $q->where('community_id', $community->id)->effective();
                        });
                })
                ->where(function ($q) use ($escaped): void {
                    $q->where('name', 'like', "%{$escaped}%")
                        ->orWhere('bio', 'like', "%{$escaped}%");
                })
                ->select(['id', 'name', 'bio', 'avatar_path'])
                ->orderBy('name')
                ->limit(4)
                ->get()
                ->map(fn ($m) => [
                    'id' => 'member-'.$m->id,
                    'title' => $m->name,
                    'subtitle' => $m->id === $community->creator_id ? 'Creator' : ($m->bio ? Str::limit($m->bio, 50) : 'Member'),
                    'badge' => $m->id === $community->creator_id ? 'Creator' : 'Member',
                    'avatar_url' => route('communities.directory.avatar', [$community, $m]),
                    'url' => route('communities.directory.show', [$community, $m]),
                ])
                ->all();

            // 5. Events (Tenant-scoped, scheduled only, max 4)
            $events = $community->events()
                ->where('status', 'SCHEDULED')
                ->where(function ($q) use ($escaped): void {
                    $q->where('title', 'like', "%{$escaped}%")
                        ->orWhere('description', 'like', "%{$escaped}%");
                })
                ->orderBy('starts_at')
                ->limit(4)
                ->get()
                ->map(fn ($e) => [
                    'id' => 'event-'.$e->id,
                    'title' => $e->title,
                    'subtitle' => $e->starts_at->format('M d, Y H:i'),
                    'badge' => 'Event',
                    'url' => route('communities.events.show', [$community, $e]),
                ])
                ->all();

            // Final race condition check before mutating component state
            if ($forRequestId < $this->requestId || $forRequestId < $this->lastProcessedRequestId) {
                return;
            }

            $this->lastProcessedRequestId = $forRequestId;
            $this->results = [
                'posts' => $posts,
                'courses_lessons' => $coursesLessons,
                'members' => $members,
                'events' => $events,
            ];

            $this->totalResultsCount = count($posts) + count($coursesLessons) + count($members) + count($events);
            $this->isOpen = true;
        } catch (\Throwable) {
            if ($forRequestId < $this->requestId || $forRequestId < $this->lastProcessedRequestId) {
                return;
            }

            $this->lastProcessedRequestId = $forRequestId;
            $this->resetResults();
            $this->errorMessage = 'Unable to complete search. Please try again.';
            $this->isOpen = true;
        }
    }

    public function selectNext(): void
    {
        $flattened = $this->flattenResults();
        $total = count($flattened);

        if ($total === 0) {
            $this->selectedIndex = -1;

            return;
        }

        $this->selectedIndex = ($this->selectedIndex + 1) >= $total ? 0 : $this->selectedIndex + 1;
        $this->isOpen = true;
    }

    public function selectPrevious(): void
    {
        $flattened = $this->flattenResults();
        $total = count($flattened);

        if ($total === 0) {
            $this->selectedIndex = -1;

            return;
        }

        $this->selectedIndex = ($this->selectedIndex - 1) < 0 ? $total - 1 : $this->selectedIndex - 1;
        $this->isOpen = true;
    }

    public function selectActive(): mixed
    {
        $flattened = $this->flattenResults();

        if ($this->selectedIndex >= 0 && isset($flattened[$this->selectedIndex])) {
            $targetUrl = $flattened[$this->selectedIndex]['url'];
            $this->closeDropdown();

            return redirect()->to($targetUrl);
        }

        $trimmed = trim($this->query);
        if (mb_strlen($trimmed) >= 2) {
            $community = $this->getAuthorizedCommunity();
            if ($community) {
                $this->closeDropdown();

                return redirect()->route('communities.search', [
                    'community' => $community,
                    'q' => $trimmed,
                ]);
            }
        }

        return null;
    }

    public function closeDropdown(): void
    {
        $this->isOpen = false;
        $this->selectedIndex = -1;
    }

    public function openDropdown(): void
    {
        if (mb_strlen(trim($this->query)) >= 2) {
            $this->isOpen = true;
        }
    }

    public function clearQuery(): void
    {
        $this->requestId++;
        $this->lastProcessedRequestId = $this->requestId;
        $this->query = '';
        $this->resetResults();
        $this->isOpen = false;
        $this->errorMessage = null;
        $this->selectedIndex = -1;
    }

    /**
     * @return list<array{id: string, title: string, subtitle: string, badge: string, url: string}>
     */
    public function flattenResults(): array
    {
        /** @var list<array{id: string, title: string, subtitle: string, badge: string, url: string}> $flattened */
        $flattened = [];

        foreach ($this->results['posts'] as $item) {
            $flattened[] = $item;
        }
        foreach ($this->results['courses_lessons'] as $item) {
            $flattened[] = $item;
        }
        foreach ($this->results['members'] as $item) {
            $flattened[] = [
                'id' => $item['id'],
                'title' => $item['title'],
                'subtitle' => $item['subtitle'],
                'badge' => $item['badge'],
                'url' => $item['url'],
            ];
        }
        foreach ($this->results['events'] as $item) {
            $flattened[] = $item;
        }

        return $flattened;
    }

    protected function getAuthorizedCommunity(): ?Community
    {
        $community = Community::query()->find($this->communityId);
        if (! $community || ! $community->isActive()) {
            return null;
        }

        $user = auth()->user();
        if (! $user) {
            return null;
        }

        $isCreator = $community->isCreator($user);
        $isActiveMember = $community->memberships()->where('user_id', $user->id)->effective()->exists();

        if (! $isCreator && ! $isActiveMember) {
            return null;
        }

        return $community;
    }

    protected function resetResults(): void
    {
        $this->results = [
            'posts' => [],
            'courses_lessons' => [],
            'members' => [],
            'events' => [],
        ];
        $this->totalResultsCount = 0;
    }

    public function render(): View
    {
        $community = Community::query()->find($this->communityId);

        return view('livewire.community-search-typeahead', [
            'community' => $community,
            'flattenedResults' => $this->flattenResults(),
        ]);
    }
}
