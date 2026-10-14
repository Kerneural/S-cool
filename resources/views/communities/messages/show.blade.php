<x-app-layout>
    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">
        <x-community-workspace :community="$community" active-tab="messages" />

        @if (session('status'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col min-h-[500px]">
            <!-- Conversation Header -->
            <div class="p-4 sm:p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('communities.messages.index', $community) }}"
                       aria-label="{{ __('Back to inbox') }}"
                       class="inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition mr-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>

                    <img src="{{ route('communities.directory.avatar', [$community, $otherUser]) }}"
                         alt="{{ $otherUser->name }}"
                         class="w-10 h-10 rounded-full object-cover border border-gray-200 bg-gray-50">

                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-base text-gray-900">{{ $otherUser->name }}</h3>
                            @if ($community->isCreator($otherUser))
                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold bg-purple-100 text-purple-800">
                                    {{ __('Creator') }}
                                </span>
                            @endif
                        </div>
                        <a href="{{ route('communities.directory.show', [$community, $otherUser]) }}"
                           class="text-xs text-indigo-600 hover:text-indigo-800 transition">
                            {{ __('View Profile') }} &rarr;
                        </a>
                    </div>
                </div>

                <div class="text-xs text-gray-400">
                    {{ __('Asynchronous Messaging') }}
                </div>
            </div>

            <!-- Messages Stream -->
            <div class="p-4 sm:p-6 flex-1 space-y-4 overflow-y-auto">
                @if ($messages->isEmpty())
                    <div class="p-12 text-center text-gray-400 text-sm">
                        {{ __('No messages in this conversation yet. Send the first message below.') }}
                    </div>
                @else
                    @foreach ($messages as $msg)
                        @php
                            $isMe = (int) $msg->sender_id === (int) auth()->id();
                        @endphp
                        <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }}">
                            <div class="max-w-[85%] sm:max-w-xl rounded-2xl px-4 py-3 text-sm {{ $isMe ? 'bg-indigo-600 text-white rounded-br-none shadow-sm' : 'bg-gray-100 text-gray-800 rounded-bl-none' }}">
                                <p class="whitespace-pre-line leading-relaxed break-words">{{ $msg->body }}</p>
                            </div>
                            <div class="flex items-center gap-2 mt-1 px-1 text-[11px] text-gray-400">
                                <span>{{ $msg->created_at->format('M d, H:i') }}</span>
                                @if ($isMe)
                                    <span>·</span>
                                    <span>{{ $msg->isRead() ? __('Read') : __('Sent') }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <div class="pt-2">
                        {{ $messages->links() }}
                    </div>
                @endif
            </div>

            <!-- Reply Box -->
            @php
                $retryId = old('client_send_id');
                if (! is_string($retryId) || trim($retryId) === '' || Str::length($retryId) > 64) {
                    $retryId = (string) Str::uuid();
                }
                $draft = old('body', '');
                $draft = is_string($draft) ? $draft : '';
            @endphp
            <div class="p-4 border-t border-gray-100 bg-gray-50/50" x-data="{ body: @js($draft) }">
                <form method="POST" action="{{ route('communities.messages.store', [$community, $conversation]) }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="client_send_id" value="{{ $retryId }}">
                    <x-input-error :messages="$errors->get('client_send_id')" class="mt-1" />

                    <div>
                        <label for="message-body" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Message') }}</label>
                        <textarea
                            id="message-body"
                            name="body"
                            x-model="body"
                            maxlength="2000"
                            rows="3"
                            required
                            placeholder="{{ __('Type your message here... (press Send to deliver)') }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ $draft }}</textarea>
                        <x-input-error :messages="$errors->get('body')" class="mt-1" />
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-400">
                            <span x-text="body.length">0</span> / 2000 {{ __('characters') }}
                        </span>

                        <button
                            type="submit"
                            :disabled="body.trim().length === 0"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-xs font-semibold uppercase tracking-wider rounded-md shadow-sm transition">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            {{ __('Send Message') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
