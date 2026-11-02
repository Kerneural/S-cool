<x-app-layout>
    <div class="max-w-5xl mx-auto py-8 sm:py-10 px-4 sm:px-6 lg:px-8 space-y-6">
        <x-community-workspace :community="$community" active-tab="messages" />

        @if (session('status'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm shadow-xs">
                {{ session('status') }}
            </div>
        @endif

        @livewire('conversation-thread', ['community' => $community, 'conversation' => $conversation])
    </div>
</x-app-layout>
