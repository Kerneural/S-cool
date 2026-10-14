<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-community-workspace :community="$community" active-tab="directory" />

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row sm:items-start gap-6">
                    <img src="{{ route('communities.directory.avatar', [$community, $member]) }}"
                         alt="{{ $member->name }}"
                         class="w-24 h-24 sm:w-28 sm:h-28 rounded-full object-cover border-2 border-gray-200 shadow-sm shrink-0 bg-gray-50">

                    <div class="space-y-4 flex-1">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900">{{ $member->name }}</h3>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $role === 'Creator' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $role === 'Creator' ? __('Community Creator') : __('Community Member') }}
                                    </span>
                                    @if ($joinedAt)
                                        <span class="text-xs text-gray-500">
                                            {{ __('Joined') }} {{ $joinedAt->format('M d, Y') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                @if (auth()->id() !== $member->id)
                                    <form method="POST" action="{{ route('communities.messages.start', $community) }}">
                                        @csrf
                                        <input type="hidden" name="recipient_id" value="{{ $member->id }}">
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-xs font-semibold uppercase tracking-wider transition">
                                            {{ __('Message') }}
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ route('communities.directory.index', $community) }}"
                                   class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-md text-xs font-semibold text-gray-700 uppercase tracking-wider transition">
                                    &larr; {{ __('Back to Directory') }}
                                </a>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100">
                            <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">{{ __('About') }}</h4>
                            <p class="text-gray-700 text-sm leading-relaxed whitespace-pre-line">
                                {{ $member->bio ?: __('This member has not written a bio yet.') }}
                            </p>
                        </div>

                        <div class="pt-4 border-t border-gray-100 text-xs text-gray-400">
                            {{ __('Viewing community profile in :community.', ['community' => $community->name]) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
