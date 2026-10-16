<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <nav class="flex items-center space-x-2 text-xs sm:text-sm text-slate-500">
                <a class="hover:text-brand-600 transition" href="{{ route('communities.classroom.index', $community) }}">
                    {{ __('Classroom') }}
                </a>
                <span class="text-slate-300">/</span>
                <a class="hover:text-brand-600 transition" href="{{ route('communities.manage.proof-of-work.index', $community) }}">
                    {{ __('Review Queue') }}
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-slate-900 font-semibold">{{ __('Submission #:id', ['id' => $proofOfWork->id]) }}</span>
            </nav>
            <a class="text-xs sm:text-sm font-semibold text-brand-600 hover:text-brand-700 transition" href="{{ route('communities.manage.proof-of-work.index', $community) }}">
                &larr; {{ __('Back to Review Queue') }}
            </a>
        </div>
    </x-slot>

    <div class="mx-auto min-w-0 max-w-5xl break-words [overflow-wrap:anywhere] space-y-6 px-4 py-8 sm:px-6">
        <x-classroom-feedback />

        {{-- Submission Header Card --}}
        <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-brand-50 text-brand-700 font-bold flex items-center justify-center text-base shrink-0 border border-brand-200">
                        {{ strtoupper(substr($proofOfWork->user->name ?? 'U', 0, 1)) }}
                    </div>
                    <div>
                        <h1 class="text-lg sm:text-xl font-bold text-slate-900">{{ $proofOfWork->user->name ?? __('Unknown') }}</h1>
                        <p class="text-xs text-slate-400">{{ $proofOfWork->user->email ?? '' }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold
                        @if ($proofOfWork->isVerified()) bg-emerald-100 text-emerald-800 border border-emerald-300
                        @elseif ($proofOfWork->isNeedsRevision()) bg-rose-100 text-rose-800 border border-rose-300
                        @elseif ($proofOfWork->isRejected()) bg-red-100 text-red-800 border border-red-300
                        @elseif ($proofOfWork->isWaiting()) bg-amber-100 text-amber-800 border border-amber-300
                        @else bg-slate-100 text-slate-700 border border-slate-300
                        @endif">
                        <span>{{ $proofOfWork->statusLabel() }}</span>
                        @if ($proofOfWork->revision > 1)
                            <span class="opacity-75">({{ __('Revision :rev', ['rev' => $proofOfWork->revision]) }})</span>
                        @endif
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-slate-400 block font-semibold mb-0.5">{{ __('Course') }}</span>
                    <span class="font-bold text-slate-800 truncate block">{{ $proofOfWork->course->title }}</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-slate-400 block font-semibold mb-0.5">{{ __('Lesson') }}</span>
                    <span class="font-bold text-slate-800 truncate block">{{ $proofOfWork->lesson->title }}</span>
                </div>
                <div class="p-3 bg-teal-50/70 rounded-xl border border-teal-100">
                    <span class="text-teal-600 block font-semibold mb-0.5">{{ __('Configured Skill') }}</span>
                    <span class="font-bold text-teal-900 truncate block">{{ $proofOfWork->lesson->skill_label }}</span>
                </div>
            </div>
        </div>

        {{-- Submission Evidence Details --}}
        <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200/80 shadow-xs space-y-5">
            <h2 class="text-base font-bold text-slate-900 pb-2 border-b border-slate-100">{{ __('Submitted Evidence') }}</h2>

            <div class="space-y-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">{{ __('Work Summary') }}</span>
                <p class="text-sm text-slate-800 whitespace-pre-line leading-relaxed bg-slate-50 p-4 rounded-xl border border-slate-100">
                    {{ $proofOfWork->summary }}
                </p>
            </div>

            @if ($proofOfWork->url)
                <div class="space-y-1 text-xs">
                    <span class="font-bold text-slate-400 uppercase tracking-wider block">{{ __('Project / Repository Link (HTTPS)') }}</span>
                    <a href="{{ $proofOfWork->url }}" target="_blank" rel="noopener noreferrer" class="inline-flex max-w-full items-center gap-1.5 text-brand-600 hover:text-brand-700 font-bold underline break-all">
                        <span class="min-w-0">{{ $proofOfWork->url }}</span>
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
            @endif

            @if ($proofOfWork->attachment_path)
                <div class="space-y-1.5">
                    <span class="font-bold text-slate-400 uppercase tracking-wider text-xs block">{{ __('Evidence Screenshot / Attachment') }}</span>
                    <div class="aspect-video max-w-xl overflow-hidden rounded-xl border border-slate-200 bg-slate-900 shadow-xs">
                        <img src="{{ route('communities.proof-of-work.image', [$community, $proofOfWork]) }}" alt="Proof of Work Attachment" class="w-full h-full object-contain">
                    </div>
                    <a href="{{ route('communities.proof-of-work.image', [$community, $proofOfWork]) }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-block text-xs font-semibold text-brand underline">
                        {{ __('Open original evidence image') }}
                    </a>
                </div>
            @endif
        </div>

        {{-- Review Action Form (When Waiting) --}}
        @if ($proofOfWork->isWaiting() && $proofOfWork->is_current)
            <div class="bg-white p-6 sm:p-8 rounded-2xl border border-teal-200 shadow-xs space-y-5">
                <h2 class="text-base font-bold text-slate-900 pb-2 border-b border-teal-100">{{ __('Evaluate & Provide Feedback') }}</h2>

                <form method="POST" action="{{ route('communities.manage.proof-of-work.review', [$community, $proofOfWork]) }}"
                      x-data="{ selectedStatus: @js(old('status', 'VERIFIED')) }" class="space-y-5">
                    @csrf

                    <div>
                        <span class="block text-xs font-bold text-ink uppercase tracking-wider mb-2">{{ __('Review Decision') }} <span class="text-rose-500">*</span></span>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <label class="flex items-center gap-2.5 p-3 rounded-xl border cursor-pointer transition"
                                   :class="selectedStatus === 'VERIFIED' ? 'border-emerald-500 bg-emerald-50/70 ring-1 ring-emerald-500' : 'border-slate-200 hover:bg-slate-50'">
                                <input type="radio" name="status" value="VERIFIED" x-model="selectedStatus" @checked(old('status', 'VERIFIED') === 'VERIFIED') class="text-emerald-600 focus:ring-emerald-500">
                                <div>
                                    <span class="text-xs font-bold text-emerald-900 block">{{ __('Verify Work') }}</span>
                                    <span class="text-[11px] text-emerald-700 block">{{ __('Snapshot skill & certify') }}</span>
                                </div>
                            </label>

                            <label class="flex items-center gap-2.5 p-3 rounded-xl border cursor-pointer transition"
                                   :class="selectedStatus === 'NEEDS_REVISION' ? 'border-amber-500 bg-amber-50/70 ring-1 ring-amber-500' : 'border-slate-200 hover:bg-slate-50'">
                                <input type="radio" name="status" value="NEEDS_REVISION" x-model="selectedStatus" @checked(old('status', 'VERIFIED') === 'NEEDS_REVISION') class="text-amber-600 focus:ring-amber-500">
                                <div>
                                    <span class="text-xs font-bold text-amber-900 block">{{ __('Request Revision') }}</span>
                                    <span class="text-[11px] text-amber-700 block">{{ __('Allow learner to resubmit') }}</span>
                                </div>
                            </label>

                            <label class="flex items-center gap-2.5 p-3 rounded-xl border cursor-pointer transition"
                                   :class="selectedStatus === 'REJECTED' ? 'border-red-500 bg-red-50/70 ring-1 ring-red-500' : 'border-slate-200 hover:bg-slate-50'">
                                <input type="radio" name="status" value="REJECTED" x-model="selectedStatus" @checked(old('status', 'VERIFIED') === 'REJECTED') class="text-red-600 focus:ring-red-500">
                                <div>
                                    <span class="text-xs font-bold text-red-900 block">{{ __('Reject') }}</span>
                                    <span class="text-[11px] text-red-700 block">{{ __('Terminal rejection') }}</span>
                                </div>
                            </label>
                        </div>
                        <x-input-error :messages="$errors->get('status')" class="mt-1" />
                    </div>

                    <div>
                        <label for="review-feedback" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1.5">
                            {{ __('Creator Feedback') }}
                            <span x-show="selectedStatus !== 'VERIFIED'" class="text-rose-500">* ({{ __('Required for Revision / Rejection') }})</span>
                            <span x-show="selectedStatus === 'VERIFIED'" class="text-slate-400 font-normal">({{ __('Optional encouragement') }})</span>
                        </label>
                        <textarea id="review-feedback" name="feedback" rows="4" maxlength="5000"
                                  :required="selectedStatus !== 'VERIFIED'"
                                  placeholder="{{ __('Provide clear, constructive feedback on the code or project...') }}"
                                  class="block w-full border-slate-300 focus:border-brand-500 focus:ring-brand-500 rounded-xl shadow-xs text-sm text-ink placeholder-slate-400 p-2.5 leading-relaxed">{{ is_string(old('feedback')) ? old('feedback') : '' }}</textarea>
                        <x-input-error :messages="$errors->get('feedback')" class="mt-1" />
                    </div>

                    <div class="flex justify-end pt-2">
                        <x-primary-button>
                            {{ __('Submit Review') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        @else
            {{-- Existing Review Feedback Display --}}
            <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                <h2 class="text-base font-bold text-slate-900 pb-2 border-b border-slate-100">{{ __('Review Record') }}</h2>
                <div class="flex items-center gap-3 text-xs text-slate-500">
                    <span>{{ __('Reviewed by:') }} <strong>{{ $proofOfWork->reviewer?->name ?? __('Creator') }}</strong></span>
                    @if ($proofOfWork->reviewed_at)
                        &bull; <span>{{ $proofOfWork->reviewed_at->format('M d, Y H:i') }}</span>
                    @endif
                </div>
                @if ($proofOfWork->feedback)
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-sm text-slate-800 whitespace-pre-line leading-relaxed">
                        <span class="font-bold text-slate-500 text-xs uppercase tracking-wider block mb-1">{{ __('Feedback given:') }}</span>
                        {{ $proofOfWork->feedback }}
                    </div>
                @endif
                @if ($proofOfWork->isVerified())
                    <div class="text-xs text-emerald-700 font-semibold flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span>{{ __('Skill snapshot created for EUR-51 profile and verified skills directory:') }} <strong>{{ $proofOfWork->skill_label_snapshot }}</strong></span>
                    </div>
                @endif
            </div>
        @endif

        {{-- Revision History Timeline --}}
        @if ($revisions->count() > 1)
            <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                <h2 class="text-base font-bold text-slate-900 pb-2 border-b border-slate-100">{{ __('Revision History') }}</h2>
                <div class="space-y-4">
                    @foreach ($revisions as $rev)
                        <div class="p-4 rounded-xl border {{ $rev->id === $proofOfWork->id ? 'border-brand-300 bg-brand-50/20' : 'border-slate-200 bg-slate-50/60' }} space-y-2 text-xs">
                            <div class="flex items-center justify-between font-bold">
                                <span class="text-slate-900">{{ __('Revision #:rev', ['rev' => $rev->revision]) }} {{ $rev->is_current ? __('(Current)') : '' }}</span>
                                <span class="px-2 py-0.5 rounded text-[11px]
                                    @if ($rev->isVerified()) bg-emerald-100 text-emerald-800
                                    @elseif ($rev->isNeedsRevision()) bg-rose-100 text-rose-800
                                    @elseif ($rev->isRejected()) bg-red-100 text-red-800
                                    @elseif ($rev->isWaiting()) bg-amber-100 text-amber-800
                                    @else bg-slate-100 text-slate-700
                                    @endif">
                                    {{ $rev->statusLabel() }}
                                </span>
                            </div>
                            <p class="text-slate-700 line-clamp-3 leading-relaxed">{{ $rev->summary }}</p>
                            @if ($rev->feedback)
                                <div class="p-2.5 bg-white rounded-lg border border-slate-200 text-slate-800">
                                    <span class="font-bold text-slate-500 block mb-0.5">{{ __('Feedback:') }}</span>
                                    <p class="leading-relaxed">{{ $rev->feedback }}</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
