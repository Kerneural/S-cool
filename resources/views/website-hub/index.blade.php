<x-app-layout>
    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Hub Header Card -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                        {{ __('Website Management') }}
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">
                        {{ __('Manage all your communities, courses, members, and operations from a single dashboard.') }}
                    </p>
                </div>

                <div class="flex items-center flex-wrap gap-2.5 self-start md:self-center shrink-0">
                    <a href="{{ route('communities.create') }}"
                       class="min-h-[44px] inline-flex items-center justify-center px-4 py-2 bg-brand hover:bg-brand-hover text-white text-sm font-semibold rounded-xl shadow-xs transition active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>{{ __('Create Community') }}</span>
                    </a>

                    <a href="{{ route('admin.communities.index') }}"
                       class="min-h-[44px] inline-flex items-center justify-center px-4 py-2 bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 text-sm font-semibold rounded-xl shadow-2xs transition active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2">
                        <svg class="w-4 h-4 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>{{ __('Platform Admin') }}</span>
                    </a>
                </div>
            </div>

            <!-- Communities Cards List -->
            @if ($communities->isEmpty())
                <div class="bg-white rounded-2xl border border-slate-200/80 p-12 text-center shadow-xs">
                    <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto text-slate-400 mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h2 class="text-base font-bold text-slate-900">{{ __('No communities found.') }}</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-sm mx-auto">
                        {{ __('Get started by creating your first community.') }}
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('communities.create') }}"
                           class="min-h-[44px] inline-flex items-center px-4 py-2 bg-brand hover:bg-brand-hover text-white text-sm font-semibold rounded-xl shadow-xs transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>{{ __('Create Community') }}</span>
                        </a>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($communities as $community)
                        @php
                            $firstCourse = $community->courses->first();
                            $courseManageUrl = $firstCourse
                                ? route('communities.manage.courses.show', [$community, $firstCourse])
                                : route('communities.classroom.index', $community);
                        @endphp
                        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between hover:border-slate-300 transition space-y-4">
                            <div>
                                <!-- Community Identity & Status Header -->
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h2 class="font-bold text-base sm:text-lg text-slate-900 truncate">
                                            <a href="{{ route('communities.show', $community) }}" class="hover:text-brand transition focus:outline-none focus-visible:underline">
                                                {{ $community->name }}
                                            </a>
                                        </h2>
                                        <p class="text-xs text-slate-500 mt-0.5">
                                            {{ $community->memberships_count }} {{ __('members') }}
                                        </p>
                                    </div>

                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold shrink-0 {{ $community->isActive() ? 'bg-emerald-500/10 text-emerald-700 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-700 border border-amber-500/20' }}">
                                        @if ($community->isActive())
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                        @endif
                                        {{ $community->isActive() ? __('Active') : __($community->status) }}
                                    </span>
                                </div>
                            </div>

                            <!-- 8 Direct Management Tools -->
                            <div class="pt-3 border-t border-slate-100">
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2">
                                    {{ __('Direct Tools') }}
                                </span>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5 text-xs">
                                    <!-- 1. Overview -->
                                    <a href="{{ route('communities.manage', $community) }}"
                                       title="{{ __('Overview') }}"
                                       aria-label="{{ __('Overview') }} - {{ $community->name }}"
                                       class="min-h-[44px] flex flex-col items-center justify-center p-2 rounded-xl bg-slate-50 hover:bg-brand-50 hover:text-brand text-slate-700 font-semibold transition border border-slate-100 shadow-2xs">
                                        <span>{{ __('Overview') }}</span>
                                    </a>

                                    <!-- 2. Courses -->
                                    <a href="{{ $courseManageUrl }}"
                                       title="{{ __('Courses') }}"
                                       aria-label="{{ __('Courses') }} - {{ $community->name }}"
                                       class="min-h-[44px] flex flex-col items-center justify-center p-2 rounded-xl bg-slate-50 hover:bg-brand-50 hover:text-brand text-slate-700 font-semibold transition border border-slate-100 shadow-2xs">
                                        <span>{{ __('Courses') }}</span>
                                    </a>

                                    <!-- 3. Members -->
                                    <a href="{{ route('communities.members.index', $community) }}"
                                       title="{{ __('Members') }}"
                                       aria-label="{{ __('Members') }} - {{ $community->name }}"
                                       class="min-h-[44px] flex flex-col items-center justify-center p-2 rounded-xl bg-slate-50 hover:bg-brand-50 hover:text-brand text-slate-700 font-semibold transition border border-slate-100 shadow-2xs">
                                        <span>{{ __('Members') }}</span>
                                    </a>

                                    <!-- 4. Invitations -->
                                    <a href="{{ route('communities.invitations.index', $community) }}"
                                       title="{{ __('Invitations') }}"
                                       aria-label="{{ __('Invitations') }} - {{ $community->name }}"
                                       class="min-h-[44px] flex flex-col items-center justify-center p-2 rounded-xl bg-slate-50 hover:bg-brand-50 hover:text-brand text-slate-700 font-semibold transition border border-slate-100 shadow-2xs">
                                        <span>{{ __('Invitations') }}</span>
                                    </a>

                                    <!-- 5. Reports -->
                                    <a href="{{ route('communities.reports.index', $community) }}"
                                       title="{{ __('Reports') }}"
                                       aria-label="{{ __('Reports') }} - {{ $community->name }}"
                                       class="min-h-[44px] flex flex-col items-center justify-center p-2 rounded-xl bg-slate-50 hover:bg-brand-50 hover:text-brand text-slate-700 font-semibold transition border border-slate-100 shadow-2xs">
                                        <span>{{ __('Reports') }}</span>
                                    </a>

                                    <!-- 6. Orders -->
                                    <a href="{{ route('communities.orders.index', $community) }}"
                                       title="{{ __('Orders') }}"
                                       aria-label="{{ __('Orders') }} - {{ $community->name }}"
                                       class="min-h-[44px] flex flex-col items-center justify-center p-2 rounded-xl bg-slate-50 hover:bg-brand-50 hover:text-brand text-slate-700 font-semibold transition border border-slate-100 shadow-2xs">
                                        <span>{{ __('Orders') }}</span>
                                    </a>

                                    <!-- 7. Submissions (Proof of Work) -->
                                    <a href="{{ route('communities.manage.proof-of-work.index', $community) }}"
                                       title="{{ __('Submissions') }}"
                                       aria-label="{{ __('Submissions') }} - {{ $community->name }}"
                                       class="min-h-[44px] flex flex-col items-center justify-center p-2 rounded-xl bg-slate-50 hover:bg-brand-50 hover:text-brand text-slate-700 font-semibold transition border border-slate-100 shadow-2xs">
                                        <span>{{ __('Submissions') }}</span>
                                    </a>

                                    <!-- 8. Settings -->
                                    <a href="{{ route('communities.edit', $community) }}"
                                       title="{{ __('Settings') }}"
                                       aria-label="{{ __('Settings') }} - {{ $community->name }}"
                                       class="min-h-[44px] flex flex-col items-center justify-center p-2 rounded-xl bg-slate-50 hover:bg-brand-50 hover:text-brand text-slate-700 font-semibold transition border border-slate-100 shadow-2xs">
                                        <span>{{ __('Settings') }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

