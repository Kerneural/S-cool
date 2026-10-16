<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <nav class="flex items-center space-x-2 text-xs sm:text-sm text-slate-500">
                <a class="hover:text-brand-600 transition" href="{{ route('communities.classroom.index', $community) }}">
                    {{ __('Classroom') }}
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-slate-900 font-semibold">{{ __('Proof of Work Review Queue') }}</span>
            </nav>
            <a class="text-xs sm:text-sm font-semibold text-brand-600 hover:text-brand-700 transition" href="{{ route('communities.classroom.index', $community) }}">
                &larr; {{ __('Back to Classroom') }}
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <x-classroom-feedback />

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-ink">{{ __('Proof of Work Review Queue') }}</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    {{ __('Review practical evidence submitted by community members and provide actionable feedback.') }}
                </p>
            </div>
        </div>

        {{-- Status Filter Tabs --}}
        <div class="bg-white p-3 rounded-2xl shadow-xs border border-slate-200/80 flex flex-wrap items-center gap-2 text-xs">
            <span class="font-bold text-slate-400 px-2 uppercase tracking-wider">{{ __('Status:') }}</span>
            <a href="{{ route('communities.manage.proof-of-work.index', [$community, 'status' => 'all']) }}"
               class="px-3 py-1.5 rounded-full font-medium transition {{ $status === 'all' ? 'bg-brand-600 text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                {{ __('All') }}
            </a>
            <a href="{{ route('communities.manage.proof-of-work.index', [$community, 'status' => 'WAITING']) }}"
               class="px-3 py-1.5 rounded-full font-medium transition inline-flex items-center gap-1.5 {{ $status === 'WAITING' ? 'bg-amber-600 text-white font-bold shadow-xs' : 'bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200' }}">
                <span>{{ __('Waiting for Feedback') }}</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === 'WAITING' ? 'bg-white/20' : 'bg-amber-200/60' }}">{{ $counts['WAITING'] }}</span>
            </a>
            <a href="{{ route('communities.manage.proof-of-work.index', [$community, 'status' => 'NEEDS_REVISION']) }}"
               class="px-3 py-1.5 rounded-full font-medium transition inline-flex items-center gap-1.5 {{ $status === 'NEEDS_REVISION' ? 'bg-rose-600 text-white font-bold shadow-xs' : 'bg-rose-50 text-rose-800 hover:bg-rose-100 border border-rose-200' }}">
                <span>{{ __('Needs Revision') }}</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === 'NEEDS_REVISION' ? 'bg-white/20' : 'bg-rose-200/60' }}">{{ $counts['NEEDS_REVISION'] }}</span>
            </a>
            <a href="{{ route('communities.manage.proof-of-work.index', [$community, 'status' => 'VERIFIED']) }}"
               class="px-3 py-1.5 rounded-full font-medium transition inline-flex items-center gap-1.5 {{ $status === 'VERIFIED' ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200' }}">
                <span>{{ __('Verified') }}</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === 'VERIFIED' ? 'bg-white/20' : 'bg-emerald-200/60' }}">{{ $counts['VERIFIED'] }}</span>
            </a>
            <a href="{{ route('communities.manage.proof-of-work.index', [$community, 'status' => 'REJECTED']) }}"
               class="px-3 py-1.5 rounded-full font-medium transition inline-flex items-center gap-1.5 {{ $status === 'REJECTED' ? 'bg-red-600 text-white font-bold shadow-xs' : 'bg-red-50 text-red-800 hover:bg-red-100 border border-red-200' }}">
                <span>{{ __('Rejected') }}</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === 'REJECTED' ? 'bg-white/20' : 'bg-red-200/60' }}">{{ $counts['REJECTED'] }}</span>
            </a>
        </div>

        {{-- Submissions List --}}
        @if ($submissions->isEmpty())
            <div class="rounded-2xl bg-white p-12 text-center border border-slate-200/80 shadow-xs">
                <div class="mx-auto w-12 h-12 rounded-2xl bg-teal-50 border border-teal-100 flex items-center justify-center text-teal-600 mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-slate-900 mb-1">{{ __('No submissions found') }}</h3>
                <p class="text-sm text-slate-500 max-w-sm mx-auto">{{ __('There are no proof of work submissions matching the selected status.') }}</p>
            </div>
        @else
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs sm:text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/75 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                                <th class="py-3.5 px-4 sm:px-6">{{ __('Learner') }}</th>
                                <th class="py-3.5 px-4">{{ __('Lesson & Course') }}</th>
                                <th class="py-3.5 px-4">{{ __('Skill Demonstrated') }}</th>
                                <th class="py-3.5 px-4">{{ __('Status') }}</th>
                                <th class="py-3.5 px-4">{{ __('Submitted') }}</th>
                                <th class="py-3.5 px-4 sm:px-6 text-right">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($submissions as $item)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="py-4 px-4 sm:px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-brand-50 text-brand-700 font-bold flex items-center justify-center text-xs shrink-0 border border-brand-200">
                                                {{ strtoupper(substr($item->user->name ?? 'U', 0, 1)) }}
                                            </div>
                                            <div>
                                                <span class="font-bold text-slate-900 block">{{ $item->user->name ?? __('Unknown') }}</span>
                                                <span class="text-xs text-slate-400 block">{{ $item->user->email ?? '' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <span class="font-bold text-slate-900 block truncate max-w-xs">{{ $item->lesson->title ?? __('Lesson') }}</span>
                                        <span class="text-xs text-slate-400 block truncate max-w-xs">{{ $item->course->title ?? '' }}</span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-teal-50 text-teal-800 border border-teal-200">
                                            {{ $item->skill_label_snapshot ?? $item->lesson->skill_label ?? __('General Practice') }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold
                                            @if ($item->isVerified()) bg-emerald-100 text-emerald-800
                                            @elseif ($item->isNeedsRevision()) bg-rose-100 text-rose-800
                                            @elseif ($item->isRejected()) bg-red-100 text-red-800
                                            @elseif ($item->isWaiting()) bg-amber-100 text-amber-800
                                            @else bg-slate-100 text-slate-700
                                            @endif">
                                            {{ $item->statusLabel() }}
                                            @if ($item->revision > 1)
                                                <span class="ml-1 text-[10px] opacity-75">#{{ $item->revision }}</span>
                                            @endif
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-xs text-slate-500 whitespace-nowrap">
                                        {{ $item->submitted_at?->diffForHumans() ?? $item->created_at->diffForHumans() }}
                                    </td>
                                    <td class="py-4 px-4 sm:px-6 text-right whitespace-nowrap">
                                        <a href="{{ route('communities.manage.proof-of-work.show', [$community, $item]) }}"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl font-bold text-xs bg-brand-50 text-brand-700 hover:bg-brand-100 border border-brand-200 transition shadow-2xs active:scale-[0.99]">
                                            <span>{{ $item->isWaiting() ? __('Review Work') : __('View Details') }}</span> &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6">
                {{ $submissions->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
