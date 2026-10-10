<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Platform Administration — Communities') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ __('Platform-level oversight and access control. Actions are immutably logged for audit.') }}
                </p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                {{ __('Platform Admin') }}
            </span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Flash alerts -->
            @if (session('status'))
                <div class="p-4 rounded-lg bg-green-50 border border-green-200 text-sm font-medium text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 rounded-lg bg-red-50 border border-red-200 text-sm font-medium text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 rounded-lg bg-red-50 border border-red-200 text-sm font-medium text-red-800 space-y-1">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <!-- Summary KPI metrics -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('admin.communities.index') }}" class="block p-4 rounded-lg border {{ empty($statusFilter) ? 'bg-white border-indigo-500 ring-2 ring-indigo-200' : 'bg-white border-gray-200 hover:border-gray-300' }} shadow-sm transition">
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Total Communities') }}</div>
                    <div class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</div>
                </a>

                <a href="{{ route('admin.communities.index', ['status' => 'ACTIVE']) }}" class="block p-4 rounded-lg border {{ $statusFilter === 'ACTIVE' ? 'bg-white border-green-500 ring-2 ring-green-200' : 'bg-white border-gray-200 hover:border-gray-300' }} shadow-sm transition">
                    <div class="text-xs font-semibold text-green-700 uppercase tracking-wider">{{ __('Active') }}</div>
                    <div class="text-2xl font-bold text-green-700 mt-1">{{ $stats['active'] }}</div>
                </a>

                <a href="{{ route('admin.communities.index', ['status' => 'SUSPENDED']) }}" class="block p-4 rounded-lg border {{ $statusFilter === 'SUSPENDED' ? 'bg-white border-red-500 ring-2 ring-red-200' : 'bg-white border-gray-200 hover:border-gray-300' }} shadow-sm transition">
                    <div class="text-xs font-semibold text-red-700 uppercase tracking-wider">{{ __('Suspended') }}</div>
                    <div class="text-2xl font-bold text-red-700 mt-1">{{ $stats['suspended'] }}</div>
                </a>

                <a href="{{ route('admin.communities.index', ['status' => 'ARCHIVED']) }}" class="block p-4 rounded-lg border {{ $statusFilter === 'ARCHIVED' ? 'bg-white border-gray-500 ring-2 ring-gray-200' : 'bg-white border-gray-200 hover:border-gray-300' }} shadow-sm transition">
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Archived') }}</div>
                    <div class="text-2xl font-bold text-gray-700 mt-1">{{ $stats['archived'] }}</div>
                </a>
            </div>

            <!-- Main Communities Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Communities') }}</h3>
                            <p class="text-xs text-gray-500 mt-0.5">
                                @if ($statusFilter)
                                    {{ __('Showing filtered results for:') }} <span class="font-semibold">{{ $statusFilter }}</span>
                                @else
                                    {{ __('Showing all communities across the platform.') }}
                                @endif
                            </p>
                        </div>

                        <!-- Status filter pills -->
                        <div class="inline-flex rounded-md shadow-sm">
                            <a href="{{ route('admin.communities.index') }}" class="px-3 py-1.5 text-xs font-medium rounded-s-md border border-gray-300 {{ empty($statusFilter) ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                                {{ __('All') }}
                            </a>
                            <a href="{{ route('admin.communities.index', ['status' => 'ACTIVE']) }}" class="px-3 py-1.5 text-xs font-medium border-t border-b border-gray-300 {{ $statusFilter === 'ACTIVE' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                                {{ __('Active') }}
                            </a>
                            <a href="{{ route('admin.communities.index', ['status' => 'SUSPENDED']) }}" class="px-3 py-1.5 text-xs font-medium border-t border-b border-gray-300 {{ $statusFilter === 'SUSPENDED' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                                {{ __('Suspended') }}
                            </a>
                            <a href="{{ route('admin.communities.index', ['status' => 'ARCHIVED']) }}" class="px-3 py-1.5 text-xs font-medium rounded-e-md border border-gray-300 {{ $statusFilter === 'ARCHIVED' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                                {{ __('Archived') }}
                            </a>
                        </div>
                    </div>

                    @if ($communities->isEmpty())
                        <div class="text-center py-12 text-gray-500">
                            <p class="text-sm">{{ __('No communities found for the selected criteria.') }}</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                            {{ __('ID') }}
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                            {{ __('Community') }}
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                            {{ __('Creator') }}
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                            {{ __('Status') }}
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                            {{ __('Recent Audit Record') }}
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                            {{ __('Actions') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 text-sm">
                                    @foreach ($communities as $community)
                                        @php
                                            $latestAction = $community->platformAdminActions->sortByDesc('id')->first();
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 font-mono text-xs text-gray-500">
                                                #{{ $community->id }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="font-semibold text-gray-900">{{ $community->name }}</div>
                                                <div class="text-xs font-mono text-gray-400">/{{ $community->slug }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-gray-700">
                                                @if ($community->creator)
                                                    <div class="font-medium">{{ $community->creator->name }}</div>
                                                    <div class="text-xs text-gray-400">{{ $community->creator->email }}</div>
                                                @else
                                                    <span class="text-xs text-gray-400">{{ __('N/A') }}</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">
                                                @if ($community->status === 'ACTIVE')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        {{ __('ACTIVE') }}
                                                    </span>
                                                @elseif ($community->status === 'SUSPENDED')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        {{ __('SUSPENDED') }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        {{ $community->status }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-xs text-gray-600">
                                                @if ($latestAction)
                                                    <div class="font-semibold text-gray-800">
                                                        {{ $latestAction->action }} {{ __('by') }} {{ $latestAction->admin?->name ?? __('Admin') }}
                                                    </div>
                                                    <div class="text-gray-500 truncate max-w-xs" title="{{ $latestAction->reason }}">
                                                        "{{ $latestAction->reason }}"
                                                    </div>
                                                    <div class="text-gray-400 text-[11px] mt-0.5">
                                                        {{ $latestAction->created_at?->diffForHumans() }}
                                                    </div>
                                                @else
                                                    <span class="text-gray-400">{{ __('No actions recorded') }}</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                @if ($community->status === 'ACTIVE')
                                                    <button
                                                        type="button"
                                                        x-data=""
                                                        x-on:click.prevent="$dispatch('open-modal', 'suspend-community-{{ $community->id }}')"
                                                        class="inline-flex items-center px-3 py-1 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                                    >
                                                        {{ __('Suspend') }}
                                                    </button>

                                                    <!-- Suspend Modal -->
                                                    <x-modal name="suspend-community-{{ $community->id }}" focusable>
                                                        <form method="POST" action="{{ route('admin.communities.suspend', $community) }}" class="p-6 text-left">
                                                            @csrf
                                                            <h2 class="text-lg font-semibold text-gray-900">
                                                                {{ __('Suspend Community: :name', ['name' => $community->name]) }}
                                                            </h2>
                                                            <p class="mt-2 text-sm text-gray-600">
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
                                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm"
                                                                    placeholder="{{ __('Specify reason (e.g. Terms of Service violation, suspicious activity)...') }}"
                                                                ></textarea>
                                                            </div>

                                                            <div class="mt-6 flex justify-end gap-3">
                                                                <x-secondary-button x-on:click="$dispatch('close')">
                                                                    {{ __('Cancel') }}
                                                                </x-secondary-button>
                                                                <x-danger-button>
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
                                                        class="inline-flex items-center px-3 py-1 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                                    >
                                                        {{ __('Reactivate') }}
                                                    </button>

                                                    <!-- Reactivate Modal -->
                                                    <x-modal name="reactivate-community-{{ $community->id }}" focusable>
                                                        <form method="POST" action="{{ route('admin.communities.reactivate', $community) }}" class="p-6 text-left">
                                                            @csrf
                                                            <h2 class="text-lg font-semibold text-gray-900">
                                                                {{ __('Reactivate Community: :name', ['name' => $community->name]) }}
                                                            </h2>
                                                            <p class="mt-2 text-sm text-gray-600">
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
                                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm"
                                                                    placeholder="{{ __('Specify reason (e.g. Issue resolved by owner, appeal approved)...') }}"
                                                                ></textarea>
                                                            </div>

                                                            <div class="mt-6 flex justify-end gap-3">
                                                                <x-secondary-button x-on:click="$dispatch('close')">
                                                                    {{ __('Cancel') }}
                                                                </x-secondary-button>
                                                                <x-primary-button>
                                                                    {{ __('Confirm Reactivation') }}
                                                                </x-primary-button>
                                                            </div>
                                                        </form>
                                                    </x-modal>

                                                @else
                                                    <span class="text-xs text-gray-400 font-medium">
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
