<x-app-layout>
    @php
        $paidAdmission = $community->access_mode === 'PAID';
        $joinAllowed = !$membership || $membership->status === 'LEFT' || ($paidAdmission && $membership->status === 'PENDING_PAYMENT');
    @endphp
    <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:py-10">
        <a href="{{ route('communities.index') }}" class="inline-flex min-h-10 items-center text-sm font-semibold text-brand hover:underline">← {{ __('All communities') }}</a>
        <div class="mt-4 grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="min-w-0 space-y-6">
                <section class="overflow-hidden rounded-xl border border-line bg-surface" aria-labelledby="community-preview-title">
                    <div class="p-5 sm:p-7">
                        <p class="text-xs font-semibold uppercase tracking-wide text-brand">{{ __('Community preview') }}</p>
                        <h1 id="community-preview-title" class="mt-2 break-words text-2xl font-bold tracking-tight text-ink sm:text-3xl">{{ $community->name }}</h1>
                        <p class="mt-3 text-sm text-muted">{{ __('By :creator', ['creator' => $creatorName]) }} · {{ trans_choice(':count member|:count members', $activeMemberCount, ['count' => number_format($activeMemberCount)]) }}</p>
                        <a href="#community-admission" class="mt-4 inline-flex min-h-11 items-center rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-hover lg:hidden">{{ $joinAllowed ? ($paidAdmission ? __('View price and join options') : __('Join this free community')) : __('Review access options') }} ↓</a>
                    </div>
                    @if ($community->cover_path)
                        <x-media-frame :src="route('communities.cover.show', $community)" :alt="$community->name" class="w-full" />
                    @endif
                    <div class="p-5 sm:p-7">
                        <p class="whitespace-pre-line break-words text-base leading-relaxed text-muted">{{ $community->description ?: __('Explore the published course overview below before joining this community.') }}</p>
                    </div>
                </section>

                @if ($introEmbedUrl)
                    <section class="overflow-hidden rounded-xl border border-line bg-surface" aria-labelledby="preview-introduction">
                        <h2 id="preview-introduction" class="px-5 py-4 text-lg font-semibold">{{ __('Meet the community') }}</h2>
                        <div class="aspect-video bg-slate-100">
                            <iframe src="{{ $introEmbedUrl }}" title="{{ __('Introduction to :community', ['community' => $community->name]) }}" class="h-full w-full border-0" loading="lazy" allow="fullscreen; picture-in-picture" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
                        </div>
                    </section>
                @endif

                <section class="rounded-xl border border-line bg-surface p-5 sm:p-7" aria-labelledby="preview-curriculum">
                    <h2 id="preview-curriculum" class="text-xl font-semibold">{{ __('What you can learn') }}</h2>
                    <p class="mt-2 text-sm leading-relaxed text-muted">{{ __('Preview published courses before joining. Lesson content and discussions open with the required membership and course access.') }}</p>
                    <div class="mt-5 space-y-3">
                        @forelse ($previewCourses as $course)
                            <article class="rounded-lg border border-line bg-canvas p-4">
                                <h3 class="break-words font-semibold text-ink">{{ $course->title }}</h3>
                                @if ($course->description)
                                    <p class="mt-2 break-words text-sm leading-relaxed text-muted">{{ \Illuminate\Support\Str::limit($course->description, 260) }}</p>
                                @endif
                                <p class="mt-3 flex flex-wrap gap-x-3 gap-y-1 text-xs font-medium text-muted">
                                    <span>{{ trans_choice(':count published lesson|:count published lessons', $course->lessons_count) }}</span>
                                    @if ($course->access_mode === 'INCLUDED')
                                        <span class="text-brand">{{ __('Included with membership') }}</span>
                                    @else
                                        <span>{{ $course->price > 0 ? __('Separate purchase: :price VND', ['price' => number_format($course->price, 0, ',', '.')]) : __('Separate course access required') }}</span>
                                    @endif
                                </p>
                            </article>
                        @empty
                            <p class="rounded-lg bg-canvas p-4 text-sm text-muted">{{ __('No published courses yet. Check back for new learning content.') }}</p>
                        @endforelse
                    </div>
                    @if ($publishedCourseCount > $previewCourses->count())
                        <p class="mt-4 text-sm text-muted">{{ __('Showing :shown of :total published courses. Join to explore the classroom.', ['shown' => $previewCourses->count(), 'total' => $publishedCourseCount]) }}</p>
                    @endif
                </section>

                @if ($community->rules)
                    <details class="rounded-xl border border-line bg-surface p-5">
                        <summary class="cursor-pointer font-semibold text-ink focus-visible:outline-brand">{{ __('Community guidelines') }}</summary>
                        <p class="mt-4 whitespace-pre-line break-words text-sm leading-relaxed text-muted">{{ $community->rules }}</p>
                    </details>
                @endif
            </div>

            <aside id="community-admission" class="scroll-mt-24 rounded-xl border border-line bg-surface p-5 lg:sticky lg:top-24" aria-labelledby="community-admission-heading">
                <h2 id="community-admission-heading" class="text-lg font-semibold">{{ __('Join this community') }}</h2>
                <p class="mt-4 text-3xl font-bold tracking-tight text-ink">{{ $paidAdmission ? number_format($community->price_vnd, 0, ',', '.').' VND' : __('Free') }}</p>
                <p class="mt-1 text-sm text-muted">{{ $paidAdmission ? ($community->billing_period === 'TERM_30_DAYS' ? __('Per 30 days · manual renewal') : __('One-time community admission')) : __('No community admission fee') }}</p>
                <ul class="mt-5 space-y-3 border-y border-line py-5 text-sm leading-relaxed text-muted">
                    <li>{{ __('Member discussions and community activities') }}</li>
                    <li>{{ __('Published courses included with membership') }}</li>
                    <li>{{ __('Some courses require a separate purchase, as labelled above') }}</li>
                </ul>
                <x-input-error :messages="$errors->get('join')" class="mt-4" />
                @if ($membership?->canRenew(auth()->user()))
                    <a href="{{ route('account.memberships.show', $membership) }}" class="mt-5 inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-brand px-4 py-3 text-sm font-semibold text-white hover:bg-brand-hover focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">{{ __('Review and renew access') }}</a>
                @elseif ($joinAllowed)
                    <form method="POST" action="{{ route('communities.join', $community) }}" class="mt-5" x-data="{ submitting: false }" @submit="submitting = true">
                        @csrf
                        <button type="submit" :disabled="submitting" :aria-busy="submitting.toString()" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-brand px-4 py-3 text-sm font-semibold text-white transition-colors hover:bg-brand-hover focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand disabled:cursor-wait disabled:opacity-60">
                            {{ $paidAdmission ? __('Continue to checkout') : __('Join community') }}
                        </button>
                    </form>
                    <p class="mt-3 text-xs leading-relaxed text-muted">{{ $paidAdmission ? __('Membership opens after payment is confirmed. Checkout currently uses SePay Test Mode.') : __('Join with your signed-in account. Your email must be verified.') }}</p>
                @else
                    <p class="mt-5 rounded-lg bg-canvas p-3 text-sm leading-relaxed text-muted">{{ __('Your current membership cannot be reactivated here. Contact the community creator about access.') }}</p>
                    <a href="{{ route('account.memberships.index') }}" class="mt-4 inline-flex min-h-10 items-center text-sm font-semibold text-brand hover:underline">{{ __('Review My Access') }} →</a>
                @endif
                <p class="mt-4 text-xs leading-relaxed text-muted">{{ __('This is a preview. Private posts, lessons and member information remain protected until you have access.') }}</p>
            </aside>
        </div>
    </div>
</x-app-layout>
