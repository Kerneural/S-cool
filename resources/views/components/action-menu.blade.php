@props(['label'])
<details {{ $attributes->class(['relative shrink-0']) }}
    x-data
    @click.outside="$root.open = false"
    @keydown.escape.stop.prevent="$root.open = false; $refs.actionTrigger.focus()">
    <summary x-ref="actionTrigger" aria-label="{{ $label }}" class="flex min-h-11 min-w-11 cursor-pointer list-none items-center justify-center rounded-lg border border-line bg-surface text-muted transition hover:bg-slate-100 hover:text-ink focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand [&::-webkit-details-marker]:hidden">
        <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/></svg>
    </summary>
    <div class="absolute right-0 top-full z-30 mt-1 w-52 max-w-[calc(100vw-3rem)] rounded-xl border border-line bg-white p-1.5 text-sm shadow-xl"
         @click="if ($event.target.closest('button, a')) { $root.open = false; $nextTick(() => $refs.actionTrigger.focus()) }">
        {{ $slot }}
    </div>
</details>
