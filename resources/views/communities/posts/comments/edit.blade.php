<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">{{ __('Edit comment') }}</h2></x-slot>
    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('communities.posts.comments.update', [$community, $post, $comment]) }}" class="bg-white p-6 shadow-sm rounded-lg space-y-4">
                @csrf
                @method('PUT')
                <x-input-label for="body" :value="__('Comment')" />
                <textarea id="body" name="body" rows="5" maxlength="2000" required class="block w-full border-gray-300 rounded-md shadow-sm">{{ old('body', $comment->body) }}</textarea>
                <x-input-error :messages="$errors->get('body')" />
                <div class="flex flex-wrap gap-4 items-center">
                    <x-primary-button>{{ __('Save comment') }}</x-primary-button>
                    <a href="{{ route('communities.posts.show', [$community, $post]) }}" class="text-sm text-indigo-600">{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
