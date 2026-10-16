<x-app-layout>
    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-community-workspace :community="$community" active-tab="about" />

            @if (session('status'))
                <div class="p-4 font-medium text-sm text-emerald-800 bg-emerald-50 rounded-xl border border-emerald-200 shadow-xs flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('status') }}</span>
                    </div>
                </div>
            @endif

            <!-- Community Overview Card -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 sm:p-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-2 space-y-4">
                        <div>
                            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 mb-2">{{ __('About this Community') }}</h1>
                            <p class="text-slate-600 leading-relaxed text-sm sm:text-base">
                                {{ $community->description ?: __('No description provided yet.') }}
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-4 text-xs sm:text-sm text-slate-500 pt-4 border-t border-slate-100">
                            <div>
                                <span class="font-medium text-slate-700">{{ __('Tenant Slug:') }}</span>
                                <code class="ml-1 px-2 py-0.5 bg-slate-100 rounded-lg text-xs font-mono text-brand-700">{{ $community->slug }}</code>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-slate-700">{{ __('Creator:') }}</span>
                                <img src="{{ route('communities.directory.avatar', [$community, $community->creator]) }}" alt="{{ $community->creator->name }}" class="w-5 h-5 rounded-full object-cover">
                                <span class="text-slate-900 font-semibold">{{ $community->creator->name }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-slate-700">{{ __('Active Members:') }}</span>
                                <span class="ml-1 font-bold text-slate-900 tabular-nums">{{ $activeMemberCount ?? $community->memberships()->effective()->count() }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-slate-700">{{ __('Created:') }}</span>
                                <span class="ml-1">{{ $community->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Private Workspace Info -->
                    <div class="bg-slate-50 p-5 rounded-xl border border-slate-100 flex flex-col justify-between">
                        <div>
                            <h4 class="font-bold text-slate-900 text-sm mb-2">{{ __('Private Community Status') }}</h4>
                            <p class="text-xs text-slate-500 leading-relaxed mb-4">
                                {{ __('This is an invite-only private community. Non-members cannot discover or view internal content.') }}
                            </p>
                            <div class="space-y-2 text-xs text-slate-600">
                                <div class="flex items-center">
                                    <span class="h-2 w-2 rounded-full bg-emerald-500 mr-2"></span>
                                    <span>{{ __('Tenant Root Active') }}</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="h-2 w-2 rounded-full bg-brand-500 mr-2"></span>
                                    <span>{{ $community->isCreator(auth()->user()) ? __('Creator access') : __('Active member access') }}</span>
                                </div>
                            </div>
                            @can('update', $community)
                                <a href="{{ route('communities.edit', $community) }}" class="mt-5 inline-flex min-h-11 items-center justify-center rounded-xl border border-brand-200 bg-white px-4 py-2 text-xs font-bold text-brand-700 shadow-xs transition hover:bg-brand-50">
                                    {{ __('Edit Settings') }}
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>

            <!-- Introduction Video (if present) -->
            @if ($community->intro_embed_url)
                <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 sm:p-8">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">{{ __('Introduction Video') }}</h3>
                    <div class="aspect-video w-full max-w-3xl rounded-2xl overflow-hidden shadow-sm border border-slate-200 bg-slate-950">
                        <iframe src="{{ $community->intro_embed_url }}" class="w-full h-full" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>
                </div>
            @endif

            <!-- Community Rules Card -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 sm:p-8">
                <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>{{ __('Community Rules') }}</span>
                </h3>
                @if ($community->rules)
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 text-slate-700 text-sm whitespace-pre-line leading-relaxed">
                        {{ $community->rules }}
                    </div>
                @else
                    <p class="text-sm text-slate-400 italic">
                        {{ __('No community rules posted yet.') }}
                    </p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
