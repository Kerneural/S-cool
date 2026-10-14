<x-app-layout>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">
        <x-community-workspace :community="$community" active-tab="messages" />

        @if (session('status'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Community Messages') }}</h3>
                    <p class="text-xs text-gray-500">
                        {{ __('Private, asynchronous 1-to-1 conversations with eligible community members.') }}
                    </p>
                </div>
                @if ($totalUnread > 0)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-600 text-white">
                        {{ $totalUnread }} {{ __('unread') }}
                    </span>
                @endif
            </div>

            @if ($conversations->isEmpty())
                <div class="p-12 text-center text-gray-400 space-y-3">
                    <svg class="w-12 h-12 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <p class="text-sm font-medium">{{ __('No conversations yet.') }}</p>
                    <p class="text-xs text-gray-400">
                        {{ __('Visit the Member Directory to start a conversation with any active member.') }}
                    </p>
                    <div class="pt-2">
                        <a href="{{ route('communities.directory.index', $community) }}"
                           class="inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-md shadow-sm transition">
                            {{ __('Open Member Directory') }}
                        </a>
                    </div>
                </div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach ($conversations as $conv)
                        @php
                            $other = $conv->getOtherParticipant(auth()->user());
                            $unread = $conv->unread_count;
                            $latest = $conv->latestMessage;
                        @endphp
                        <a href="{{ route('communities.messages.show', [$community, $conv]) }}"
                           class="p-4 sm:p-5 flex items-center justify-between hover:bg-gray-50/75 transition block">
                            <div class="flex items-center space-x-4 min-w-0 flex-1">
                                <img src="{{ route('communities.directory.avatar', [$community, $other]) }}"
                                     alt="{{ $other->name }}"
                                     class="w-11 h-11 rounded-full object-cover border border-gray-200 shrink-0 bg-gray-50">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <p class="font-semibold text-sm text-gray-900 truncate">{{ $other->name }}</p>
                                        @if ($community->isCreator($other))
                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold bg-purple-100 text-purple-800">
                                                {{ __('Creator') }}
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500 truncate mt-0.5">
                                        @if ($latest)
                                            <span class="{{ $latest->sender_id === auth()->id() ? 'text-gray-400' : 'font-medium text-gray-700' }}">
                                                {{ $latest->sender_id === auth()->id() ? __('You: ') : '' }}
                                            </span>
                                            {{ Str::limit($latest->body, 60) }}
                                        @else
                                            <span class="italic text-gray-400">{{ __('No messages yet') }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="text-right shrink-0 ml-4 flex flex-col items-end gap-1">
                                @if ($conv->last_message_at)
                                    <span class="text-xs text-gray-400">
                                        {{ $conv->last_message_at->format('M d, H:i') }}
                                    </span>
                                @endif
                                @if ($unread > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-600 text-white">
                                        {{ $unread }}
                                    </span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="p-4 border-t border-gray-100">
                    {{ $conversations->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
