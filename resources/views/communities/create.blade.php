<x-app-layout>
    <x-slot name="header">
        <h1 class="font-bold text-xl text-slate-900 leading-tight">
            {{ __('Create Private Community') }}
        </h1>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xs rounded-2xl border border-slate-200/80">
                <div class="p-6 sm:p-8 text-slate-900">
                    <div class="border-b border-slate-100 pb-5 mb-6">
                        <h3 class="text-lg font-bold text-slate-900 tracking-tight">{{ __('Launch Your New Community') }}</h3>
                        <p class="text-xs text-slate-500 mt-1">Set up your community identity, access rules, and pricing structure.</p>
                    </div>

                    <form method="POST" action="{{ route('communities.store') }}" class="space-y-6">
                        @csrf

                        <!-- Community Name -->
                        <div>
                            <x-input-label for="name" :value="__('Community Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1.5 block w-full rounded-xl border-slate-200 py-2.5" :value="old('name')" required autofocus placeholder="e.g. AI Master Hub" />
                            <x-input-error class="mt-1.5" :messages="$errors->get('name')" />
                        </div>

                        <!-- Slug -->
                        <div>
                            <x-input-label for="slug" :value="__('URL Slug')" />
                            <div class="mt-1.5 flex rounded-xl shadow-2xs overflow-hidden border border-slate-200 bg-white focus-within:border-teal-500 focus-within:ring-1 focus-within:ring-teal-500">
                                <span class="inline-flex items-center px-4 bg-slate-50 border-r border-slate-200 text-slate-500 font-mono text-xs">
                                    /communities/
                                </span>
                                <x-text-input id="slug" name="slug" type="text" class="block w-full border-0 focus:ring-0 text-xs font-mono py-2.5 px-3" :value="old('slug')" required placeholder="ai-master-hub" />
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ __('Unique address: ASCII letters, numbers, dashes or underscores. The word create is reserved. Letters are saved in lowercase.') }}
                            </p>
                            <x-input-error class="mt-1.5" :messages="$errors->get('slug')" />
                        </div>

                        <!-- Description -->
                        <div>
                            <x-input-label for="description" :value="__('Description (Optional)')" />
                            <textarea id="description" name="description" rows="4" class="mt-1.5 block w-full border-slate-200 focus:border-teal-500 focus:ring-teal-500 rounded-xl shadow-2xs text-sm py-2.5" placeholder="{{ __('What is this community about?') }}">{{ old('description') }}</textarea>
                            <x-input-error class="mt-1.5" :messages="$errors->get('description')" />
                        </div>

                        @include('communities.partials.access-pricing')

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                            <a href="{{ route('communities.index') }}" class="inline-flex items-center px-4 py-2.5 bg-white border border-slate-200 rounded-xl font-bold text-xs text-slate-700 uppercase tracking-widest shadow-2xs hover:bg-slate-50 transition duration-150">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button class="px-6 py-2.5 rounded-xl font-bold text-xs tracking-wider">
                                {{ __('Create Community') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
