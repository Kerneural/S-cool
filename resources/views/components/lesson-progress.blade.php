@props(['community', 'course', 'lesson'])
@php($completed = $lesson->isCompletedBy(auth()->user()))
<div x-data="lessonProgress({
    completed: @js((bool) $completed),
    lessonId: @js($lesson->id),
    url: @js(route('communities.lessons.progress.update', [$community, $course, $lesson])),
    csrf: @js(csrf_token()),
    errorMessage: @js(__('Could not update progress. Please try again.'))
})" class="space-y-2">
    <form method="POST" action="{{ route('communities.lessons.progress.update', [$community, $course, $lesson]) }}" @submit.prevent="toggleProgress" class="flex flex-wrap items-center gap-3">
        @csrf
        <input type="hidden" name="completed" :value="completed ? 0 : 1" value="{{ $completed ? 0 : 1 }}">

        <span role="status" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold transition"
              :class="completed ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-slate-600 border border-gray-200'">
            <span x-text="completed ? @js(__('Completed')) : @js(__('Incomplete'))">{{ $completed ? __('Completed') : __('Incomplete') }}</span>
        </span>

        <button type="submit" :disabled="loading" class="min-h-[44px] inline-flex items-center justify-center px-4 py-2 rounded-xl text-sm font-semibold transition shadow-xs active:scale-[0.99] focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 disabled:opacity-50 cursor-pointer"
                :class="completed ? 'bg-white border border-slate-300 text-slate-700 hover:bg-slate-50' : 'bg-brand hover:bg-brand-hover text-white'">
            <span x-show="!loading" x-text="completed ? '{{ __('Mark Incomplete') }}' : '{{ __('Mark Complete') }}'">
                {{ $completed ? __('Mark Incomplete') : __('Mark Complete') }}
            </span>
            <span x-show="loading" x-cloak class="inline-flex items-center gap-2">
                <svg class="animate-spin h-4 w-4 text-current" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                <span>{{ __('Saving...') }}</span>
            </span>
        </button>
    </form>

    <div role="alert" x-show="errorMessage" x-cloak class="text-xs font-semibold text-rose-600" x-text="errorMessage"></div>
</div>
