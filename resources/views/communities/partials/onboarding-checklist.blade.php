@php
    $checklist = \App\Models\OnboardingStepCompletion::getChecklist($community, auth()->user());
    $nextStep = $checklist ? collect($checklist['steps'])->first(fn ($step) => ! $step['completed'] && ! $step['unavailable']) : null;
@endphp

@if ($checklist && (! $checklist['allCompleted'] || session('status') === 'video-acknowledged'))
    {{-- Compact summary: true count, one next actionable step, all steps behind a disclosure. --}}
    <section x-data="{ dismissed: false }" x-show="!dismissed" aria-labelledby="onboarding-checklist-heading" class="rail-card p-4 space-y-3">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <h3 id="onboarding-checklist-heading" class="text-sm font-bold text-ink">{{ __('Welcome Checklist') }}</h3>
                <p class="text-xs text-muted">
                    {{ __(':done of :total steps completed', ['done' => $checklist['completedSteps'], 'total' => $checklist['totalSteps']]) }}
                    @if ($checklist['isCreator'])
                        <span class="ml-1 font-medium text-brand">({{ __('Creator Preview') }})</span>
                    @endif
                </p>
            </div>
            <button type="button" @click="dismissed = true" class="-m-2 inline-flex min-h-11 min-w-11 items-center justify-center rounded-lg text-lg leading-none text-slate-500 hover:text-ink" title="{{ __('Hide checklist') }}" aria-label="{{ __('Hide checklist') }}">
                &times;
            </button>
        </div>

        <progress class="sr-only" max="100" value="{{ $checklist['percentage'] }}">{{ $checklist['percentage'] }}%</progress>
        <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100" aria-hidden="true">
            <div class="h-full rounded-full bg-brand" style="width: {{ $checklist['percentage'] }}%"></div>
        </div>

        @if ($nextStep)
            <div class="rounded-xl border border-line bg-slate-50 p-3 space-y-2">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">{{ __('Next step') }}</p>
                <p class="text-sm font-semibold text-ink break-words">{{ $nextStep['title'] }}</p>
                <div class="flex flex-wrap items-center gap-2">
                    @include('communities.partials.onboarding-step-action', ['step' => $nextStep])
                </div>
            </div>
        @elseif (! $checklist['allCompleted'])
            <p class="text-xs text-muted">{{ __('The remaining steps are unavailable right now.') }}</p>
        @endif

        <details class="rail-disclosure rail-disclosure--nested">
            <summary>
                <svg class="rail-disclosure__chevron h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
                <span>{{ __('All steps (:count)', ['count' => count($checklist['steps'])]) }}</span>
            </summary>
            <ol class="rail-disclosure__body space-y-2">
                @foreach ($checklist['steps'] as $step)
                    <li @class(['rounded-xl border p-3 space-y-1.5', 'border-emerald-200 bg-emerald-50/50' => $step['completed'], 'border-slate-200 bg-slate-50 text-slate-500' => ! $step['completed'] && $step['unavailable'], 'border-line bg-white' => ! $step['completed'] && ! $step['unavailable']])>
                        <p class="text-sm font-semibold break-words {{ $step['completed'] ? 'text-emerald-900' : 'text-ink' }}">
                            <span class="sr-only">{{ $step['completed'] ? __('Completed:') : ($step['unavailable'] ? __('Unavailable:') : __('Not completed:')) }}</span>
                            {{ $step['title'] }}
                        </p>
                        <p class="text-xs text-muted break-words">{{ $step['description'] }}</p>
                        <div class="flex flex-wrap items-center gap-2">
                            @include('communities.partials.onboarding-step-action', ['step' => $step])
                        </div>
                    </li>
                @endforeach
            </ol>
        </details>
    </section>
@endif
