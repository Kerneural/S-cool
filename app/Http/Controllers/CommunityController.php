<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\PostCategory;
use App\Services\Search\SearchNormalizationService;
use App\Services\VideoEmbedService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class CommunityController extends Controller
{
    /**
     * Discover active communities, or filter the user's owned/joined communities.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $rawFilter = $request->query('filter', 'all');
        $filter = is_string($rawFilter) && in_array($rawFilter, ['all', 'owned', 'joined'], true) ? $rawFilter : 'all';

        $query = Community::query()
            ->select(['id', 'creator_id', 'name', 'slug', 'description', 'status', 'cover_path'])
            ->withExists(['memberships as has_effective_membership' => function ($q) use ($user): void {
                $q->where('user_id', $user->id)->effective();
            }]);

        if ($filter === 'owned') {
            $query->where('creator_id', $user->id);
        } elseif ($filter === 'joined') {
            $query->where('status', 'ACTIVE')
                ->where('creator_id', '!=', $user->id)
                ->whereHas('memberships', function ($q) use ($user): void {
                    $q->where('user_id', $user->id)->effective();
                });
        } else {
            $query->active();
        }

        $rawQ = $request->query('q');
        $search = is_string($rawQ) ? trim(mb_substr($rawQ, 0, 100)) : '';

        if ($search !== '') {
            $escaped = SearchNormalizationService::escapeSqlWildcards($search);
            $tokens = SearchNormalizationService::tokenize($search);

            $query->where(function ($q) use ($escaped, $tokens): void {
                $q->where('name', 'like', "%{$escaped}%")
                    ->orWhere('description', 'like', "%{$escaped}%");

                foreach (array_slice($tokens, 0, 4) as $token) {
                    if (mb_strlen($token) >= 3) {
                        $escToken = SearchNormalizationService::escapeSqlWildcards($token);
                        $q->orWhere('name', 'like', "%{$escToken}%");
                    }
                    if (mb_strlen($token) >= 4) {
                        $prefix2 = SearchNormalizationService::escapeSqlWildcards(mb_substr($token, 0, 2));
                        $q->orWhere('name', 'like', "%{$prefix2}%");
                    }
                }
            });

            // Bounded candidate retrieval (max 50 to prevent memory exhaustion)
            SearchNormalizationService::prioritizeCandidates($query, 'name', $search);
            $candidates = $query->distinct()->take(50)->get();

            $scored = $candidates->map(function ($community) use ($search) {
                $nameScore = SearchNormalizationService::calculateScore($community->name, $search);
                $descScore = $community->description ? (int) (SearchNormalizationService::calculateScore($community->description, $search) * 0.8) : 0;
                $community->search_score = max($nameScore, $descScore);

                return $community;
            })->filter(fn ($c): bool => ($c->search_score ?? 0) > 0)
                ->sort(function ($a, $b) {
                    if ($a->search_score === $b->search_score) {
                        return $b->id <=> $a->id;
                    }

                    return $b->search_score <=> $a->search_score;
                })
                ->values();

            $page = LengthAwarePaginator::resolveCurrentPage();
            $perPage = 12;
            $itemsForCurrentPage = $scored->slice(($page - 1) * $perPage, $perPage)->values();
            $communities = new LengthAwarePaginator(
                $itemsForCurrentPage,
                $scored->count(),
                $perPage,
                $page,
                ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
            );
        } else {
            $communities = $query->distinct()->orderByDesc('id')->paginate(12)->withQueryString();
        }

        return view('communities.index', [
            'communities' => $communities,
            'currentFilter' => $filter,
            'searchQuery' => $search,
        ]);
    }

    /**
     * Show the form for creating a new community.
     */
    public function create(): View
    {
        Gate::authorize('create', Community::class);

        return view('communities.create');
    }

    /**
     * Store a newly created community in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Community::class);

        if (is_string($request->input('slug'))) {
            $request->merge(['slug' => strtolower($request->input('slug'))]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['bail', 'required', 'string', 'max:255', 'alpha_dash:ascii', Rule::notIn(['create']), 'unique:communities,slug'],
            'description' => ['nullable', 'string', 'max:1000'],
            'cover' => [
                'nullable',
                File::image()->dimensions(Rule::dimensions()->maxWidth(4096)->maxHeight(4096)),
                File::types(['jpg', 'jpeg', 'png', 'webp'])->extensions(['jpg', 'jpeg', 'png', 'webp'])->max(2048),
            ],
            'access_mode' => ['sometimes', 'required', Rule::in(['FREE', 'PAID'])],
            'price_vnd' => ['nullable', 'required_if:access_mode,PAID', 'integer', 'min:1', 'max:4294967295'],
            'billing_period' => ['sometimes', 'required', Rule::in(['ONE_TIME', 'TERM_30_DAYS'])],
            'referral_commission_rate' => ['sometimes', 'integer', 'min:0', 'max:30'],
        ]);

        $disk = Storage::disk('community_media');
        $coverPath = null;
        $community = null;

        try {
            DB::transaction(function () use ($request, $validated, $disk, &$community, &$coverPath): void {
                /** @var Community $community */
                $community = $request->user()->createdCommunities()->make([
                    'name' => $validated['name'],
                    'slug' => $validated['slug'],
                    'description' => $validated['description'] ?? null,
                ]);
                $mode = $validated['access_mode'] ?? 'FREE';
                $community->forceFill([
                    'access_mode' => $mode,
                    'price_vnd' => $mode === 'PAID' ? $validated['price_vnd'] : null,
                    'billing_period' => $mode === 'PAID' ? ($validated['billing_period'] ?? 'ONE_TIME') : 'ONE_TIME',
                    'referral_commission_rate' => $validated['referral_commission_rate'] ?? 0,
                ])->save();

                if ($request->hasFile('cover')) {
                    $coverPath = $disk->putFile((string) $community->id, $request->file('cover'));
                    if (! is_string($coverPath)) {
                        throw new \RuntimeException('Cover storage failed.');
                    }
                    $community->forceFill(['cover_path' => $coverPath])->save();
                }

                PostCategory::initializeDefaultsFor($community);
            });
        } catch (UniqueConstraintViolationException) {
            if (is_string($coverPath) && $community !== null) {
                $this->cleanupCover($coverPath, $community->id);
            }
            // The slug is the only client-controlled unique key on this insert.
            throw ValidationException::withMessages(['slug' => 'The slug is not available.']);
        } catch (Throwable $exception) {
            if (is_string($coverPath) && $community !== null) {
                $this->cleanupCover($coverPath, $community->id);
            }
            throw $exception;
        }

        return redirect()->route('communities.show', $community)->with('status', 'community-created');
    }

    /**
     * Display the specified community dashboard/about view.
     */
    public function show(Community $community): View
    {
        Gate::authorize('view', $community);

        $user = Auth::user();
        $activeMemberCount = $community->memberships()->effective()->count();
        $roadmap = $community->courses()->published()->ordered();
        if (! $community->isCreator($user)) {
            $roadmap->entitledTo($user);
        }

        return view('communities.show', [
            'community' => $community,
            'activeMemberCount' => $activeMemberCount,
            'roadmapCourses' => $roadmap->limit(8)->get(['id', 'community_id', 'title']),
        ]);
    }

    /**
     * Dedicated About view action for the community.
     */
    public function about(Community $community): View
    {
        return $this->show($community);
    }

    /**
     * Show the form for editing the specified community.
     */
    public function edit(Community $community): View
    {
        Gate::authorize('update', $community);

        return view('communities.edit', [
            'community' => $community,
        ]);
    }

    /**
     * Update the specified community in storage.
     */
    public function update(Request $request, Community $community): RedirectResponse
    {
        Gate::authorize('update', $community);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'rules' => ['nullable', 'string', 'max:5000'],
            'intro_video_url' => ['nullable', 'string', 'max:500'],
            'access_mode' => ['sometimes', 'required', Rule::in(['FREE', 'PAID'])],
            'price_vnd' => ['nullable', 'required_if:access_mode,PAID', 'integer', 'min:1', 'max:4294967295'],
            'billing_period' => ['sometimes', 'required', Rule::in(['ONE_TIME', 'TERM_30_DAYS'])],
            'onboarding_enabled' => ['sometimes', 'boolean'],
            'referral_commission_rate' => ['sometimes', 'integer', 'min:0', 'max:30'],
            'introduction_category_id' => [
                'nullable',
                'integer',
                Rule::exists('post_categories', 'id')->where('community_id', $community->id),
            ],
        ]);

        if (! empty($validated['intro_video_url']) && ! VideoEmbedService::isValid($validated['intro_video_url'])) {
            throw ValidationException::withMessages([
                'intro_video_url' => 'Please provide a valid YouTube, Vimeo, or Loom URL.',
            ]);
        }

        $mode = $validated['access_mode'] ?? $community->access_mode;
        $price = $mode === 'PAID' ? ($validated['price_vnd'] ?? $community->price_vnd) : null;
        if ($mode === 'PAID' && ! $price) {
            throw ValidationException::withMessages(['price_vnd' => 'Set a positive whole-VND price for paid access.']);
        }

        $fillFields = ['name', 'description', 'rules', 'intro_video_url'];
        if ($request->has('onboarding_enabled')) {
            $fillFields[] = 'onboarding_enabled';
            $validated['onboarding_enabled'] = $request->boolean('onboarding_enabled');
        }
        if (array_key_exists('referral_commission_rate', $validated)) {
            $fillFields[] = 'referral_commission_rate';
        }
        if (array_key_exists('introduction_category_id', $validated)) {
            $fillFields[] = 'introduction_category_id';
        }

        $community->fill(array_intersect_key($validated, array_flip($fillFields)));
        $community->forceFill(['access_mode' => $mode, 'price_vnd' => $price,
            'billing_period' => $mode === 'PAID' ? ($validated['billing_period'] ?? $community->billing_period) : 'ONE_TIME',
        ])->save();

        return redirect()->route('communities.show', $community)->with('status', 'community-updated');
    }

    private function cleanupCover(string $path, int $communityId): void
    {
        if (preg_match('/\A'.preg_quote((string) $communityId, '/').'\/[a-zA-Z0-9]{40}\.(?:jpg|jpeg|png|webp)\z/D', $path) !== 1) {
            return;
        }

        try {
            Storage::disk('community_media')->delete($path);
        } catch (Throwable) {
        }
    }
}
