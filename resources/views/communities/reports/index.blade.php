<x-app-layout>
    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-community-workspace :community="$community" active-tab="reports" />

            <!-- Header & Filter Tabs -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Content Moderation Queue</h1>
                    <p class="text-sm text-slate-500 mt-1">
                        Moderate reported posts and comments in <span class="font-semibold text-slate-700">{{ $community->name }}</span>.
                    </p>
                </div>

                <!-- Status Filter Tabs -->
                <div class="flex w-full flex-wrap items-center gap-1 bg-slate-100/80 p-1 rounded-xl border border-slate-200/60 md:w-auto">
                    <a href="{{ route('communities.reports.index', ['community' => $community, 'status' => 'OPEN']) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $status === 'OPEN' ? 'bg-white text-teal-700 shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}">
                        Pending (OPEN)
                    </a>
                    <a href="{{ route('communities.reports.index', ['community' => $community, 'status' => 'RESOLVED']) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $status === 'RESOLVED' ? 'bg-white text-emerald-700 shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}">
                        Resolved (RESOLVED)
                    </a>
                    <a href="{{ route('communities.reports.index', ['community' => $community, 'status' => 'DISMISSED']) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $status === 'DISMISSED' ? 'bg-white text-slate-800 shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}">
                        Dismissed (DISMISSED)
                    </a>
                    <a href="{{ route('communities.reports.index', ['community' => $community, 'status' => 'ALL']) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $status === 'ALL' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}">
                        All
                    </a>
                </div>
            </div>

            <!-- Flash alerts -->
            @if (session('status'))
                <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm shadow-xs">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif
            @if (session('info'))
                <div class="flex items-center gap-3 p-4 bg-sky-50 border border-sky-200 text-sky-800 rounded-xl text-sm shadow-xs">
                    <svg class="w-5 h-5 text-sky-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            <!-- Reports Queue List -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                @if ($reports->isEmpty())
                    <div class="p-16 text-center text-slate-400 text-sm">
                        <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="font-medium text-slate-500">No content reports found for the selected status filter.</p>
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($reports as $report)
                            @php
                                $target = $report->getTargetModel();
                            @endphp
                            <div class="p-6 space-y-4 hover:bg-slate-50/50 transition">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold {{ $report->target_type === 'POST' ? 'bg-sky-50 text-sky-700 border border-sky-200' : 'bg-purple-50 text-purple-700 border border-purple-200' }}">
                                            {{ $report->target_type === 'POST' ? 'POST' : 'COMMENT' }} #{{ $report->target_id }}
                                        </span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold {{ $report->status === 'OPEN' ? 'bg-amber-50 text-amber-700 border border-amber-200' : ($report->status === 'RESOLVED' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-700 border border-slate-200') }}">
                                            {{ $report->status }}
                                        </span>
                                        <span class="text-xs text-slate-400">
                                            Reported at: {{ $report->created_at->format('Y-m-d H:i') }} UTC
                                        </span>
                                    </div>
                                    <div class="text-xs text-slate-500">
                                        Reporter: <span class="font-semibold text-slate-800">{{ $report->reporter?->name ?? 'Unavailable user' }}</span>
                                    </div>
                                </div>

                                <!-- Target Preview -->
                                <div class="bg-slate-50/80 p-4 rounded-xl border border-slate-200/70">
                                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">
                                        Reported Content:
                                    </div>
                                    @if (! $target)
                                        <p class="text-sm text-slate-400 italic">[Content does not exist or was permanently removed]</p>
                                    @elseif ($target->trashed())
                                        <p class="text-sm text-rose-600 font-semibold italic">[Content removed]</p>
                                    @else
                                        @if ($report->target_type === 'POST')
                                            <div>
                                                @if ($target->title)
                                                    <h4 class="font-bold text-slate-900 text-sm mb-1">{{ $target->title }}</h4>
                                                @endif
                                                <p class="text-sm text-slate-700 line-clamp-3 leading-relaxed">{{ $target->body }}</p>
                                                <div class="mt-2.5">
                                                    <a href="{{ route('communities.posts.show', [$community, $target]) }}"
                                                        class="inline-flex items-center gap-1 text-xs text-teal-600 hover:text-teal-700 font-bold"
                                                        target="_blank">
                                                        <span>View post details &rarr;</span>
                                                    </a>
                                                </div>
                                            </div>
                                        @else
                                            <p class="text-sm text-slate-700 line-clamp-3 leading-relaxed">{{ $target->body }}</p>
                                            @if ($target->post)
                                                <div class="mt-2.5">
                                                    <a href="{{ route('communities.posts.show', [$community, $target->post]) }}"
                                                        class="inline-flex items-center gap-1 text-xs text-teal-600 hover:text-teal-700 font-bold"
                                                        target="_blank">
                                                        <span>View parent post &rarr;</span>
                                                    </a>
                                                </div>
                                            @endif
                                        @endif
                                    @endif
                                </div>

                                <!-- Reason Provided -->
                                <div class="space-y-1.5">
                                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                        Report Reason:
                                    </div>
                                    <div class="p-3.5 bg-white rounded-xl border border-slate-200 text-sm text-slate-800 whitespace-pre-wrap font-sans leading-relaxed">
                                        {{ $report->reason }}
                                    </div>
                                </div>

                                <!-- Resolution Metadata if resolved or dismissed -->
                                @if (! $report->isOpen())
                                    <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500 bg-slate-50/75 p-3.5 rounded-xl border border-slate-200/60">
                                        <div>
                                            <span class="font-semibold text-slate-700">Action:</span>
                                            <span class="font-mono text-slate-900 font-bold ml-1">{{ $report->resolution_action }}</span>
                                            <span class="mx-2 text-slate-300">&bull;</span>
                                            <span class="font-semibold text-slate-700">Resolution note:</span>
                                            <span class="text-slate-800 ml-1">{{ $report->resolution_reason }}</span>
                                        </div>
                                        <div>
                                            <span>Moderator: <strong class="text-slate-800">{{ $report->resolver?->name ?? 'Moderator' }}</strong></span>
                                            <span class="mx-1 text-slate-300">&bull;</span>
                                            <span>{{ $report->resolved_at?->format('Y-m-d H:i') }} UTC</span>
                                        </div>
                                    </div>
                                @endif

                                <!-- Actions for Creator (if OPEN) -->
                                @if ($report->isOpen())
                                    <div class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3">
                                        <!-- Remove Target Form -->
                                        <form method="POST" action="{{ route('communities.reports.remove', [$community, $report]) }}" class="relative flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center">
                                            @csrf
                                            <label for="remove-resolution-{{ $report->id }}" class="sr-only">Removal note</label>
                                            <input id="remove-resolution-{{ $report->id }}" type="text" name="resolution_reason" placeholder="Removal note (optional)..."
                                                class="w-full text-xs border-slate-200 rounded-xl px-3 py-2 focus:ring-rose-500 focus:border-rose-500 sm:w-56 shadow-2xs">
                                            <button type="submit" onclick="return confirm('Are you sure you want to remove this reported content?')"
                                                class="px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-xs transition duration-150 whitespace-nowrap">
                                                Remove Content
                                            </button>
                                        </form>

                                        <!-- Dismiss Report Form -->
                                        <form method="POST" action="{{ route('communities.reports.dismiss', [$community, $report]) }}" class="relative flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center">
                                            @csrf
                                            <label for="dismiss-resolution-{{ $report->id }}" class="sr-only">Dismissal note</label>
                                            <input id="dismiss-resolution-{{ $report->id }}" type="text" name="resolution_reason" placeholder="Dismissal note (optional)..."
                                                class="w-full text-xs border-slate-200 rounded-xl px-3 py-2 focus:ring-slate-500 focus:border-slate-500 sm:w-56 shadow-2xs">
                                            <button type="submit"
                                                class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition duration-150 whitespace-nowrap">
                                                Dismiss
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if ($reports->hasPages())
                        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                            {{ $reports->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
