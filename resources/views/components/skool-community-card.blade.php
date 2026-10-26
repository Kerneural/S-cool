@props([
    'community',
])

<div x-data="{
    showJoinModal: @js($errors->has('email')),
    email: '{{ auth()->user()?->email ?? '' }}',
    answer: ''
}" class="relative">
    @php
        $compact = $variant === 'compact';
        $coverUrl = $community->cover_path ? route('communities.cover.show', $community) : null;
    @endphp
    <div @class(['space-y-3' => $compact, 'bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden' => ! $compact])>
        @if ($compact)
            {{-- Small identity image: the Feed never repeats a full-size shell cover. --}}
            <div class="flex items-center gap-3">
                <x-media-frame :src="$coverUrl" shape="thumb" :busy="false" class="h-12 w-12 shrink-0 rounded-xl border border-line">
                    <span class="text-sm font-bold text-brand">{{ mb_strtoupper(mb_substr($community->name, 0, 1)) }}</span>
                </x-media-frame>
                <div class="min-w-0">
                    <h3 class="break-words text-sm font-bold text-ink">{{ $community->name }}</h3>
                    <p class="text-xs text-muted">{{ __('Invitation-only community') }}</p>
                </div>
            </div>
        @else
            <x-media-frame :src="$coverUrl" alt="" class="w-full">
                <span class="w-full h-full bg-gradient-to-br from-brand-700 via-teal-800 to-slate-900 flex items-center justify-center p-4">
                    <span class="text-white/90 text-sm font-bold line-clamp-1">{{ $community->name }}</span>
                </span>
            </x-media-frame>
        @endif

        <div @class(['space-y-3' => $compact, 'p-5 space-y-4' => ! $compact])>
            @unless ($compact)
                <div>
                    <h3 class="text-lg font-bold text-slate-900 leading-snug break-words">{{ $community->name }}</h3>
                    <p class="text-xs text-muted mt-0.5">{{ __('Invitation-only community') }}</p>
                </div>
            @endunless

            @if ($community->description)
                <p class="text-sm text-slate-700 leading-relaxed line-clamp-3 break-words">{{ $community->description }}</p>
            @endif

            <dl class="grid grid-cols-2 text-center py-2.5 border-y border-slate-100">
                <div class="min-w-0 px-2 flex flex-col">
                    <dt class="text-xs text-slate-500 font-medium">{{ __('Members') }}</dt>
                    <dd class="order-first text-lg font-bold text-slate-900 tabular-nums">{{ $formattedMemberCount }}</dd>
                </div>
                <div class="min-w-0 px-2 flex flex-col border-l border-slate-200">
                    <dt class="text-xs text-slate-500 font-medium">{{ __('Admins') }}</dt>
                    <dd class="order-first text-lg font-bold text-slate-900 tabular-nums">{{ $adminCount }}</dd>
                </div>
            </dl>

            @if ($sampleMembers->isNotEmpty())
                {{-- Decorative sample; names stay in the member directory. --}}
                <div class="flex items-center -space-x-1.5 overflow-hidden py-1" role="img" aria-label="{{ __('Recent members') }}">
                    @foreach ($sampleMembers as $member)
                        <x-media-frame :src="$member->avatar_path ? route('communities.directory.avatar', [$community, $member]) : null" shape="avatar" loading="lazy" class="h-8 w-8 shrink-0 ring-2 ring-white" aria-hidden="true">
                            <span class="text-xs font-bold text-slate-700">{{ mb_strtoupper(mb_substr($member->name ?? '?', 0, 1)) }}</span>
                        </x-media-frame>
                    @endforeach
                </div>
            @endif

            @if ($isCreator)
                <p class="rounded-xl border border-brand-200 bg-brand-soft px-4 py-2.5 text-center text-sm font-semibold text-brand">{{ __('You host this community') }}</p>
            @elseif ($isMember)
                <p class="flex items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-800">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ __('Joined') }}</span>
                </p>
            @else
                <button type="button"
                        @click="showJoinModal = true"
                        class="w-full min-h-11 rounded-xl bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-brand-hover">
                    {{ __('Join community') }}
                </button>
            @endif

            <a href="{{ route('communities.show', $community) }}" class="inline-flex min-h-11 items-center text-sm font-semibold text-brand underline-offset-2 hover:underline">{{ __('About this community') }}</a>
        </div>
    </div>

    <!-- Join Community Modal (Figure 2) -->
    @if (! $isCreator && ! $isMember)
        <div x-show="showJoinModal"
             x-cloak
             @keydown.escape.window="showJoinModal = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
             role="dialog"
             aria-modal="true"
             aria-labelledby="join-modal-title">

            <!-- Modal Dialog Box -->
            <div @click.away="showJoinModal = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-1"
                 class="bg-white rounded-2xl max-w-lg w-full p-6 sm:p-8 relative shadow-2xl border border-slate-100">

                <!-- Close Button -->
                <button type="button"
                        @click="showJoinModal = false"
                        class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition p-1 text-2xl leading-none"
                        aria-label="{{ __('Close') }}">
                    &times;
                </button>

                <!-- Top Globe Icon -->
                <div class="mx-auto w-14 h-14 rounded-full border border-slate-300 flex items-center justify-center text-slate-900 mb-3">
                    <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="2" y1="12" x2="22" y2="12"></line>
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                    </svg>
                </div>

                <!-- Header Titles -->
                <h3 id="join-modal-title" class="text-xl font-bold text-slate-900 text-center">
                    {{ $community->name }}
                </h3>
                <p class="text-sm text-slate-500 text-center mt-1">
                    {{ __('An invitation from the creator is required to join this private community.') }}
                </p>

                <!-- Join Form -->
                <form method="POST" action="{{ route('communities.join', $community) }}" class="mt-6 space-y-4">
                    @csrf
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />

                    <!-- Question 1: Email Address -->
                    <div class="space-y-1.5 text-left">
                        <label for="join-email-{{ $community->id }}" class="block text-sm font-semibold text-slate-900">
                            {{ __('What is your email address?') }}
                        </label>
                        <input id="join-email-{{ $community->id }}"
                               type="email"
                               name="email"
                               x-model="email"
                               required
                               placeholder="{{ __('Email address') }}"
                               class="w-full text-sm rounded-lg border-slate-300 focus:border-slate-800 focus:ring-1 focus:ring-slate-800 py-2.5 px-3 bg-white">
                    </div>

                    <!-- Question 2: Referral Source -->
                    <div class="space-y-1.5 text-left">
                        <label for="join-referral-{{ $community->id }}" class="block text-sm font-semibold text-slate-900">
                            {{ __('Where did you hear about us? The more specific the better :)') }}
                        </label>
                        <textarea id="join-referral-{{ $community->id }}"
                                  name="referral_source"
                                  x-model="answer"
                                  maxlength="200"
                                  rows="3"
                                  placeholder="{{ __('Your answer') }}"
                                  class="w-full text-sm rounded-lg border-slate-300 focus:border-slate-800 focus:ring-1 focus:ring-slate-800 py-2.5 px-3 bg-white resize-none"></textarea>
                        <div class="flex justify-end text-xs text-slate-400">
                            <span x-text="answer.length">0</span> / 200
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                            :disabled="!email || email.trim() === ''"
                            class="w-full mt-4 min-h-11 rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-brand-hover disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-500">
                        {{ __('Join community') }}
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
