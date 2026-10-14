<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $community->name }} &mdash; {{ __('Notifications') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-community-workspace :community="$community" activeTab="notifications" />

            @if (session('status'))
                <div class="p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 text-sm flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>{{ session('status') }}</span>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
                <!-- Header Toolbar -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 tracking-tight">{{ __('Notification Center') }}</h3>
                        <p class="text-sm text-gray-500 mt-0.5">
                            {{ __('Updates on discussion replies, new courses, and community activities.') }}
                        </p>
                    </div>

                    @if ($unreadCount > 0)
                        <form method="POST" action="{{ route('communities.notifications.read-all', $community) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 rounded-lg transition shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>{{ __('Mark all as read') }} ({{ $unreadCount }})</span>
                            </button>
                        </form>
                    @endif
                </div>

                <!-- Notifications List -->
                @if ($notifications->isEmpty())
                    <div class="text-center py-12 px-4">
                        <div class="w-14 h-14 mx-auto mb-4 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </div>
                        <h4 class="text-base font-semibold text-gray-900">{{ __('All caught up!') }}</h4>
                        <p class="text-sm text-gray-500 mt-1 max-w-sm mx-auto">
                            {{ __('No notifications found in this community. When new replies or activities occur, they will appear here.') }}
                        </p>
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach ($notifications as $notification)
                            @php
                                $isUnread = ! $notification->read_at;
                            @endphp
                            <div class="py-4 px-3 sm:px-4 rounded-xl transition flex flex-col sm:flex-row sm:items-center justify-between gap-4 {{ $isUnread ? 'bg-indigo-50/40 border border-indigo-100' : 'hover:bg-gray-50' }}">
                                <div class="flex items-start gap-3.5">
                                    <!-- Icon badge -->
                                    <div class="mt-0.5 shrink-0 w-9 h-9 rounded-full flex items-center justify-center {{ match($notification->type) {
                                        'post_reply' => 'bg-blue-100 text-blue-700',
                                        'course_published' => 'bg-emerald-100 text-emerald-700',
                                        'member_activated' => 'bg-amber-100 text-amber-700',
                                        default => 'bg-gray-100 text-gray-700'
                                    } }}">
                                        @if ($notification->type === 'post_reply')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                            </svg>
                                        @elseif ($notification->type === 'course_published')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                            </svg>
                                        @elseif ($notification->type === 'member_activated')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        @endif
                                    </div>

                                    <!-- Content -->
                                    <div class="space-y-1">
                                        <p class="text-sm {{ $isUnread ? 'font-semibold text-gray-900' : 'text-gray-700' }}">
                                            {{ $notification->summary() }}
                                        </p>
                                        <div class="flex items-center gap-2 text-xs text-gray-500">
                                            <span>{{ $notification->created_at->diffForHumans() }}</span>
                                            @if ($isUnread)
                                                <span class="inline-flex items-center px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-700 uppercase tracking-wider">
                                                    {{ __('Unread') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
                                    <a href="{{ route('communities.notifications.follow', [$community, $notification]) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 border border-gray-300 rounded-lg transition shadow-sm">
                                        <span>{{ __('View') }}</span>
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>

                                    @if ($isUnread)
                                        <form method="POST" action="{{ route('communities.notifications.read', [$community, $notification]) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition">
                                                {{ __('Mark read') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-4 border-t border-gray-100">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
