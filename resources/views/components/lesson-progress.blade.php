@props(['community', 'course', 'lesson'])
@php($completed = $lesson->isCompletedBy(auth()->user()))
<form method="POST" action="{{ route('communities.lessons.progress.update', [$community, $course, $lesson]) }}" class="flex flex-wrap items-center gap-3">
    @csrf
    <input type="hidden" name="completed" value="{{ $completed ? 0 : 1 }}">
    <span role="status" class="text-sm {{ $completed ? 'text-green-700' : 'text-gray-600' }}">{{ $completed ? __('Completed') : __('Incomplete') }}</span>
    <button type="submit" class="min-h-[44px] rounded border border-indigo-600 bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
        {{ $completed ? __('Mark Incomplete') : __('Mark Complete') }}
    </button>
</form>
