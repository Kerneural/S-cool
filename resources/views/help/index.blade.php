<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Help Center & Community Guidelines') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <!-- Hero Introduction -->
            <div class="bg-gradient-to-r from-indigo-700 to-purple-800 rounded-2xl p-8 text-white shadow-md">
                <h1 class="text-3xl font-extrabold tracking-tight">
                    Welcome to the S-cool Help Center
                </h1>
                <p class="mt-2 text-indigo-100 max-w-2xl text-sm sm:text-base leading-relaxed">
                    Comprehensive guide to community onboarding, classroom courses, event calendars, content safety, and simulated payments.
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('communities.index') }}" class="px-4 py-2 bg-white text-indigo-700 hover:bg-indigo-50 rounded-lg text-xs sm:text-sm font-semibold shadow-sm transition">
                        Browse Communities
                    </a>
                    <a href="{{ route('profile.edit') }}" class="px-4 py-2 bg-indigo-600/75 hover:bg-indigo-600 text-white rounded-lg text-xs sm:text-sm font-semibold transition border border-indigo-400/30">
                        Account Settings
                    </a>
                    <a href="{{ route('account.payment-history.index') }}" class="px-4 py-2 bg-indigo-600/75 hover:bg-indigo-600 text-white rounded-lg text-xs sm:text-sm font-semibold transition border border-indigo-400/30">
                        Payment History
                    </a>
                </div>
            </div>

            <!-- Critical Safety Notice: SePay Test Mode Notice (AC-05) -->
            <div class="bg-amber-50 border-2 border-amber-300 rounded-xl p-6 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="p-2 bg-amber-100 rounded-lg text-amber-800 shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-amber-900 uppercase tracking-wide">
                            Important Notice: SePay Test Mode Environment
                        </h3>
                        <p class="mt-1 text-sm text-amber-800 leading-relaxed font-medium">
                            Community admission, individual course purchases and manual membership renewal operate in SePay Test Mode. Included courses require effective community access; one-time courses also require their own entitlement. Fixed-term access expires after 30 UTC days, with no grace period or automatic debit. Use My Access to renew an eligible expired term. Existing lifetime memberships never expire merely because community pricing changes.
                            QR codes and transfer details displayed on the checkout screen are simulated for testing purposes only.
                        </p>
                        <p class="mt-2 text-sm text-red-700 font-bold bg-amber-100/75 p-2.5 rounded-lg border border-amber-300">
                            DO NOT transfer real money from your actual bank account. Any funds sent mistakenly to test accounts cannot be automatically refunded.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Help Guide Sections Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- 1. Community Access & Private Invitations -->
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">
                            1
                        </div>
                        <h3 class="text-base font-bold text-gray-900">Community Access & Private Invitations</h3>
                    </div>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Each community operates as a private space. You can only join when you receive a valid invitation link from the community creator.
                    </p>
                    <ul class="text-xs text-gray-500 space-y-1.5 list-disc list-inside">
                        <li>Invitation links have expiration dates and can be revoked by the creator.</li>
                        <li>For paid communities, you must complete the simulated checkout step to activate your active membership.</li>
                        <li>If suspended or removed from a group, content access is revoked immediately.</li>
                    </ul>
                </div>

                <!-- 2. Classroom & Course Publishing Rules -->
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">
                            2
                        </div>
                        <h3 class="text-base font-bold text-gray-900">Classroom & Course Publishing Rules</h3>
                    </div>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        The Classroom module organizes learning into structured courses, sections, and lessons with instructional videos and exercises.
                    </p>
                    <ul class="text-xs text-gray-500 space-y-1.5 list-disc list-inside">
                        <li>Only courses marked as <strong>PUBLISHED</strong> by the creator are visible to members.</li>
                        <li>Draft courses under development remain accessible only to the creator.</li>
                        <li>Learning progress is tracked securely and calculated based on your completed lessons.</li>
                    </ul>
                </div>

                <!-- 3. Events & Timezone Standardization -->
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-sm">
                            3
                        </div>
                        <h3 class="text-base font-bold text-gray-900">Events & Timezone Standardization</h3>
                    </div>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Online workshops, recurring meetings, and community events are synchronized seamlessly.
                    </p>
                    <ul class="text-xs text-gray-500 space-y-1.5 list-disc list-inside">
                        <li>Timestamps are stored in UTC to prevent scheduling discrepancies across global members.</li>
                        <li>The interface allows seamless viewing in your local browser timezone.</li>
                        <li>Meeting links (Google Meet, Zoom, etc.) are only visible to active members.</li>
                    </ul>
                </div>

                <!-- 4. Content Reporting & Moderation Standards -->
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-sm">
                            4
                        </div>
                        <h3 class="text-base font-bold text-gray-900">Content Reporting & Moderation Standards</h3>
                    </div>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        To maintain a respectful learning environment, members can report content that violates guidelines.
                    </p>
                    <ul class="text-xs text-gray-500 space-y-1.5 list-disc list-inside">
                        <li>You can report posts or comments directly with a clear reason (minimum 10 characters).</li>
                        <li>Reports are delivered privately to the creator's moderation queue and are never visible to other members.</li>
                        <li>Creators can remove violating content or dismiss reports without merit.</li>
                    </ul>
                </div>
            </div>

            <!-- Footer Quick Links -->
            <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-gray-500">
                <div>
                    S-cool Learning & Community Platform &bull; Test Mode Environment
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ route('communities.index') }}" class="hover:text-indigo-600 font-medium">Communities</a>
                    <a href="{{ route('profile.edit') }}" class="hover:text-indigo-600 font-medium">Notification Preferences</a>
                    <a href="{{ route('account.payment-history.index') }}" class="hover:text-indigo-600 font-medium">Payment History</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
