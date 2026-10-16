@props(['community', 'course', 'lesson'])
@php($completed = $lesson->isCompletedBy(auth()->user()))
<form method="POST" action="{{ route('communities.lessons.progress.update', [$community, $course, $lesson]) }}" class="flex flex-wrap items-center gap-3">
    @csrf
    <input type="hidden" name="completed" value="{{ $completed ? 0 : 1 }}">
    <span role="status" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ $completed ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-slate-600 border border-gray-200' }}">
        @if ($completed)
            <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            {{ __('Completed') }}
        @else
            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/></svg>
            {{ __('Incomplete') }}
        @endif
    </span>
    <button type="submit" class="min-h-[44px] inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-semibold transition shadow-sm active:scale-[0.99] focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 {{ $completed ? 'bg-white border border-gray-300 text-slate-700 hover:bg-gray-50' : 'bg-brand hover:bg-brand-hover text-white' }}">
        {{ $completed ? __('Mark Incomplete') : __('Mark Complete') }}
    </button>
</form>
