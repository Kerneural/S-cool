@php
    $checklist = \App\Models\OnboardingStepCompletion::getChecklist($community, auth()->user());
@endphp

@if ($checklist && (! $checklist['allCompleted'] || session('status') === 'video-acknowledged'))
    <div x-data="{ dismissed: false }" x-show="!dismissed" class="bg-gradient-to-r from-indigo-50/70 via-white to-purple-50/50 rounded-2xl border border-indigo-100 shadow-sm p-5 transition-all">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-indigo-600 text-white shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-gray-900 tracking-tight">{{ __('Welcome Checklist') }}</h3>
                        <p class="text-xs text-gray-500">
                            {{ __(':done of :total steps completed', ['done' => $checklist['completedSteps'], 'total' => $checklist['totalSteps']]) }}
                            @if ($checklist['isCreator'])
                                <span class="ml-1 text-indigo-600 font-medium">({{ __('Creator Preview') }})</span>
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="mt-3 w-full bg-gray-200/80 rounded-full h-2 overflow-hidden">
                    <div class="bg-indigo-600 h-2 rounded-full transition-all duration-500" style="width: {{ $checklist['percentage'] }}%"></div>
                </div>
            </div>

            <button type="button" @click="dismissed = true" class="text-gray-400 hover:text-gray-600 text-sm font-semibold p-1" title="{{ __('Hide checklist') }}">
                &times;
            </button>
        </div>

        <!-- Checklist Items -->
        <div class="mt-4 space-y-2.5">
            @foreach ($checklist['steps'] as $step)
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-3 rounded-xl border transition {{ $step['completed'] ? 'bg-green-50/50 border-green-200/70' : ($step['unavailable'] ? 'bg-gray-50 border-gray-200 opacity-60' : 'bg-white border-gray-200 hover:border-indigo-200') }} gap-2">
                    <div class="flex items-start sm:items-center gap-2.5">
                        <div class="flex-shrink-0 mt-0.5 sm:mt-0">
                            @if ($step['completed'])
                                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-green-500 text-white">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                </span>
                            @elseif ($step['unavailable'])
                                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-gray-300 text-white">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                </span>
                            @else
                                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full border-2 border-gray-300"></span>
                            @endif
                        </div>

                        <div>
                            <div class="text-sm font-semibold {{ $step['completed'] ? 'text-green-900 line-through' : 'text-gray-900' }}">
                                {{ $step['title'] }}
                            </div>
                            <p class="text-xs text-gray-500">
                                {{ $step['description'] }}
                            </p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2 pl-7 sm:pl-0 flex-shrink-0">
                        @if ($step['completed'])
                            <span class="text-xs font-semibold text-green-700 bg-green-100/80 px-2 py-0.5 rounded-md">
                                {{ __('Done') }}
                            </span>
                        @elseif ($step['unavailable'])
                            <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded-md">
                                {{ __('Unavailable') }}
                            </span>
                        @elseif ($step['key'] === 'video')
                            <a href="{{ $step['about_url'] }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                {{ __('Watch Video') }}
                            </a>
                            <form method="POST" action="{{ route('communities.onboarding.video', $community) }}" class="inline">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 text-xs font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-md shadow-sm transition">
                                    {{ __('Acknowledge') }}
                                </button>
                            </form>
                        @elseif ($step['key'] === 'comment')
                            <a href="{{ $step['action_url'] }}" class="px-2.5 py-1 text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-md transition">
                                {{ __('Browse Posts') }}
                            </a>
                        @elseif ($step['key'] === 'post')
                            <a href="{{ $step['action_url'] }}" class="px-2.5 py-1 text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-md transition">
                                {{ __('Introduce Yourself') }}
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
