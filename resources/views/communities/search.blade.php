<x-app-layout>
    <x-slot name="header">
        <h1 class="font-bold text-xl text-slate-900 leading-tight">
            {{ __('Search') }} &mdash; {{ $community->name }}
        </h1>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Contextual Workspace Header -->
            <x-community-workspace :community="$community" active-tab="search" />

            <!-- Search Input Card -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 sm:p-6">
                <form method="GET" action="{{ route('communities.search', $community) }}" class="space-y-4">
                    <input type="hidden" name="type" value="{{ $type }}">

                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text"
                                   name="q"
                                   id="search-input"
                                   value="{{ $searchQuery ?? '' }}"
                                   placeholder="{{ __('Search discussions, courses, lessons, and members...') }}"
                                   class="block w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="submit"
                                    class="inline-flex items-center px-5 py-2.5 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-sm font-semibold shadow-xs transition active:scale-[0.99]">
                                {{ __('Search') }}
                            </button>
                            @if ($searchQuery !== null)
                                <a href="{{ route('communities.search', [$community, 'type' => $type]) }}"
                                   class="inline-flex items-center px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-medium transition">
                                    {{ __('Clear') }}
                                </a>
                            @endif
                        </div>
                    </div>

                    @error('q')
                        <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                    @error('type')
                        <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </form>

                <!-- Type Selector Tabs -->
                <div class="mt-6 border-b border-slate-100">
                    <nav class="-mb-px flex flex-wrap gap-4 sm:gap-8" aria-label="Search Type Tabs">
                        @php
                            $tabs = [
                                'posts' => __('Discussions'),
                                'courses' => __('Courses'),
                                'lessons' => __('Lessons'),
                                'members' => __('Members'),
                            ];
                        @endphp

                        @foreach ($tabs as $tabKey => $tabLabel)
                            @php
                                $isActive = $type === $tabKey;
                                $tabParams = ['community' => $community, 'type' => $tabKey];
                                if ($searchQuery !== null && $searchQuery !== '') {
                                    $tabParams['q'] = $searchQuery;
                                }
                                $tabUrl = route('communities.search', $tabParams);
                                $tabCount = $counts[$tabKey] ?? 0;
                            @endphp
                            <a href="{{ $tabUrl }}"
                               class="inline-flex items-center py-2.5 px-1 border-b-2 text-sm font-medium transition {{ $isActive ? 'border-brand-600 text-brand-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-900 hover:border-slate-300' }}">
                                <span>{{ $tabLabel }}</span>
                                @if ($searchQuery !== null && $searchQuery !== '')
                                    <span class="ml-2 py-0.5 px-2 rounded-full text-xs font-semibold {{ $isActive ? 'bg-brand-50 text-brand-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $tabCount }}
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </nav>
                </div>
            </div>

            <!-- Results Section -->
            <div>
                @if ($results === null)
                    <!-- Initial Idle State -->
                    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-12 text-center text-slate-500">
                        <svg class="mx-auto h-12 w-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <p class="text-base font-semibold text-slate-900">{{ __('Discover content in :community', ['community' => $community->name]) }}</p>
                        <p class="text-sm text-slate-500 mt-1 max-w-md mx-auto">
                            {{ __('Enter at least 2 characters to search across discussions, courses, lessons, and community members.') }}
                        </p>
                    </div>
                @elseif ($results->isEmpty())
                    <!-- Zero Results State -->
                    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-12 text-center text-slate-500">
                        <svg class="mx-auto h-12 w-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-base font-semibold text-slate-900">{{ __('No results found') }}</p>
                        <p class="text-sm text-slate-500 mt-1">
                            {{ __('We couldn\'t find any :type matching ":query". Try searching for different keywords.', ['type' => $type, 'query' => $searchQuery]) }}
                        </p>
                    </div>
                @else
                    <!-- Results List -->
                    <div class="space-y-4">
                        @if ($type === 'posts')
                            @foreach ($results as $post)
                                <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 hover:border-brand-500/30 hover:shadow-md transition">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            @if ($post->isPinned())
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                                    {{ __('Pinned') }}
                                                </span>
                                            @endif
                                            @if ($post->category)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                                    {{ $post->category->name }}
                                                </span>
                                            @endif
                                            <span class="text-xs text-slate-400">
                                                {{ __('By :name', ['name' => $post->author->name ?? __('Unknown')]) }} &bull; {{ $post->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>
                                    <h3 class="text-lg font-bold text-slate-900 mb-2">
                                        <a href="{{ route('communities.posts.show', [$community, $post]) }}" class="hover:text-brand-600 transition">
                                            {{ $post->title }}
                                        </a>
                                    </h3>
                                    <p class="text-sm text-slate-600 line-clamp-3 leading-relaxed">
                                        {{ Str::limit(strip_tags($post->body), 160) }}
                                    </p>
                                </div>
                            @endforeach
                        @elseif ($type === 'courses')
                            @foreach ($results as $course)
                                <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 hover:border-brand-500/30 hover:shadow-md transition">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            @if ($course->isDraft() && $isCreator)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                                    {{ __('Draft') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <h3 class="text-lg font-bold text-slate-900 mb-2">
                                        <a href="{{ route('communities.courses.show', [$community, $course]) }}" class="hover:text-brand-600 transition">
                                            {{ $course->title }}
                                        </a>
                                    </h3>
                                    @if ($course->description && ($isCreator || $course->hasMemberAccess(auth()->user())))
                                        <p class="text-sm text-slate-600 line-clamp-3 leading-relaxed">
                                            {{ Str::limit(strip_tags($course->description), 160) }}
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        @elseif ($type === 'lessons')
                            @foreach ($results as $lesson)
                                @php
                                    $course = $lesson->courseSection?->course;
                                @endphp
                                <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 hover:border-brand-500/30 hover:shadow-md transition">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            @if (($lesson->isDraft() || $course?->isDraft()) && $isCreator)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                                    {{ __('Draft') }}
                                                </span>
                                            @endif
                                            @if ($course)
                                                <span class="text-xs text-slate-400">
                                                    {{ __('Course: :course', ['course' => $course->title]) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <h3 class="text-lg font-bold text-slate-900 mb-2">
                                        @if ($course)
                                            <a href="{{ route('communities.lessons.show', [$community, $course, $lesson]) }}" class="hover:text-brand-600 transition">
                                                {{ $lesson->title }}
                                            </a>
                                        @else
                                            <span>{{ $lesson->title }}</span>
                                        @endif
                                    </h3>
                                    @if ($lesson->content)
                                        <p class="text-sm text-slate-600 line-clamp-3 leading-relaxed">
                                            {{ Str::limit(strip_tags($lesson->content), 160) }}
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        @elseif ($type === 'members')
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach ($results as $member)
                                    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 flex items-start gap-3.5 hover:border-brand-500/30 hover:shadow-md transition">
                                        <img src="{{ route('communities.directory.avatar', [$community, $member]) }}"
                                             alt="{{ $member->name }}"
                                             class="w-12 h-12 rounded-full object-cover border border-slate-200 bg-slate-100 shrink-0">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('communities.directory.show', [$community, $member]) }}" class="text-sm font-bold text-slate-900 hover:text-brand-600 truncate transition">
                                                    {{ $member->name }}
                                                </a>
                                                @if ($member->id === $community->creator_id)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-brand-50 text-brand-700 border border-brand-200 shrink-0">
                                                        {{ __('Creator') }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if ($member->bio)
                                                <p class="text-xs text-slate-500 line-clamp-2 mt-1 leading-relaxed">
                                                    {{ Str::limit(strip_tags($member->bio), 100) }}
                                                </p>
                                            @endif
                                            <div class="mt-2">
                                                <a href="{{ route('communities.directory.show', [$community, $member]) }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700 inline-flex items-center">
                                                    <span>{{ __('View profile &rarr;') }}</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-6">
                            {{ $results->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
