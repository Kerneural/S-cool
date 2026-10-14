<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-community-workspace :community="$community" active-tab="reports" />

            <!-- Header & Filter Tabs -->
            <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Content Moderation Queue</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Moderate reported posts and comments in <span class="font-medium text-gray-700">{{ $community->name }}</span>.
                    </p>
                </div>

                <!-- Status Filter Tabs -->
                <div class="flex items-center gap-1.5 bg-gray-100 p-1 rounded-lg">
                    <a href="{{ route('communities.reports.index', ['community' => $community, 'status' => 'OPEN']) }}"
                       class="px-3 py-1.5 rounded-md text-xs font-semibold transition {{ $status === 'OPEN' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                        Pending (OPEN)
                    </a>
                    <a href="{{ route('communities.reports.index', ['community' => $community, 'status' => 'RESOLVED']) }}"
                       class="px-3 py-1.5 rounded-md text-xs font-semibold transition {{ $status === 'RESOLVED' ? 'bg-white text-emerald-600 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                        Resolved (RESOLVED)
                    </a>
                    <a href="{{ route('communities.reports.index', ['community' => $community, 'status' => 'DISMISSED']) }}"
                       class="px-3 py-1.5 rounded-md text-xs font-semibold transition {{ $status === 'DISMISSED' ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                        Dismissed (DISMISSED)
                    </a>
                    <a href="{{ route('communities.reports.index', ['community' => $community, 'status' => 'ALL']) }}"
                       class="px-3 py-1.5 rounded-md text-xs font-semibold transition {{ $status === 'ALL' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                        All
                    </a>
                </div>
            </div>

            <!-- Flash alerts -->
            @if (session('status'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">
                    {{ session('status') }}
                </div>
            @endif
            @if (session('info'))
                <div class="p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-lg text-sm">
                    {{ session('info') }}
                </div>
            @endif

            <!-- Reports Queue List -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                @if ($reports->isEmpty())
                    <div class="p-12 text-center text-gray-400 text-sm">
                        No content reports found for the selected status filter.
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach ($reports as $report)
                            @php
                                $target = $report->getTargetModel();
                            @endphp
                            <div class="p-6 space-y-4 hover:bg-gray-50/50 transition">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold {{ $report->target_type === 'POST' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                            {{ $report->target_type === 'POST' ? 'POST' : 'COMMENT' }} #{{ $report->target_id }}
                                        </span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $report->status === 'OPEN' ? 'bg-amber-100 text-amber-800' : ($report->status === 'RESOLVED' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700') }}">
                                            {{ $report->status }}
                                        </span>
                                        <span class="text-xs text-gray-400">
                                            Reported at: {{ $report->created_at->format('Y-m-d H:i') }} UTC
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Reporter: <span class="font-medium text-gray-800">{{ $report->reporter?->name ?? 'Unavailable user' }}</span>
                                    </div>
                                </div>

                                <!-- Target Preview -->
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">
                                        Reported Content:
                                    </div>
                                    @if (! $target)
                                        <p class="text-sm text-gray-400 italic">[Content does not exist or was permanently removed]</p>
                                    @elseif ($target->trashed())
                                        <p class="text-sm text-rose-600 font-medium italic">[Content removed]</p>
                                    @else
                                        @if ($report->target_type === 'POST')
                                            <div>
                                                @if ($target->title)
                                                    <h4 class="font-semibold text-gray-900 text-sm mb-1">{{ $target->title }}</h4>
                                                @endif
                                                <p class="text-sm text-gray-700 line-clamp-3">{{ $target->body }}</p>
                                                <div class="mt-2">
                                                    <a href="{{ route('communities.posts.show', [$community, $target]) }}"
                                                        class="text-xs text-indigo-600 hover:text-indigo-800 font-medium"
                                                        target="_blank">
                                                        View post details &rarr;
                                                    </a>
                                                </div>
                                            </div>
                                        @else
                                            <p class="text-sm text-gray-700 line-clamp-3">{{ $target->body }}</p>
                                            @if ($target->post)
                                                <div class="mt-2">
                                                    <a href="{{ route('communities.posts.show', [$community, $target->post]) }}"
                                                        class="text-xs text-indigo-600 hover:text-indigo-800 font-medium"
                                                        target="_blank">
                                                        View parent post &rarr;
                                                    </a>
                                                </div>
                                            @endif
                                        @endif
                                    @endif
                                </div>

                                <!-- Reason Provided -->
                                <div class="space-y-1">
                                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Report Reason:
                                    </div>
                                    <div class="p-3 bg-white rounded border border-gray-200 text-sm text-gray-800 whitespace-pre-wrap font-sans">
                                        {{ $report->reason }}
                                    </div>
                                </div>

                                <!-- Resolution Metadata if resolved or dismissed -->
                                @if (! $report->isOpen())
                                    <div class="pt-3 border-t border-gray-100 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500 bg-gray-50/75 p-3 rounded-lg">
                                        <div>
                                            <span class="font-semibold text-gray-700">Action:</span>
                                            <span class="font-mono text-gray-800 font-bold ml-1">{{ $report->resolution_action }}</span>
                                            <span class="mx-2">&bull;</span>
                                            <span class="font-semibold text-gray-700">Resolution note:</span>
                                            <span class="text-gray-800 ml-1">{{ $report->resolution_reason }}</span>
                                        </div>
                                        <div>
                                            <span>Moderator: <strong class="text-gray-700">{{ $report->resolver?->name ?? 'Moderator' }}</strong></span>
                                            <span class="mx-1">&bull;</span>
                                            <span>{{ $report->resolved_at?->format('Y-m-d H:i') }} UTC</span>
                                        </div>
                                    </div>
                                @endif

                                <!-- Actions for Creator (if OPEN) -->
                                @if ($report->isOpen())
                                    <div class="pt-3 border-t border-gray-100 flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3" x-data="{ showReason: false, action: '' }">
                                        <!-- Remove Target Form -->
                                        <form method="POST" action="{{ route('communities.reports.remove', [$community, $report]) }}" class="inline-flex gap-2 items-center">
                                            @csrf
                                            <input type="text" name="resolution_reason" placeholder="Removal note (optional)..." class="text-xs border-gray-300 rounded-lg px-2.5 py-1.5 focus:ring-rose-500 focus:border-rose-500 w-56">
                                            <button type="submit" onclick="return confirm('Are you sure you want to remove this reported content?')" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-semibold transition">
                                                Remove Content
                                            </button>
                                        </form>

                                        <!-- Dismiss Report Form -->
                                        <form method="POST" action="{{ route('communities.reports.dismiss', [$community, $report]) }}" class="inline-flex gap-2 items-center">
                                            @csrf
                                            <input type="text" name="resolution_reason" placeholder="Dismissal note (optional)..." class="text-xs border-gray-300 rounded-lg px-2.5 py-1.5 focus:ring-gray-500 focus:border-gray-500 w-56">
                                            <button type="submit" class="px-3 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-xs font-semibold transition">
                                                Dismiss
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if ($reports->hasPages())
                        <div class="p-4 border-t border-gray-100 bg-gray-50">
                            {{ $reports->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
