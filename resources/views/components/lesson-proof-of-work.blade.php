@props([
    'community',
    'course',
    'lesson',
    'currentProofOfWork' => null,
    'proofOfWorkHistory' => null,
    'isCreator' => false,
    'isActiveMember' => false,
])

@if ($lesson->isPractical())
    <section aria-labelledby="proof-of-work-title" class="min-w-0 break-words [overflow-wrap:anywhere] space-y-5 rounded-2xl bg-white p-6 sm:p-8 shadow-xs border border-teal-200/90 bg-gradient-to-br from-white via-teal-50/20 to-teal-50/40">
        <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-teal-100">
            <div class="space-y-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-teal-100 text-teal-800 border border-teal-200">
                        <svg class="w-3.5 h-3.5 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        {{ __('Practical Lesson') }}
                    </span>
                    @if ($lesson->skill_label)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-200">
                            {{ __('Skill:') }} {{ $lesson->skill_label }}
                        </span>
                    @endif
                </div>
                <h2 id="proof-of-work-title" class="text-xl font-extrabold text-slate-900 tracking-tight">
                    {{ __('Proof of Work') }}
                </h2>
            </div>

            <div class="flex items-center gap-2">
                @if ($currentProofOfWork)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold
                        @if ($currentProofOfWork->isVerified()) bg-emerald-100 text-emerald-800 border border-emerald-300
                        @elseif ($currentProofOfWork->isNeedsRevision()) bg-rose-100 text-rose-800 border border-rose-300
                        @elseif ($currentProofOfWork->isRejected()) bg-red-100 text-red-800 border border-red-300
                        @elseif ($currentProofOfWork->isWaiting()) bg-amber-100 text-amber-800 border border-amber-300
                        @else bg-slate-100 text-slate-700 border border-slate-300
                        @endif">
                        <span>{{ $currentProofOfWork->statusLabel() }}</span>
                        @if ($currentProofOfWork->revision > 1)
                            <span class="text-[11px] opacity-75">({{ __('Rev. :rev', ['rev' => $currentProofOfWork->revision]) }})</span>
                        @endif
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                        {{ __('Not Submitted') }}
                    </span>
                @endif

                @if ($isCreator)
                    <a href="{{ route('communities.manage.proof-of-work.index', $community) }}" class="inline-flex items-center gap-1 text-xs font-bold text-teal-700 hover:text-teal-900 bg-white border border-teal-200 hover:bg-teal-50 px-3 py-1 rounded-full transition shadow-2xs">
                        <span>{{ __('Review Queue') }}</span> &rarr;
                    </a>
                @endif
            </div>
        </div>

        {{-- Creator Feedback Banners --}}
        @if ($currentProofOfWork?->isVerified())
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-sm text-emerald-900 space-y-2">
                <div class="flex items-center gap-2 font-bold text-emerald-800">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span>{{ __('Work Verified by Creator!') }}</span>
                </div>
                <p class="text-xs text-emerald-700">
                    {{ __('Skill snapshotted:') }} <strong>{{ $currentProofOfWork->skill_label_snapshot ?? $lesson->skill_label }}</strong>
                    @if ($currentProofOfWork->reviewed_at)
                        &bull; {{ $currentProofOfWork->reviewed_at->format('M d, Y') }}
                    @endif
                </p>
                @if ($currentProofOfWork->feedback)
                    <div class="mt-2 p-3 bg-white/80 rounded-lg border border-emerald-200/80 text-xs text-slate-700">
                        <span class="font-semibold text-emerald-900 block mb-1">{{ __('Feedback:') }}</span>
                        <p class="whitespace-pre-line">{{ $currentProofOfWork->feedback }}</p>
                    </div>
                @endif
            </div>
        @elseif ($currentProofOfWork?->isNeedsRevision())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-sm text-rose-900 space-y-2">
                <div class="flex items-center gap-2 font-bold text-rose-800">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>{{ __('Revisions Requested') }}</span>
                </div>
                <p class="text-xs text-rose-700 leading-relaxed">
                    {{ __('The creator reviewed your submission and requested improvements before verification.') }}
                </p>
                @if ($currentProofOfWork->feedback)
                    <div class="mt-2 p-3 bg-white rounded-lg border border-rose-200 text-xs text-slate-800">
                        <span class="font-bold text-rose-900 block mb-1">{{ __('Creator Feedback:') }}</span>
                        <p class="whitespace-pre-line leading-relaxed">{{ $currentProofOfWork->feedback }}</p>
                    </div>
                @endif
            </div>
        @elseif ($currentProofOfWork?->isRejected())
            <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-sm text-red-900 space-y-2">
                <div class="flex items-center gap-2 font-bold text-red-800">
                    <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 9l-6 6M9 9l6 6"/></svg>
                    <span>{{ __('Submission Rejected') }}</span>
                </div>
                @if ($currentProofOfWork->feedback)
                    <div class="mt-2 p-3 bg-white rounded-lg border border-red-200 text-xs text-slate-800">
                        <span class="font-bold text-red-900 block mb-1">{{ __('Creator Reason:') }}</span>
                        <p class="whitespace-pre-line leading-relaxed">{{ $currentProofOfWork->feedback }}</p>
                    </div>
                @endif
            </div>
        @elseif ($currentProofOfWork?->isWaiting())
            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-sm text-amber-900 space-y-1">
                <div class="flex items-center gap-2 font-bold text-amber-800">
                    <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ __('Waiting for Creator Feedback') }}</span>
                </div>
                <p class="text-xs text-amber-700">
                    {{ __('Your work has been submitted and is in the review queue. You will see feedback here once reviewed.') }}
                </p>
            </div>
        @endif

        {{-- Submitted Work Display (When Waiting or Terminal) --}}
        @if ($currentProofOfWork && ($currentProofOfWork->isWaiting() || $currentProofOfWork->isTerminal()))
            <div class="bg-white p-5 rounded-xl border border-slate-200 space-y-3">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('Your Submitted Evidence') }}</h3>
                <div class="text-sm text-slate-800 whitespace-pre-line leading-relaxed">
                    {{ $currentProofOfWork->summary }}
                </div>
                @if ($currentProofOfWork->url)
                    <div class="pt-2 text-xs">
                        <span class="font-semibold text-slate-500">{{ __('Result / Repository Link:') }}</span>
                        <a href="{{ $currentProofOfWork->url }}" target="_blank" rel="noopener noreferrer" class="ml-1 text-brand-600 hover:underline font-medium break-all">
                            {{ $currentProofOfWork->url }} &rarr;
                        </a>
                    </div>
                @endif
                @if ($currentProofOfWork->attachment_path)
                    <div class="pt-2">
                        <span class="font-semibold text-slate-500 text-xs block mb-1.5">{{ __('Attached Evidence Image:') }}</span>
                        <div class="aspect-video max-w-md overflow-hidden rounded-xl border border-slate-200 bg-slate-900">
                            <img src="{{ route('communities.proof-of-work.image', [$community, $currentProofOfWork]) }}" alt="Submitted Proof of Work" class="w-full h-full object-contain">
                        </div>
                        <a href="{{ route('communities.proof-of-work.image', [$community, $currentProofOfWork]) }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-block text-xs font-semibold text-brand underline">
                            {{ __('Open original evidence image') }}
                        </a>
                    </div>
                @endif
            </div>
        @endif

        {{-- Learner Submission / Revision Form (When Draft, Needs Revision, or No Submission Yet) --}}
        @if ($isActiveMember && (! $currentProofOfWork || $currentProofOfWork->isDraft() || $currentProofOfWork->isNeedsRevision()))
            <form method="POST" action="{{ route('communities.lessons.proof-of-work.store', [$community, $course, $lesson]) }}" enctype="multipart/form-data" class="space-y-4 bg-white p-5 sm:p-6 rounded-xl border border-teal-200/80">
                @csrf
                <div class="space-y-1">
                    <h3 class="font-bold text-sm text-slate-900">
                        {{ $currentProofOfWork?->isNeedsRevision() ? __('Submit Revision') : __('Submit Your Work') }}
                    </h3>
                    <p class="text-xs text-slate-500">
                        {{ __('Demonstrate your understanding with a summary, verified HTTPS link (e.g. GitHub repo or deployment), and optional screenshot.') }}
                    </p>
                </div>

                <div>
                    <label for="pow-summary" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1.5">
                        {{ __('Summary of Work / Key Learnings') }} <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="pow-summary" name="summary" rows="4" maxlength="5000" placeholder="{{ __('Describe what you built, problems encountered, and how you solved them (min 10 characters)...') }}"
                              class="block w-full border-slate-300 focus:border-brand-500 focus:ring-brand-500 rounded-xl shadow-xs text-sm text-ink placeholder-slate-400 p-2.5 leading-relaxed">{{ is_string(old('summary', $currentProofOfWork?->summary)) ? old('summary', $currentProofOfWork?->summary) : '' }}</textarea>
                    <x-input-error :messages="$errors->get('summary')" class="mt-1" />
                </div>

                <div>
                    <label for="pow-url" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1.5">
                        {{ __('Result / Repository URL (HTTPS only)') }} <span class="text-rose-500">*</span>
                    </label>
                    <input id="pow-url" type="url" name="url" maxlength="2048" value="{{ is_string(old('url', $currentProofOfWork?->url)) ? old('url', $currentProofOfWork?->url) : '' }}" placeholder="https://github.com/your-username/your-project"
                           class="block w-full border-slate-300 focus:border-brand-500 focus:ring-brand-500 rounded-xl shadow-xs text-sm text-ink placeholder-slate-400 p-2.5">
                    <p class="text-xs text-muted mt-1">{{ __('Must be an absolute HTTPS link without credentials.') }}</p>
                    <x-input-error :messages="$errors->get('url')" class="mt-1" />
                </div>

                <div>
                    <label for="pow-attachment" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1.5">
                        {{ __('Evidence Screenshot / Image (Optional)') }}
                    </label>
                    <input id="pow-attachment" type="file" name="attachment" accept=".jpg,.jpeg,.png,.webp"
                           class="block w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 cursor-pointer">
                    <p class="text-xs text-muted mt-1">{{ __('JPEG, PNG, or WebP. Max 5 MiB, max 4096px.') }}</p>
                    <x-input-error :messages="$errors->get('attachment')" class="mt-1" />
                </div>

                <div class="flex flex-wrap items-center justify-end gap-3 pt-2">
                    <button type="submit" name="action" value="draft" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-xl font-semibold text-xs hover:bg-slate-50 transition active:scale-[0.99] cursor-pointer">
                        {{ __('Save Draft') }}
                    </button>
                    <button type="submit" name="action" value="submit" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-hover text-white rounded-xl font-bold text-xs shadow-xs transition active:scale-[0.99] cursor-pointer">
                        {{ $currentProofOfWork?->isNeedsRevision() ? __('Submit Revision for Review') : __('Submit for Review') }}
                    </button>
                </div>
            </form>
        @endif

        {{-- Prior Revisions History --}}
        @if ($proofOfWorkHistory && $proofOfWorkHistory->count() > 1)
            <details class="rounded-xl border border-teal-100 bg-white/70 p-4 text-xs space-y-3">
                <summary class="cursor-pointer font-bold text-slate-700 hover:text-teal-700 flex items-center justify-between">
                    <span>{{ __('Revision History (:count)', ['count' => $proofOfWorkHistory->count()]) }}</span>
                    <span class="text-slate-400 font-normal">{{ __('Click to expand') }}</span>
                </summary>
                <div class="space-y-3 pt-3 border-t border-slate-100">
                    @foreach ($proofOfWorkHistory as $historyItem)
                        <div class="p-3 rounded-lg border {{ $historyItem->is_current ? 'border-teal-300 bg-teal-50/30' : 'border-slate-200 bg-slate-50' }} space-y-1.5">
                            <div class="flex items-center justify-between font-semibold">
                                <span class="text-slate-900">{{ __('Revision #:rev', ['rev' => $historyItem->revision]) }} {{ $historyItem->is_current ? __('(Current)') : '' }}</span>
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold
                                    @if ($historyItem->isVerified()) bg-emerald-100 text-emerald-800
                                    @elseif ($historyItem->isNeedsRevision()) bg-rose-100 text-rose-800
                                    @elseif ($historyItem->isRejected()) bg-red-100 text-red-800
                                    @elseif ($historyItem->isWaiting()) bg-amber-100 text-amber-800
                                    @else bg-slate-100 text-slate-700
                                    @endif">
                                    {{ $historyItem->statusLabel() }}
                                </span>
                            </div>
                            <div class="text-slate-600 line-clamp-2">{{ $historyItem->summary }}</div>
                            @if ($historyItem->feedback)
                                <div class="text-slate-700 bg-white p-2 rounded border border-slate-200 mt-1">
                                    <span class="font-bold text-slate-500">{{ __('Feedback:') }}</span> {{ $historyItem->feedback }}
                                </div>
                            @endif
                            <div class="text-[11px] text-slate-400">
                                {{ __('Submitted:') }} {{ $historyItem->submitted_at?->diffForHumans() ?? __('Draft') }}
                                @if ($historyItem->reviewed_at)
                                    &bull; {{ __('Reviewed:') }} {{ $historyItem->reviewed_at->diffForHumans() }}
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </details>
        @endif
    </section>
@endif
