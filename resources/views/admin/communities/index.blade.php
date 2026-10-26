<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="font-bold text-xl text-slate-900 leading-tight">
                    {{ __('Platform Administration — Communities') }}
                </h1>
                <p class="text-xs text-slate-500 mt-1">
                    {{ __('Platform-level oversight and access control. Actions are immutably logged for audit.') }}
                </p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200">
                <span class="w-1.5 h-1.5 rounded-full bg-brand-600"></span>
                {{ __('Platform Admin') }}
            </span>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash alerts -->
            @if (session('status'))
                <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-sm font-medium text-emerald-800 shadow-xs">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="flex items-center gap-3 p-4 rounded-xl bg-rose-50 border border-rose-200 text-sm font-medium text-rose-800 shadow-xs">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-sm font-medium text-rose-800 space-y-1 shadow-xs">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <!-- Summary KPI metrics -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('admin.communities.index') }}" class="block p-5 rounded-2xl border {{ empty($statusFilter) ? 'bg-white border-brand-500 ring-2 ring-brand-200' : 'bg-white border-slate-200/80 hover:border-slate-300' }} shadow-xs transition duration-150">
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('Total Communities') }}</div>
                    <div class="text-2xl font-bold text-slate-900 mt-1 tracking-tight">{{ $stats['total'] }}</div>
                </a>

                <a href="{{ route('admin.communities.index', ['status' => 'ACTIVE']) }}" class="block p-5 rounded-2xl border {{ $statusFilter === 'ACTIVE' ? 'bg-white border-emerald-500 ring-2 ring-emerald-200' : 'bg-white border-slate-200/80 hover:border-slate-300' }} shadow-xs transition duration-150">
                    <div class="text-xs font-bold text-emerald-700 uppercase tracking-wider">{{ __('Active') }}</div>
                    <div class="text-2xl font-bold text-emerald-700 mt-1 tracking-tight">{{ $stats['active'] }}</div>
                </a>

                <a href="{{ route('admin.communities.index', ['status' => 'SUSPENDED']) }}" class="block p-5 rounded-2xl border {{ $statusFilter === 'SUSPENDED' ? 'bg-white border-rose-500 ring-2 ring-rose-200' : 'bg-white border-slate-200/80 hover:border-slate-300' }} shadow-xs transition duration-150">
                    <div class="text-xs font-bold text-rose-700 uppercase tracking-wider">{{ __('Suspended') }}</div>
                    <div class="text-2xl font-bold text-rose-700 mt-1 tracking-tight">{{ $stats['suspended'] }}</div>
                </a>

                <a href="{{ route('admin.communities.index', ['status' => 'ARCHIVED']) }}" class="block p-5 rounded-2xl border {{ $statusFilter === 'ARCHIVED' ? 'bg-white border-slate-500 ring-2 ring-slate-200' : 'bg-white border-slate-200/80 hover:border-slate-300' }} shadow-xs transition duration-150">
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('Archived') }}</div>
                    <div class="text-2xl font-bold text-slate-700 mt-1 tracking-tight">{{ $stats['archived'] }}</div>
                </a>
            </div>

            <!-- Main Communities Table -->
            <div class="bg-white overflow-hidden shadow-xs rounded-2xl border border-slate-200/80">
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 tracking-tight">{{ __('Communities') }}</h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                @if ($statusFilter)
                                    {{ __('Showing filtered results for:') }} <span class="font-bold text-slate-700">{{ $statusFilter }}</span>
                                @else
                                    {{ __('Showing all communities across the platform.') }}
                                @endif
                            </p>
                        </div>

                        <!-- Status filter pills -->
                        <div class="flex items-center gap-1 bg-slate-100/80 p-1 rounded-xl border border-slate-200/60">
                            <a href="{{ route('admin.communities.index') }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ empty($statusFilter) ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}">
                                {{ __('All') }}
                            </a>
                            <a href="{{ route('admin.communities.index', ['status' => 'ACTIVE']) }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ $statusFilter === 'ACTIVE' ? 'bg-white text-emerald-700 shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}">
                                {{ __('Active') }}
                            </a>
                            <a href="{{ route('admin.communities.index', ['status' => 'SUSPENDED']) }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ $statusFilter === 'SUSPENDED' ? 'bg-white text-rose-700 shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}">
                                {{ __('Suspended') }}
                            </a>
                            <a href="{{ route('admin.communities.index', ['status' => 'ARCHIVED']) }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ $statusFilter === 'ARCHIVED' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}">
                                {{ __('Archived') }}
                            </a>
                        </div>
                    </div>

                    @if ($communities->isEmpty())
                        <div class="text-center py-16 text-slate-400">
                            <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <p class="text-sm font-medium">{{ __('No communities found for the selected criteria.') }}</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100">
                                <thead class="bg-slate-50/75 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    <tr>
                                        <th scope="col" class="px-5 py-3.5 text-left">
                                            {{ __('ID') }}
                                        </th>
                                        <th scope="col" class="px-5 py-3.5 text-left">
                                            {{ __('Community') }}
                                        </th>
                                        <th scope="col" class="px-5 py-3.5 text-left">
                                            {{ __('Creator') }}
                                        </th>
                                        <th scope="col" class="px-5 py-3.5 text-left">
                                            {{ __('Status') }}
                                        </th>
                                        <th scope="col" class="px-5 py-3.5 text-left">
                                            {{ __('Recent Audit Record') }}
                                        </th>
                                        <th scope="col" class="px-5 py-3.5 text-right">
                                            {{ __('Actions') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-100 text-sm">
                                    @foreach ($communities as $community)
                                        @php
                                            $latestAction = $community->latestPlatformAdminAction;
                                        @endphp
                                        <tr class="hover:bg-slate-50/60 transition-colors">
                                            <td class="px-5 py-3.5 font-mono text-xs text-slate-500">
                                                #{{ $community->id }}
                                            </td>
                                            <td class="px-5 py-3.5">
                                                <div class="font-bold text-slate-900">{{ $community->name }}</div>
                                                <div class="text-xs font-mono text-slate-400">/{{ $community->slug }}</div>
                                            </td>
                                            <td class="px-5 py-3.5 text-slate-700">
                                                @if ($community->creator)
                                                    <div class="font-semibold text-slate-900">{{ $community->creator->name }}</div>
                                                    <div class="text-xs text-slate-400 font-mono">{{ $community->creator->email }}</div>
                                                @else
                                                    <span class="text-xs text-slate-400">{{ __('N/A') }}</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-3.5">
                                                @if ($community->status === 'ACTIVE')
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                        {{ __('ACTIVE') }}
                                                    </span>
                                                @elseif ($community->status === 'SUSPENDED')
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                        {{ __('SUSPENDED') }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                                        {{ $community->status }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-3.5 text-xs text-slate-600">
                                                @if ($latestAction)
                                                    <div class="font-bold text-slate-800">
                                                        {{ $latestAction->action }} {{ __('by') }} {{ $latestAction->admin?->name ?? __('Admin') }}
                                                    </div>
                                                    <div class="text-slate-500 truncate max-w-xs mt-0.5" title="{{ $latestAction->reason }}">
                                                        "{{ $latestAction->reason }}"
                                                    </div>
                                                    <div class="text-slate-400 text-[11px] mt-0.5">
                                                        {{ $latestAction->created_at?->diffForHumans() }}
                                                    </div>
                                                @else
                                                    <span class="text-slate-400 italic">{{ __('No actions recorded') }}</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-3.5 text-right">
                                                @if ($community->status === 'ACTIVE')
                                                    <button
                                                        type="button"
                                                        x-data=""
                                                        x-on:click.prevent="$dispatch('open-modal', 'suspend-community-{{ $community->id }}')"
                                                        class="inline-flex items-center px-3 py-1.5 bg-rose-600 hover:bg-rose-700 rounded-lg font-bold text-xs text-white uppercase tracking-wider shadow-xs transition duration-150"
                                                    >
                                                        {{ __('Suspend') }}
                                                    </button>

                                                    <!-- Suspend Modal -->
                                                    <x-modal name="suspend-community-{{ $community->id }}" labelledby="suspend-community-title-{{ $community->id }}" focusable>
                                                        <form method="POST" action="{{ route('admin.communities.suspend', $community) }}" class="p-6 sm:p-8 text-left">
                                                            @csrf
                                                            <h2 id="suspend-community-title-{{ $community->id }}" class="text-lg font-bold text-slate-900 tracking-tight">
                                                                {{ __('Suspend Community: :name', ['name' => $community->name]) }}
                                                            </h2>
                                                            <p class="mt-2 text-xs text-slate-500 leading-relaxed">
                                                                {{ __('Suspending this community will immediately deny member and creator access across all modules. This action is audited and can be reactivated later.') }}
                                                            </p>

                                                            <div class="mt-4">
                                                                <x-input-label for="reason-suspend-{{ $community->id }}" :value="__('Reason for Suspension (Mandatory)')" />
                                                                <textarea
                                                                    id="reason-suspend-{{ $community->id }}"
                                                                    name="reason"
                                                                    required
                                                                    rows="3"
                                                                    minlength="3"
                                                                    maxlength="1000"
                                                                    class="mt-1.5 block w-full rounded-xl border-slate-200 shadow-2xs focus:border-rose-500 focus:ring-rose-500 text-sm"
                                                                    placeholder="{{ __('Specify reason (e.g. Terms of Service violation, suspicious activity)...') }}"
                                                                ></textarea>
                                                            </div>

                                                            <div class="mt-6 flex justify-end gap-3">
                                                                <x-secondary-button x-on:click="$dispatch('close')" class="px-4 py-2 rounded-xl text-xs font-bold">
                                                                    {{ __('Cancel') }}
                                                                </x-secondary-button>
                                                                <x-danger-button class="px-4 py-2 rounded-xl text-xs font-bold">
                                                                    {{ __('Confirm Suspension') }}
                                                                </x-danger-button>
                                                            </div>
                                                        </form>
                                                    </x-modal>

                                                @elseif ($community->status === 'SUSPENDED')
                                                    <button
                                                        type="button"
                                                        x-data=""
                                                        x-on:click.prevent="$dispatch('open-modal', 'reactivate-community-{{ $community->id }}')"
                                                        class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 rounded-lg font-bold text-xs text-white uppercase tracking-wider shadow-xs transition duration-150"
                                                    >
                                                        {{ __('Reactivate') }}
                                                    </button>

                                                    <!-- Reactivate Modal -->
                                                    <x-modal name="reactivate-community-{{ $community->id }}" labelledby="reactivate-community-title-{{ $community->id }}" focusable>
                                                        <form method="POST" action="{{ route('admin.communities.reactivate', $community) }}" class="p-6 sm:p-8 text-left">
                                                            @csrf
                                                            <h2 id="reactivate-community-title-{{ $community->id }}" class="text-lg font-bold text-slate-900 tracking-tight">
                                                                {{ __('Reactivate Community: :name', ['name' => $community->name]) }}
                                                            </h2>
                                                            <p class="mt-2 text-xs text-slate-500 leading-relaxed">
                                                                {{ __('Reactivating this community will restore full access for creator and existing members without any data loss. This action is audited.') }}
                                                            </p>

                                                            <div class="mt-4">
                                                                <x-input-label for="reason-reactivate-{{ $community->id }}" :value="__('Reason for Reactivation (Mandatory)')" />
                                                                <textarea
                                                                    id="reason-reactivate-{{ $community->id }}"
                                                                    name="reason"
                                                                    required
                                                                    rows="3"
                                                                    minlength="3"
                                                                    maxlength="1000"
                                                                    class="mt-1.5 block w-full rounded-xl border-slate-200 shadow-2xs focus:border-emerald-500 focus:ring-emerald-500 text-sm"
                                                                    placeholder="{{ __('Specify reason (e.g. Issue resolved by owner, appeal approved)...') }}"
                                                                ></textarea>
                                                            </div>

                                                            <div class="mt-6 flex justify-end gap-3">
                                                                <x-secondary-button x-on:click="$dispatch('close')" class="px-4 py-2 rounded-xl text-xs font-bold">
                                                                    {{ __('Cancel') }}
                                                                </x-secondary-button>
                                                                <x-primary-button class="px-4 py-2 rounded-xl text-xs font-bold">
                                                                    {{ __('Confirm Reactivation') }}
                                                                </x-primary-button>
                                                            </div>
                                                        </form>
                                                    </x-modal>

                                                @else
                                                    <span class="text-xs text-slate-400 font-medium">
                                                        {{ __('Archived (No action)') }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">
                            {{ $communities->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
