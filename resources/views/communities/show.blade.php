<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-community-workspace :community="$community" active-tab="about" />

            @if (session('status'))
                <div class="p-4 font-medium text-sm text-green-700 bg-green-50 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Community Overview Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-2">
                            <h3 class="text-lg font-bold text-gray-900 mb-2">{{ __('About this Community') }}</h3>
                            <p class="text-gray-700 leading-relaxed mb-4">
                                {{ $community->description ?: __('No description provided yet.') }}
                            </p>

                            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 pt-4 border-t">
                                <div>
                                    <span class="font-medium text-gray-700">{{ __('Tenant Slug:') }}</span>
                                    <code class="ml-1 px-2 py-0.5 bg-gray-100 rounded text-xs text-indigo-600">{{ $community->slug }}</code>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="font-medium text-gray-700">{{ __('Creator:') }}</span>
                                    <img src="{{ route('communities.directory.avatar', [$community, $community->creator]) }}" alt="{{ $community->creator->name }}" class="w-5 h-5 rounded-full object-cover">
                                    <span class="text-gray-900 font-medium">{{ $community->creator->name }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">{{ __('Active Members:') }}</span>
                                    <span class="ml-1 font-semibold text-gray-900">{{ $activeMemberCount ?? $community->memberships()->where('status', 'ACTIVE')->count() }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">{{ __('Created:') }}</span>
                                    <span class="ml-1">{{ $community->created_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Private Workspace Info -->
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-100 flex flex-col justify-between">
                            <div>
                                <h4 class="font-semibold text-gray-800 text-sm mb-2">{{ __('Private Community Status') }}</h4>
                                <p class="text-xs text-gray-500 leading-normal mb-3">
                                    {{ __('This is an invite-only private community. Non-members cannot discover or view internal content.') }}
                                </p>
                                <div class="space-y-1.5 text-xs text-gray-600">
                                    <div class="flex items-center">
                                        <span class="h-2 w-2 rounded-full bg-green-400 mr-2"></span>
                                        <span>{{ __('Tenant Root Active') }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="h-2 w-2 rounded-full bg-indigo-400 mr-2"></span>
                                        <span>{{ $community->isCreator(auth()->user()) ? __('Creator access') : __('Active member access') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Introduction Video (if present) -->
            @if ($community->intro_embed_url)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Introduction Video') }}</h3>
                    <div class="aspect-video w-full max-w-3xl rounded-xl overflow-hidden shadow-sm border border-gray-200">
                        <iframe src="{{ $community->intro_embed_url }}" class="w-full h-full" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>
                </div>
            @endif

            <!-- Community Rules Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>{{ __('Community Rules') }}</span>
                </h3>
                @if ($community->rules)
                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 text-gray-800 text-sm whitespace-pre-line leading-relaxed">
                        {{ $community->rules }}
                    </div>
                @else
                    <p class="text-sm text-gray-500 italic">
                        {{ __('No community rules posted yet.') }}
                    </p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
