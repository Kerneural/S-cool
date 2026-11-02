<x-app-layout>
<div class="py-8 sm:py-10">
<main class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-ink">{{ __('Website Management') }}</h1>
            <p class="mt-2 text-sm text-muted">{{ __('Choose a community to manage its courses, members and activity.') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.communities.index') }}" class="inline-flex min-h-[44px] items-center rounded-lg border border-line bg-white px-4 text-sm font-medium text-ink hover:bg-slate-50">{{ __('Website operations') }}</a>
            <a href="{{ route('communities.create') }}" class="inline-flex min-h-[44px] items-center rounded-lg bg-brand px-4 text-sm font-semibold text-white hover:bg-brand-hover">+ {{ __('Create Community') }}</a>
        </div>
    </header>
    <section class="rounded-xl border border-line bg-white" aria-label="{{ __('Your communities') }}">
        <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-line p-4">
            <div class="min-w-0 flex-1 basis-64">
                <label for="hub-search" class="mb-1 block text-sm font-medium">{{ __('Search communities') }}</label>
                <input id="hub-search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100" class="min-h-11 w-full rounded-lg border-slate-300 text-sm" placeholder="{{ __('Search by community name') }}">
            </div>
            <div>
                <label for="hub-status" class="mb-1 block text-sm font-medium">{{ __('Status') }}</label>
                <select id="hub-status" name="status" class="min-h-11 rounded-lg border-slate-300 text-sm">
                    <option value="">{{ __('All') }}</option>
                    <option value="ACTIVE" @selected(($filters['status'] ?? '') === 'ACTIVE')>{{ __('Active') }}</option>
                    <option value="ARCHIVED" @selected(($filters['status'] ?? '') === 'ARCHIVED')>{{ __('Archived') }}</option>
                    <option value="SUSPENDED" @selected(($filters['status'] ?? '') === 'SUSPENDED')>{{ __('Suspended') }}</option>
                </select>
            </div>
            <x-secondary-button type="submit">{{ __('Search') }}</x-secondary-button>
            @if (!empty($filters['q']) || !empty($filters['status']))
                <a href="{{ route('website-hub') }}" class="inline-flex min-h-11 items-center px-2 text-sm text-brand">{{ __('Clear filters') }}</a>
            @endif
        </form>
        <div class="hidden grid-cols-[minmax(0,1fr)_140px_100px_320px] gap-4 border-b border-line bg-slate-50 px-5 py-3 text-sm font-medium text-muted lg:grid" aria-hidden="true">
            <span>{{ __('Community') }}</span><span>{{ __('Status') }}</span><span>{{ __('Members') }}</span><span>{{ __('Actions') }}</span>
        </div>
        <ul class="divide-y divide-line">
            @forelse ($communities as $community)
                <li class="grid min-w-0 grid-cols-1 items-center gap-4 p-4 sm:p-5 lg:grid-cols-[minmax(0,1fr)_140px_100px_320px]">
                    <div class="flex min-w-0 items-center gap-3">
                        <x-media-frame :src="$community->cover_path ? route('communities.cover.show', $community) : null" class="h-12 w-16 shrink-0 rounded-lg bg-brand-soft">
                            <span class="text-lg font-semibold text-brand">{{ mb_strtoupper(mb_substr($community->name, 0, 1)) }}</span>
                        </x-media-frame>
                        <h2 class="min-w-0 break-words text-base font-semibold text-ink">{{ $community->name }}</h2>
                    </div>
                    <div><span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-medium {{ $community->isActive() ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-amber-200 bg-amber-50 text-amber-800' }}">{{ $community->isActive() ? __('Active') : __($community->status) }}</span></div>
                    <div class="text-sm text-muted"><span class="tabular-nums font-semibold text-ink">{{ $community->memberships_count }}</span><span class="md:sr-only"> {{ __('members') }}</span></div>
                    <div class="flex flex-wrap items-center gap-2 md:flex-nowrap">
                        <a href="{{ route('communities.manage', $community) }}" aria-label="{{ __('Manage') }} — {{ $community->name }}" class="inline-flex min-h-11 items-center rounded-lg bg-brand px-3 text-sm font-semibold text-white hover:bg-brand-hover">{{ __('Manage') }}</a>
                        <a href="{{ route('communities.posts.index', $community) }}" class="inline-flex min-h-11 items-center px-2 text-sm font-medium text-brand">{{ __('View community') }}</a>
                        <x-action-menu :label="__('Community actions').' — '.$community->name">
                            @foreach ([
                                [__('Manage courses'), route('communities.classroom.index', [$community, 'manage' => 1])],
                                [__('Members'), route('communities.members.index', $community)],
                                [__('Invitations'), route('communities.invitations.index', $community)],
                                [__('Orders'), route('communities.orders.index', $community)],
                                [__('Moderation reports'), route('communities.reports.index', $community)],
                                [__('Submissions'), route('communities.manage.proof-of-work.index', $community)],
                                [__('Community settings'), route('communities.edit', $community)],
                            ] as [$label, $url])
                                <a href="{{ $url }}" class="flex min-h-11 items-center rounded-lg px-3 text-sm hover:bg-slate-100">{{ $label }}</a>
                            @endforeach
                        </x-action-menu>
                    </div>
                </li>
            @empty
                <li class="p-10 text-center">
                    <h2 class="font-semibold text-ink">{{ __('No communities found.') }}</h2>
                    <p class="mt-2 text-sm text-muted">{{ __('Change the filters or create your first community.') }}</p>
                </li>
            @endforelse
        </ul>
        @if ($communities->hasPages())<div class="border-t border-line p-4">{{ $communities->links() }}</div>@endif
    </section>
</main>
</div>
</x-app-layout>
