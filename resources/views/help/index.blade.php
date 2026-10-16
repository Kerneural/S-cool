<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-900 leading-tight">
            {{ __('Help Center & Community Guidelines') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <!-- Hero Introduction -->
            <div class="bg-gradient-to-br from-slate-900 via-teal-950 to-slate-900 rounded-3xl p-8 sm:p-10 text-white shadow-lg relative overflow-hidden border border-teal-900/40">
                <div class="relative z-10 max-w-2xl space-y-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30">
                        Help Center
                    </span>
                    <h1 class="text-3xl sm:text-4xl font-black tracking-tight leading-tight">
                        Welcome to the S-cool Help Center
                    </h1>
                    <p class="text-slate-300 text-sm sm:text-base leading-relaxed">
                        Comprehensive guide to community onboarding, classroom courses, event calendars, content safety, and simulated payments.
                    </p>

                    <div class="pt-4 flex flex-wrap gap-3">
                        <a href="{{ route('communities.index') }}" class="px-5 py-2.5 bg-teal-500 hover:bg-teal-400 text-slate-950 rounded-xl text-xs sm:text-sm font-bold shadow-md shadow-teal-500/20 transition duration-150">
                            Browse Communities
                        </a>
                        <a href="{{ route('profile.edit') }}" class="px-5 py-2.5 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs sm:text-sm font-semibold transition border border-white/15 backdrop-blur-xs">
                            Account Settings
                        </a>
                        <a href="{{ route('account.payment-history.index') }}" class="px-5 py-2.5 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs sm:text-sm font-semibold transition border border-white/15 backdrop-blur-xs">
                            Payment History
                        </a>
                    </div>
                </div>
            </div>

            <!-- Critical Safety Notice: SePay Test Mode Notice (AC-05) -->
            <div class="bg-amber-50/90 border border-amber-300/80 rounded-2xl p-6 sm:p-7 shadow-xs">
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-amber-100 text-amber-800 rounded-xl shrink-0 mt-0.5">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="space-y-2">
                        <h3 class="text-sm font-black text-amber-950 uppercase tracking-wider">
                            Important Notice: SePay Test Mode Environment
                        </h3>
                        <p class="text-sm text-amber-900/90 leading-relaxed font-medium">
                            Community admission, individual course purchases and manual membership renewal operate in SePay Test Mode. Included courses require effective community access; one-time courses also require their own entitlement. Fixed-term access expires after 30 UTC days, with no grace period or automatic debit. Use My Access to renew an eligible expired term. Existing lifetime memberships never expire merely because community pricing changes.
                            QR codes and transfer details displayed on the checkout screen are simulated for testing purposes only.
                        </p>
                        <div class="mt-2 text-sm text-rose-800 font-bold bg-amber-100/80 p-3 rounded-xl border border-amber-300/60">
                            DO NOT transfer real money from your actual bank account. Any funds sent mistakenly to test accounts cannot be automatically refunded.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help Guide Sections Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- 1. Community Access & Private Invitations -->
                <div class="bg-white p-7 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center font-black text-sm">
                            1
                        </div>
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">Community Access & Private Invitations</h3>
                    </div>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Each community operates as a private space. You can only join when you receive a valid invitation link from the community creator.
                    </p>
                    <ul class="text-xs text-slate-500 space-y-2 list-disc list-inside">
                        <li>Invitation links have expiration dates and can be revoked by the creator.</li>
                        <li>For paid communities, you must complete the simulated checkout step to activate your active membership.</li>
                        <li>If suspended or removed from a group, content access is revoked immediately.</li>
                    </ul>
                </div>

                <!-- 2. Classroom & Course Publishing Rules -->
                <div class="bg-white p-7 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-black text-sm">
                            2
                        </div>
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">Classroom & Course Publishing Rules</h3>
                    </div>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        The Classroom module organizes learning into structured courses, sections, and lessons with instructional videos and exercises.
                    </p>
                    <ul class="text-xs text-slate-500 space-y-2 list-disc list-inside">
                        <li>Only courses marked as <strong>PUBLISHED</strong> by the creator are visible to members.</li>
                        <li>Draft courses under development remain accessible only to the creator.</li>
                        <li>Learning progress is tracked securely and calculated based on your completed lessons.</li>
                    </ul>
                </div>

                <!-- 3. Events & Timezone Standardization -->
                <div class="bg-white p-7 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center font-black text-sm">
                            3
                        </div>
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">Events & Timezone Standardization</h3>
                    </div>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Online workshops, recurring meetings, and community events are synchronized seamlessly.
                    </p>
                    <ul class="text-xs text-slate-500 space-y-2 list-disc list-inside">
                        <li>Timestamps are stored in UTC to prevent scheduling discrepancies across global members.</li>
                        <li>The interface allows seamless viewing in your local browser timezone.</li>
                        <li>Meeting links (Google Meet, Zoom, etc.) are only visible to active members.</li>
                    </ul>
                </div>

                <!-- 4. Content Reporting & Moderation Standards -->
                <div class="bg-white p-7 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-700 flex items-center justify-center font-black text-sm">
                            4
                        </div>
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">Content Reporting & Moderation Standards</h3>
                    </div>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        To maintain a respectful learning environment, members can report content that violates guidelines.
                    </p>
                    <ul class="text-xs text-slate-500 space-y-2 list-disc list-inside">
                        <li>You can report posts or comments directly with a clear reason (minimum 10 characters).</li>
                        <li>Reports are delivered privately to the creator's moderation queue and are never visible to other members.</li>
                        <li>Creators can remove violating content or dismiss reports without merit.</li>
                    </ul>
                </div>
            </div>

            <!-- Footer Quick Links -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
                <div>
                    S-cool Learning & Community Platform &bull; Test Mode Environment
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ route('communities.index') }}" class="hover:text-teal-600 font-bold">Communities</a>
                    <a href="{{ route('profile.edit') }}" class="hover:text-teal-600 font-bold">Notification Preferences</a>
                    <a href="{{ route('account.payment-history.index') }}" class="hover:text-teal-600 font-bold">Payment History</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
