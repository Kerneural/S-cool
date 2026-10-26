@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl',
    'labelledby' => null,
    'beforeClose' => 'true',
])

@php
$maxWidth = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
][$maxWidth];
@endphp

<div
    x-data="{
        show: @js($show),
        returnFocus: null,
        closeModal() { if ({{ $beforeClose }}) this.show = false },
        focusables() {
            // All focusable element types...
            let selector = 'a, button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])'
            return [...$el.querySelectorAll(selector)]
                // All non-disabled elements...
                .filter(el => ! el.hasAttribute('disabled') && el.offsetParent !== null)
        },
        firstFocusable() { return this.focusables()[0] },
        lastFocusable() { return this.focusables().slice(-1)[0] },
        nextFocusable() {
            const elements = this.focusables()
            if (! elements.length) return $refs.dialog
            const index = elements.indexOf(document.activeElement)
            return elements[(index + 1) % elements.length]
        },
        prevFocusable() {
            const elements = this.focusables()
            if (! elements.length) return $refs.dialog
            const index = elements.indexOf(document.activeElement)
            return elements[(index <= 0 ? elements.length : index) - 1]
        },
    }"
    x-init="if (show) {
        document.body.classList.add('overflow-y-hidden');
        $nextTick(() => firstFocusable()?.focus());
    }
    $watch('show', value => {
        if (value) {
            document.body.classList.add('overflow-y-hidden');
            {{ $attributes->has('focusable') ? 'setTimeout(() => { if (show) firstFocusable()?.focus() }, 100)' : '' }}
        } else {
            document.body.classList.remove('overflow-y-hidden');
            $nextTick(() => {
                returnFocus?.focus();
                returnFocus = null;
            });
        }
    })"
    x-on:open-modal.window="if ($event.detail == '{{ $name }}') { returnFocus = document.activeElement; show = true }"
    x-on:close-modal.window="if ($event.detail == '{{ $name }}') closeModal()"
    x-on:close.stop="closeModal()"
    x-on:keydown.escape.window="if (show) closeModal()"
    x-on:keydown.tab.prevent="if (show) { ($event.shiftKey ? prevFocusable() : nextFocusable()).focus() }"
    x-show="show"
    class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50"
    :style="{ display: show ? 'block' : 'none' }"
    @if (! $show) style="display: none;" @endif
>
    <div
        x-show="show"
        class="fixed inset-0 transform transition-all"
        x-on:click="closeModal()"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs"></div>
    </div>

    <div
        x-ref="dialog"
        x-show="show"
        role="dialog"
        aria-modal="true"
        @if ($labelledby)
            aria-labelledby="{{ $labelledby }}"
        @else
            aria-label="{{ $name }}"
        @endif
        tabindex="-1"
        class="relative z-10 mb-6 bg-white rounded-2xl border border-line overflow-hidden shadow-2xl shadow-slate-900/20 transform transition-all sm:w-full {{ $maxWidth }} sm:mx-auto"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
    >
        {{ $slot }}
    </div>
</div>
