@props([
    'community',
])

<div x-data="{
    showJoinModal: false,
    email: '{{ auth()->user()?->email ?? '' }}',
    answer: ''
}" class="relative">
    <!-- Skool Right Sidebar Community Card (Figure 1) -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
        <!-- Cover Image (16:9 Aspect Ratio) -->
        <div class="aspect-[16/9] w-full overflow-hidden bg-slate-900 relative">
            @if ($community->cover_path)
                <img src="{{ route('communities.cover.show', $community) }}" alt="{{ $community->name }}" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full bg-gradient-to-br from-brand-700 via-teal-800 to-slate-900 flex items-center justify-center p-4">
                    <span class="text-white/90 text-sm font-bold uppercase tracking-wider line-clamp-1">{{ $community->name }}</span>
                </div>
            @endif
        </div>

        <!-- Card Content Body -->
        <div class="p-5 sm:p-6 space-y-4">
            <!-- Community Title & Domain/Slug -->
            <div>
                <h3 class="text-xl font-bold text-slate-900 leading-snug">
                    {{ $community->name }}
                </h3>
                <div class="text-xs text-slate-400 font-medium mt-0.5">
                    skool.com/{{ $community->slug }}
                </div>
            </div>

            <!-- Description -->
            <p class="text-sm text-slate-700 leading-relaxed line-clamp-3">
                {{ $community->description ?: __('Learn to get paid for AI solutions, regardless of your background.') }}
            </p>

            <!-- Statistics (Members | Online | Admins) arranged horizontally like Image 2 -->
            <div class="flex items-center text-center py-2.5 border-y border-slate-100">
                <div class="flex-1 min-w-0 px-2">
                    <div class="text-xl sm:text-2xl font-bold text-slate-900 tabular-nums truncate">
                        {{ $formattedMemberCount }}
                    </div>
                    <div class="text-xs text-slate-500 font-medium mt-0.5 truncate">
                        {{ __('Members') }}
                    </div>
                </div>

                <div class="w-px h-8 bg-slate-200 shrink-0"></div>

                <div class="flex-1 min-w-0 px-2">
                    <div class="text-xl sm:text-2xl font-bold text-slate-900 tabular-nums truncate">
                        {{ $formattedOnlineCount }}
                    </div>
                    <div class="text-xs text-slate-500 font-medium mt-0.5 truncate">
                        {{ __('Online') }}
                    </div>
                </div>

                <div class="w-px h-8 bg-slate-200 shrink-0"></div>

                <div class="flex-1 min-w-0 px-2">
                    <div class="text-xl sm:text-2xl font-bold text-slate-900 tabular-nums truncate">
                        {{ $adminCount }}
                    </div>
                    <div class="text-xs text-slate-500 font-medium mt-0.5 truncate">
                        {{ __('Admins') }}
                    </div>
                </div>
            </div>

            <!-- Real Members Avatars Row -->
            <div class="flex items-center -space-x-1.5 overflow-hidden py-1">
                @forelse ($sampleMembers as $member)
                    @if ($member->avatar_path)
                        <img class="inline-block h-8 w-8 rounded-full ring-2 ring-white object-cover"
                             src="{{ route('communities.directory.avatar', [$community, $member]) }}"
                             alt="{{ $member->name }}"
                             title="{{ $member->name }}">
                    @else
                        <div class="inline-flex items-center justify-center h-8 w-8 rounded-full ring-2 ring-white bg-slate-100 text-slate-700 font-bold text-xs"
                             title="{{ $member->name }}">
                            {{ strtoupper(substr($member->name ?? '?', 0, 1)) }}
                        </div>
                    @endif
                @empty
                    @if ($community->creator)
                        <div class="inline-flex items-center justify-center h-8 w-8 rounded-full ring-2 ring-white bg-amber-100 text-amber-800 font-bold text-xs"
                             title="{{ $community->creator->name }}">
                            {{ strtoupper(substr($community->creator->name, 0, 1)) }}
                        </div>
                    @endif
                @endforelse
            </div>

            <!-- Join Community Button (only for member / candidate accounts, not creator) -->
            @if (! $isCreator)
                @if ($isMember)
                    <div class="w-full py-3 px-4 bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold text-sm tracking-wide rounded-xl text-center flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ __('JOINED') }}</span>
                    </div>
                @else
                    <button type="button"
                            @click="showJoinModal = true"
                            class="w-full py-3.5 px-4 bg-[#F5C347] hover:bg-[#eab308] text-slate-900 font-bold text-sm tracking-wider uppercase rounded-xl shadow-xs transition duration-150 text-center cursor-pointer active:scale-[0.99]">
                        {{ __('JOIN GROUP') }}
                    </button>
                @endif
            @endif
        </div>
    </div>

    <!-- Join Community Modal (Figure 2) -->
    @if (! $isCreator && ! $isMember)
        <div x-show="showJoinModal"
             x-cloak
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
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
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
                    {{ __('Answer questions to join group') }}
                </p>

                <!-- Join Form -->
                <form method="POST" action="{{ route('communities.join', $community) }}" class="mt-6 space-y-4">
                    @csrf

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
                            :class="email && email.trim() !== '' ? 'bg-[#F5C347] hover:bg-[#eab308] text-slate-900 cursor-pointer shadow-xs' : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                            class="w-full mt-4 py-3.5 px-4 font-bold text-sm tracking-wider uppercase rounded-lg transition duration-150 text-center">
                        {{ __('JOIN GROUP') }}
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
