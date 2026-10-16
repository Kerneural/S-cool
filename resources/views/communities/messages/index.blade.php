<x-app-layout>
    <div class="max-w-7xl mx-auto py-8 sm:py-10 px-4 sm:px-6 lg:px-8 space-y-6">
        <x-community-workspace :community="$community" active-tab="messages" />

        @if (session('status'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm shadow-xs">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h1 class="text-lg font-bold text-slate-900">{{ __('Community Messages') }}</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        {{ __('Private, asynchronous 1-to-1 conversations with eligible community members.') }}
                    </p>
                </div>
                @if ($totalUnread > 0)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-500 text-white shadow-xs">
                        {{ $totalUnread }} {{ __('unread') }}
                    </span>
                @endif
            </div>

            @if ($conversations->isEmpty())
                <div class="p-12 text-center text-slate-400 space-y-3">
                    <div class="w-12 h-12 mx-auto rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <p class="text-base font-semibold text-slate-900">{{ __('No conversations yet.') }}</p>
                    <p class="text-xs sm:text-sm text-slate-500 max-w-sm mx-auto">
                        {{ __('Visit the Member Directory to start a conversation with any active member.') }}
                    </p>
                    <div class="pt-2">
                        <a href="{{ route('communities.directory.index', $community) }}"
                           class="inline-flex items-center px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold rounded-xl shadow-xs transition active:scale-[0.99]">
                            {{ __('Open Member Directory') }}
                        </a>
                    </div>
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($conversations as $conv)
                        @php
                            $other = $conv->getOtherParticipant(auth()->user());
                            $unread = $conv->unread_count;
                            $latest = $conv->latestMessage;
                        @endphp
                        <a href="{{ route('communities.messages.show', [$community, $conv]) }}"
                           class="p-4 sm:p-5 flex items-center justify-between hover:bg-slate-50/75 transition block group">
                            <div class="flex items-center space-x-4 min-w-0 flex-1">
                                <img src="{{ route('communities.directory.avatar', [$community, $other]) }}"
                                     alt="{{ $other->name }}"
                                     class="w-12 h-12 rounded-full object-cover border border-slate-200 shrink-0 bg-slate-100 group-hover:scale-105 transition-transform">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <p class="font-bold text-sm text-slate-900 truncate group-hover:text-brand-600 transition">{{ $other->name }}</p>
                                        @if ($community->isCreator($other))
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-brand-50 text-brand-700 border border-brand-200">
                                                {{ __('Creator') }}
                                            </span>
                                        @endif
                                        @if ($unread > 0)
                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-rose-500 text-white">
                                                {{ $unread }}
                                            </span>
                                        @endif
                                    </div>
                                    @if ($latest)
                                        <p class="text-xs text-slate-500 truncate mt-1 {{ $unread > 0 ? 'font-semibold text-slate-800' : '' }}">
                                            {{ Str::limit($latest->body, 90) }}
                                        </p>
                                    @else
                                        <p class="text-xs text-slate-400 italic mt-1">{{ __('No messages yet') }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="text-right text-xs text-slate-400 shrink-0 ml-4">
                                @if ($latest)
                                    <span>{{ $latest->created_at->diffForHumans() }}</span>
                                @endif
                                <svg class="w-4 h-4 text-slate-300 group-hover:text-brand-600 group-hover:translate-x-0.5 transition-all mt-1 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </a>
                    @endforeach
                </div>
                @if ($conversations->hasPages())
                    <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                        {{ $conversations->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
