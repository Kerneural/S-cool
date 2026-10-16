<x-app-layout>
    <div class="py-8 sm:py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-community-workspace :community="$community" active-tab="directory" />

            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row sm:items-start gap-6">
                    <img src="{{ route('communities.directory.avatar', [$community, $member]) }}"
                         alt="{{ $member->name }}"
                         class="w-24 h-24 sm:w-28 sm:h-28 rounded-full object-cover border-2 border-slate-200 shadow-sm shrink-0 bg-slate-100">

                    <div class="space-y-4 flex-1">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h1 class="text-2xl font-bold text-slate-900">{{ $member->name }}</h1>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $role === 'Creator' ? 'bg-brand-50 text-brand-700 border border-brand-200' : 'bg-slate-100 text-slate-700' }}">
                                        {{ $role === 'Creator' ? __('Community Creator') : __('Community Member') }}
                                    </span>
                                    @if ($joinedAt)
                                        <span class="text-xs text-slate-400">
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
                                        <button type="submit" class="inline-flex items-center px-3.5 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold uppercase tracking-wider transition shadow-sm active:scale-[0.99]">
                                            {{ __('Message') }}
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ route('communities.directory.index', $community) }}"
                                   class="inline-flex items-center px-3.5 py-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-semibold text-slate-700 uppercase tracking-wider transition">
                                    &larr; {{ __('Back to Directory') }}
                                </a>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100">
                            <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">{{ __('About') }}</h4>
                            <p class="text-slate-700 text-sm leading-relaxed whitespace-pre-line">
                                {{ $member->bio ?: __('This member has not written a bio yet.') }}
                            </p>
                        </div>

                        <div class="pt-4 border-t border-slate-100 text-xs text-slate-400">
                            {{ __('Viewing community profile in :community.', ['community' => $community->name]) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
