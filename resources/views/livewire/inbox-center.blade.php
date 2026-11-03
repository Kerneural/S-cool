<div wire:poll.5s="refreshInbox" x-data="inboxPanel(@js(['newMessage' => __('You have a new message'), 'newNotification' => __('You have a new notification')]))" class="flex items-center gap-1"
     @inbox-arrived="arrived($event.detail)" @keydown.escape.stop="closePanel()"
     @focus.window="refresh()" @visibilitychange.document="if (!document.hidden) refresh()">
    @if ($available && $community)
        @foreach (['notifications' => __('Notifications'), 'messages' => __('Messages')] as $kind => $label)
            <button type="button" x-ref="{{ $kind }}" @click="toggle('{{ $kind }}')"
                    aria-label="{{ $label }}" :aria-expanded="(panel === '{{ $kind }}').toString()" aria-controls="inbox-{{ $kind }}"
                    class="relative inline-flex min-h-11 min-w-11 items-center justify-center rounded-xl p-2 text-slate-600 hover:bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-brand">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    @if ($kind === 'notifications')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    @endif
                </svg>
                @php($count = $kind === 'notifications' ? $notificationCount : $messageCount)
                @if ($count > 0)
                    <span data-inbox-badge="{{ $kind }}" class="absolute right-0 top-0 rounded-full bg-rose-600 px-1.5 text-[11px] font-bold text-white">{{ $count > 99 ? '99+' : $count }}<span class="sr-only"> {{ __('unread') }}</span></span>
                @endif
            </button>
        @endforeach

        @teleport('body')
        <div>
        <section id="inbox-notifications" x-cloak x-show="panel === 'notifications'" x-transition.opacity.duration.150ms
                 @keydown.escape.stop="closePanel()"
                 @click.outside="if (panel === 'notifications' && !$event.target.closest('[aria-controls^=inbox-]')) closePanel(false)"
                 role="dialog" aria-label="{{ __('Recent notifications') }}" tabindex="-1" x-ref="notificationPanel"
                 class="fixed inset-x-3 top-20 z-50 max-h-[calc(100dvh-6rem)] w-auto max-w-md overflow-y-auto rounded-2xl border border-line bg-white shadow-xl sm:left-auto sm:w-[28rem]">
            <div class="flex items-center justify-between border-b border-line px-4 py-2">
                <h2 class="font-bold">{{ __('Notifications') }}</h2>
                <button type="button" @click="closePanel()" class="min-h-11 min-w-11 text-xl" aria-label="{{ __('Close notifications') }}">×</button>
            </div>
            <div class="max-h-[60vh] overflow-y-auto">
                @forelse ($notices as $notice)
                    <a wire:key="notice-{{ $notice['id'] }}" href="{{ $notice['url'] }}" class="flex gap-3 border-b border-line p-4 hover:bg-slate-50 {{ $notice['unread'] ? 'bg-brand-soft' : '' }}">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full {{ $notice['unread'] ? 'bg-brand' : 'bg-slate-200' }}" aria-hidden="true"></span>
                        <span class="min-w-0"><span class="block text-sm {{ $notice['unread'] ? 'font-semibold' : '' }}">{{ $notice['summary'] }}</span><span class="text-xs text-muted">{{ $notice['time'] }}</span></span>
                    </a>
                @empty
                    <p class="p-6 text-sm text-muted">{{ __('No notifications yet.') }}</p>
                @endforelse
            </div>
            <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-2 text-sm">
                <button type="button" wire:click="markAllRead" wire:loading.attr="disabled" class="min-h-11 font-semibold text-brand">{{ __('Mark all as read') }}</button>
                <a href="{{ route('communities.notifications.index', $community) }}" class="min-h-11 py-3 text-brand">{{ __('View all') }}</a>
            </div>
        </section>

        <section id="inbox-messages" x-cloak x-show="panel === 'messages'" x-transition.opacity.duration.150ms
                 @keydown.escape.stop="closePanel()"
                 @click.outside="if (panel === 'messages' && !$event.target.closest('[aria-controls^=inbox-]')) closePanel(false)"
                 role="dialog" aria-label="{{ __('Recent messages') }}" tabindex="-1" x-ref="messagePanel"
                 class="fixed inset-x-3 top-20 z-50 max-h-[calc(100dvh-6rem)] w-auto max-w-md overflow-y-auto rounded-2xl border border-line bg-white shadow-xl sm:left-auto sm:w-[28rem]">
            <div class="flex items-center justify-between border-b border-line px-4 py-2">
                <h2 class="font-bold">{{ __('Messages') }}</h2>
                <button type="button" @click="closePanel()" class="min-h-11 min-w-11 text-xl" aria-label="{{ __('Close messages') }}">×</button>
            </div>
            @if ($activeConversation)
                <livewire:conversation-thread :community="$community" :conversation="$activeConversation" :compact="true" :key="'inbox-thread-'.$activeConversation->id" />
            @else
                <div class="max-h-[60vh] overflow-y-auto">
                    @forelse ($threads as $thread)
                        <button type="button" wire:key="inbox-thread-row-{{ $thread['id'] }}" wire:click="openConversation({{ $thread['id'] }})" class="flex w-full items-center gap-3 border-b border-line p-4 text-left hover:bg-slate-50">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-soft text-brand">{{ $thread['initial'] }}</span>
                            <span class="min-w-0 flex-1"><span class="block font-semibold">{{ $thread['name'] }}</span><span class="block truncate text-sm text-muted">{{ $thread['body'] ?: __('No messages yet') }}</span><span class="text-xs text-muted">{{ $thread['time'] }}</span></span>
                            @if ($thread['unread'] > 0)
                                <span class="rounded-full bg-rose-600 px-2 text-xs font-bold text-white">{{ $thread['unread'] }}<span class="sr-only"> {{ __('unread') }}</span></span>
                            @endif
                        </button>
                    @empty
                        <p class="p-6 text-sm text-muted">{{ __('No conversations yet. Open a member profile to start messaging.') }}</p>
                    @endforelse
                </div>
                <a href="{{ route('communities.messages.index', $community) }}" class="block p-4 text-center text-sm font-semibold text-brand">{{ __('View all messages') }}</a>
            @endif
        </section>
        <div x-cloak x-show="toast" x-transition.opacity role="status" aria-live="polite" class="fixed bottom-4 right-4 z-50 max-w-[calc(100vw-2rem)] rounded-xl bg-slate-900 px-4 py-3 text-sm text-white shadow-lg">
            <button type="button" @click="toggle(toastKind); toast = ''" x-text="toast" class="min-h-8"></button>
            <button type="button" @click="toast = ''" aria-label="{{ __('Close reminder') }}" class="ml-3 min-h-8 px-2">×</button>
        </div>
        <div wire:offline class="fixed bottom-4 left-4 z-50 rounded-xl bg-amber-100 p-3 text-sm text-amber-950" role="status">{{ __('Connection lost. Updates will resume when connected.') }}</div>
        </div>
        @endteleport
    @endif
</div>
