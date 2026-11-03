<x-app-layout>
    <div class="py-8 sm:py-10 bg-canvas">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Hub Header Card -->
            <div class="bg-surface p-6 rounded-2xl border border-line shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-ink tracking-tight">
                        {{ __('Website Management') }}
                    </h1>
                    <p class="text-xs sm:text-sm text-muted mt-1">
                        {{ __('Manage all your communities, courses, members, and operations from a single dashboard.') }}
                    </p>
                </div>

                <div class="flex items-center flex-wrap gap-2.5 self-start md:self-center shrink-0">
                    <a href="{{ route('admin.communities.index') }}"
                       class="min-h-[44px] inline-flex items-center justify-center px-4 py-2 bg-slate-50 hover:bg-slate-100 text-slate-700 border border-line text-sm font-semibold rounded-xl shadow-2xs transition active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2">
                        <svg class="w-4 h-4 mr-2 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>{{ __('Platform Admin') }}</span>
                    </a>
                </div>
            </div>

            <!-- Communities Cards List -->
            @if ($communities->isEmpty())
                <div class="bg-surface rounded-2xl border border-line p-12 text-center shadow-xs">
                    <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto text-slate-400 mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h2 class="text-base font-bold text-ink">{{ __('No communities found.') }}</h2>
                    <p class="text-xs sm:text-sm text-muted mt-1 max-w-sm mx-auto">
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
                            $coverUrl = ($community->isActive() && $community->cover_path)
                                ? route('communities.cover.show', $community)
                                : null;
                        @endphp
                        <div class="bg-surface rounded-2xl border border-line p-5 shadow-xs flex flex-col justify-between hover:border-brand/40 hover:shadow-sm transition-all duration-200 space-y-4">
                            <div>
                                <!-- Community Identity Header (Real community image + role badge) -->
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <!-- Real Community Cover / Thumbnail Frame -->
                                        <x-media-frame :src="$coverUrl" shape="thumb" :busy="false" class="w-12 h-12 shrink-0 rounded-xl border border-line shadow-2xs overflow-hidden">
                                            <span class="w-full h-full bg-brand-soft border border-brand/20 flex items-center justify-center text-brand font-bold text-base">
                                                {{ mb_strtoupper(mb_substr($community->name, 0, 1)) }}
                                            </span>
                                        </x-media-frame>

                                        <div class="min-w-0">
                                            <h2 class="font-bold text-base text-ink truncate">
                                                <a href="{{ route('communities.show', $community) }}" class="hover:text-brand transition focus:outline-none focus-visible:underline">
                                                    {{ $community->name }}
                                                </a>
                                            </h2>
                                            <div class="flex items-center gap-1.5 mt-1">
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                                    Admin
                                                </span>
                                                <span class="text-slate-300 text-xs">•</span>
                                                <span class="text-xs text-muted">
                                                    {{ $community->memberships_count }} {{ __('members') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Status Badge -->
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold shrink-0 {{ $community->isActive() ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                                        @if ($community->isActive())
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                        @endif
                                        {{ $community->isActive() ? __('Active') : __($community->status) }}
                                    </span>
                                </div>
                            </div>

                            <!-- 4 Direct Management Tools for Admin -->
                            <div class="pt-3 border-t border-line/60">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[11px] font-bold text-muted uppercase tracking-wider">
                                        {{ __('Direct Tools') }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                                    <!-- 1. Overview -->
                                    <a href="{{ route('communities.manage', $community) }}"
                                       title="{{ __('Overview') }}"
                                       aria-label="{{ __('Overview') }} - {{ $community->name }}"
                                       class="min-h-[44px] flex flex-col items-center justify-center p-2 rounded-xl bg-slate-50/80 hover:bg-brand-soft hover:text-brand hover:border-brand/30 text-muted font-medium transition-all border border-line shadow-2xs group">
                                        <svg class="w-4 h-4 mb-1 text-slate-400 group-hover:text-brand transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                        </svg>
                                        <span>{{ __('Overview') }}</span>
                                    </a>

                                    <!-- 2. Members -->
                                    <a href="{{ route('communities.members.index', $community) }}"
                                       title="{{ __('Members') }}"
                                       aria-label="{{ __('Members') }} - {{ $community->name }}"
                                       class="min-h-[44px] flex flex-col items-center justify-center p-2 rounded-xl bg-slate-50/80 hover:bg-brand-soft hover:text-brand hover:border-brand/30 text-muted font-medium transition-all border border-line shadow-2xs group">
                                        <svg class="w-4 h-4 mb-1 text-slate-400 group-hover:text-brand transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        <span>{{ __('Members') }}</span>
                                    </a>

                                    <!-- 3. Orders -->
                                    <a href="{{ route('communities.orders.index', $community) }}"
                                       title="{{ __('Orders') }}"
                                       aria-label="{{ __('Orders') }} - {{ $community->name }}"
                                       class="min-h-[44px] flex flex-col items-center justify-center p-2 rounded-xl bg-slate-50/80 hover:bg-brand-soft hover:text-brand hover:border-brand/30 text-muted font-medium transition-all border border-line shadow-2xs group">
                                        <svg class="w-4 h-4 mb-1 text-slate-400 group-hover:text-brand transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                        </svg>
                                        <span>{{ __('Orders') }}</span>
                                    </a>

                                    <!-- 4. Settings -->
                                    <a href="{{ route('communities.edit', $community) }}"
                                       title="{{ __('Settings') }}"
                                       aria-label="{{ __('Settings') }} - {{ $community->name }}"
                                       class="min-h-[44px] flex flex-col items-center justify-center p-2 rounded-xl bg-slate-50/80 hover:bg-brand-soft hover:text-brand hover:border-brand/30 text-muted font-medium transition-all border border-line shadow-2xs group">
                                        <svg class="w-4 h-4 mb-1 text-slate-400 group-hover:text-brand transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
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
