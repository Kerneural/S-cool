<x-app-layout>
    <x-slot name="header"><h1 class="font-semibold text-xl">Invitations — {{ $community->name }}</h1></x-slot>
    <div class="max-w-4xl mx-auto py-8 px-4 space-y-6">
        <a class="underline" href="{{ route('communities.show', $community) }}">Back to community</a>
        @if (session('status'))<p class="p-4 bg-green-50">{{ session('status') }}</p>@endif
        <form method="POST" action="{{ route('communities.invitations.store', $community) }}" class="p-6 bg-white rounded-lg space-y-4">
            @csrf
            <x-input-label for="email" value="Invite by email" />
            <x-text-input id="email" name="email" type="email" :value="old('email')" required class="w-full" />
            <x-input-error :messages="$errors->get('email')" />
            <x-primary-button>Send invitation</x-primary-button>
            <p class="text-xs text-gray-500">Valid for seven days. Only the invited, verified email can accept.</p>
        </form>
        @forelse ($invitations as $invitation)
            <div class="p-4 bg-white rounded-lg flex flex-wrap items-center justify-between gap-4">
                <div><p>{{ $invitation->normalized_email }}</p><p class="text-xs text-gray-500">{{ $invitation->status === 'PENDING' && ! $invitation->isPending() ? 'EXPIRED' : $invitation->status }} · {{ $invitation->sent_at ? 'Email delivered' : 'Not delivered' }}</p></div>
                @if ($invitation->isPending())
                    <form method="POST" action="{{ route('communities.invitations.revoke', [$community, $invitation]) }}">@csrf @method('DELETE')<x-secondary-button type="submit">Revoke</x-secondary-button></form>
                @endif
            </div>
        @empty
            <p class="text-gray-500">No invitations yet.</p>
        @endforelse
        {{ $invitations->links() }}
    </div>
</x-app-layout>
