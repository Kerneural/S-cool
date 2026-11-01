<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <nav class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1" aria-label="Breadcrumb">
                    <a href="{{ url('/') }}" class="hover:text-teal-600 transition">Home</a>
                    <span class="text-slate-300">/</span>
                    <span class="text-slate-800">Help & Task Guides</span>
                </nav>
                <h2 class="font-bold text-xl sm:text-2xl text-slate-900 leading-tight">
                    {{ __('S-cool Guides & Documentation') }}
                </h2>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-bold bg-amber-500/10 text-amber-800 border border-amber-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    Test Mode Environment
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-10" x-data="{ activeTab: 'member' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <!-- Hero Banner -->
            <div class="bg-gradient-to-br from-slate-900 via-teal-950 to-slate-900 rounded-3xl p-8 sm:p-10 text-white shadow-lg relative overflow-hidden border border-teal-900/40">
                <div class="relative z-10 max-w-3xl space-y-4">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30">
                        Public Task Walkthroughs
                    </span>
                    <h1 class="text-3xl sm:text-4xl font-bold tracking-tight leading-tight">
                        Clear, step-by-step guidance for every role on S-cool
                    </h1>
                    <p class="text-slate-300 text-sm sm:text-base leading-relaxed">
                        Learn how to join communities, navigate classrooms, submit practical proof-of-work, manage course publishing, and troubleshoot access in a safe environment.
                    </p>

                    <div class="pt-2 flex flex-wrap gap-3 text-xs sm:text-sm">
                        <a href="{{ route('communities.index') }}" class="px-5 py-2.5 bg-teal-500 hover:bg-teal-400 text-slate-950 rounded-xl font-bold shadow-md shadow-teal-500/20 transition duration-150">
                            Explore Communities
                        </a>
                        <a href="#test-mode-notice" class="px-5 py-2.5 bg-white/10 hover:bg-white/20 text-white rounded-xl font-semibold transition border border-white/15 backdrop-blur-xs">
                            Payment Safety Notice
                        </a>
                    </div>
                </div>
            </div>

            <!-- Critical Safety Notice: SePay Test Mode Notice (AC-02, AC-04) -->
            <div id="test-mode-notice" class="bg-amber-50/90 border border-amber-300/80 rounded-2xl p-6 sm:p-7 shadow-xs">
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-amber-100 text-amber-800 rounded-xl shrink-0 mt-0.5">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="space-y-2">
                        <h3 class="text-sm font-bold text-amber-950 uppercase tracking-wider">
                            Critical Security & Testing Boundary: SePay Test Mode
                        </h3>
                        <p class="text-sm text-amber-900/90 leading-relaxed font-medium">
                            Community admission, individual course purchases, and membership renewals on this platform run strictly in <strong>SePay Test Mode</strong>.
                            Included courses require active community membership; standalone courses also require their specific course entitlement. Fixed-term memberships expire after 30 UTC days without automatic debit.
                        </p>
                        <div class="mt-2 text-sm text-rose-800 font-bold bg-amber-100/90 p-3.5 rounded-xl border border-amber-300/80">
                            ⚠️ DO NOT transfer real money from banking apps. All QR codes, account numbers, and webhook events are simulated test fixtures.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Role-Based Task Navigation Tabs (AC-01, AC-05) -->
            <div class="border-b border-slate-200">
                <nav class="flex space-x-6" aria-label="Guide Categories">
                    <button type="button"
                            @click="activeTab = 'member'"
                            :class="activeTab === 'member' ? 'border-teal-600 text-teal-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium'"
                            class="py-3 px-1 border-b-2 text-sm transition focus:outline-none focus:ring-2 focus:ring-teal-500/20 rounded-t">
                        📘 Learner & Member Guides
                    </button>
                    <button type="button"
                            @click="activeTab = 'creator'"
                            :class="activeTab === 'creator' ? 'border-teal-600 text-teal-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium'"
                            class="py-3 px-1 border-b-2 text-sm transition focus:outline-none focus:ring-2 focus:ring-teal-500/20 rounded-t">
                        🛠️ Creator Studio & Operations
                    </button>
                    <button type="button"
                            @click="activeTab = 'troubleshooting'"
                            :class="activeTab === 'troubleshooting' ? 'border-teal-600 text-teal-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium'"
                            class="py-3 px-1 border-b-2 text-sm transition focus:outline-none focus:ring-2 focus:ring-teal-500/20 rounded-t">
                        🔧 Troubleshooting & Recovery
                    </button>
                </nav>
            </div>

            <!-- Tab 1: Learner & Member Guides -->
            <div x-show="activeTab === 'member'" class="space-y-8" x-cloak>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Task M1: Onboarding & Account Verification -->
                    <div class="bg-white p-7 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center font-bold text-xs">M1</span>
                            <h3 class="text-base font-bold text-slate-900">Joining a Community & Verifying Your Account</h3>
                        </div>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            How to register, accept an invitation token, and ensure your session cookies keep you signed in.
                        </p>
                        <div class="text-xs text-slate-600 space-y-2 bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <div class="font-bold text-slate-800 mb-1">Step-by-step walkthrough:</div>
                            <ol class="list-decimal list-inside space-y-1.5 text-slate-600">
                                <li>Open the invitation link received via email (or Mailpit in local dev).</li>
                                <li>Sign in or register an account at <code class="bg-white px-1 py-0.5 rounded border border-slate-200">127.0.0.1:8080/login</code>.</li>
                                <li>Verify your email using the signed URL link sent to Mailpit.</li>
                                <li>Click <strong>Accept Invitation</strong> on the community landing view.</li>
                            </ol>
                        </div>
                        <div class="text-xs text-teal-700 font-medium flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Result: Active membership row persisted; access to Classroom and Feed unlocked.
                        </div>
                    </div>

                    <!-- Task M2: Searching Content Across 3 Surfaces -->
                    <div class="bg-white p-7 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center font-bold text-xs">M2</span>
                            <h3 class="text-base font-bold text-slate-900">Finding Permitted Content via Live Search</h3>
                        </div>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            S-cool provides fast, typo-tolerant search across three distinct entrypoints.
                        </p>
                        <div class="text-xs text-slate-600 space-y-2 bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <div class="font-bold text-slate-800 mb-1">Search surfaces & shortcuts:</div>
                            <ul class="list-disc list-inside space-y-1.5 text-slate-600">
                                <li><strong>Surface 1 (Dashboard)</strong>: Live list search with debouncing; tolerates typos like <code class="bg-white px-1 py-0.5 rounded border border-slate-200">dokcer</code>.</li>
                                <li><strong>Surface 2 (Classroom)</strong>: Filters courses and sections within tenant boundaries without leaving the view.</li>
                                <li><strong>Surface 3 (Typeahead)</strong>: Global search dropdown with exact-first ranking and category tabs.</li>
                            </ul>
                        </div>
                        <div class="text-xs text-teal-700 font-medium flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Result: Scoped SQL candidates prevent N+1 queries and protect private course metadata.
                        </div>
                    </div>

                    <!-- Task M3: Course Learning & Submitting Proof of Work -->
                    <div class="bg-white p-7 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center font-bold text-xs">M3</span>
                            <h3 class="text-base font-bold text-slate-900">Classroom Progression & Proof of Work Submissions</h3>
                        </div>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Complete curriculum lessons, watch video instructions, and submit evidence of practical mastery.
                        </p>
                        <div class="text-xs text-slate-600 space-y-2 bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <div class="font-bold text-slate-800 mb-1">Progression walkthrough:</div>
                            <ol class="list-decimal list-inside space-y-1.5 text-slate-600">
                                <li>Enter an accessible course from your community's <strong>Classroom</strong> tab.</li>
                                <li>Read the markdown syllabus, run instructions, and inspect code examples.</li>
                                <li>For practical lessons, locate the <strong>Submit Proof of Work</strong> card at the lesson bottom.</li>
                                <li>Paste your GitHub repository, demo URL, or terminal log output, then submit.</li>
                            </ol>
                        </div>
                        <div class="text-xs text-teal-700 font-medium flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Result: Lesson progress status marks complete; submission queues for Creator review.
                        </div>
                    </div>

                    <!-- Task M4: Paid Access Simulation via SePay Test Mode -->
                    <div class="bg-white p-7 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center font-bold text-xs">M4</span>
                            <h3 class="text-base font-bold text-slate-900">Paid Access & Simulated Checkout</h3>
                        </div>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            How to experience paid community entry and standalone course purchases safely.
                        </p>
                        <div class="text-xs text-slate-600 space-y-2 bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <div class="font-bold text-slate-800 mb-1">Checkout simulation:</div>
                            <ol class="list-decimal list-inside space-y-1.5 text-slate-600">
                                <li>On a paid community card or About page, click <strong>Continue to checkout</strong>.</li>
                                <li>Review the generated reference code and transfer amount in Vietnamese Dong (VND).</li>
                                <li>In local development, the automated test suite or IPN runner triggers the verified webhook.</li>
                                <li>Upon receipt of the valid webhook, your pending order immediately transitions to active.</li>
                            </ol>
                        </div>
                        <div class="text-xs text-teal-700 font-medium flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Result: Payment status becomes succeeded; entitlement and lifetime access granted.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Creator Studio & Operations -->
            <div x-show="activeTab === 'creator'" class="space-y-8" x-cloak>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Task C1: Creating a Community & Setting Rules -->
                    <div class="bg-white p-7 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-xs">C1</span>
                            <h3 class="text-base font-bold text-slate-900">Setting Up a Community & Guidelines</h3>
                        </div>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Define your private workspace identity, code of conduct, and intro media.
                        </p>
                        <div class="text-xs text-slate-600 space-y-2 bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <div class="font-bold text-slate-800 mb-1">Setup checklist:</div>
                            <ul class="list-disc list-inside space-y-1.5 text-slate-600">
                                <li>Navigate to <strong>Dashboard &gt; Owned Communities &gt; Create Community</strong>.</li>
                                <li>Provide unique name, slug, description, and whole-VND price (or select Free).</li>
                                <li>Upload a cover image (JPEG/PNG/WebP, max 2MB, scaled automatically).</li>
                                <li>Draft clear onboarding rules to establish community conduct standards.</li>
                            </ul>
                        </div>
                        <div class="text-xs text-emerald-700 font-medium flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Result: Community initialized with default announcement, questions, and wins categories.
                        </div>
                    </div>

                    <!-- Task C2: Building Curriculum in Classroom -->
                    <div class="bg-white p-7 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-xs">C2</span>
                            <h3 class="text-base font-bold text-slate-900">Course Structuring & Publishing Flow</h3>
                        </div>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Organize courses into sequential sections and lessons with video embeds.
                        </p>
                        <div class="text-xs text-slate-600 space-y-2 bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <div class="font-bold text-slate-800 mb-1">Authoring steps:</div>
                            <ol class="list-decimal list-inside space-y-1.5 text-slate-600">
                                <li>In your community Classroom, click <strong>Create Course</strong>.</li>
                                <li>Add Course Sections (Modules) to break up complex topics.</li>
                                <li>Add individual lessons with Loom, YouTube, or direct MP4 video URLs.</li>
                                <li>Set lesson content in clean Markdown; mark as <em>Practical Lesson</em> if submissions are required.</li>
                                <li>Switch course status from <strong>DRAFT</strong> to <strong>PUBLISHED</strong> when ready.</li>
                            </ol>
                        </div>
                        <div class="text-xs text-emerald-700 font-medium flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Result: Draft lessons remain completely hidden from learners until explicitly published.
                        </div>
                    </div>

                    <!-- Task C3: Reviewing Proof of Work Submissions -->
                    <div class="bg-white p-7 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-xs">C3</span>
                            <h3 class="text-base font-bold text-slate-900">Creator Review Mode & Learner Feedback</h3>
                        </div>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Evaluate student exercise evidence, give feedback, and award completion status.
                        </p>
                        <div class="text-xs text-slate-600 space-y-2 bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <div class="font-bold text-slate-800 mb-1">Review workflow:</div>
                            <ul class="list-disc list-inside space-y-1.5 text-slate-600">
                                <li>Open the lesson in <strong>Creator Review Mode</strong>.</li>
                                <li>Inspect learner evidence links or uploaded code snippets.</li>
                                <li>Select <strong>Approve</strong> to award lesson credit or <strong>Request Changes</strong> with notes.</li>
                                <li>Approved completions update community points and leaderboard ranking automatically.</li>
                            </ul>
                        </div>
                        <div class="text-xs text-emerald-700 font-medium flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Result: Audit trail of creator reviews maintained; student notified of completion.
                        </div>
                    </div>

                    <!-- Task C4: Managing Members & Invitations -->
                    <div class="bg-white p-7 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-xs">C4</span>
                            <h3 class="text-base font-bold text-slate-900">Member Lifecycle, Suspension & Reactivation</h3>
                        </div>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Issue tokenized invitation links and maintain community quality through membership controls.
                        </p>
                        <div class="text-xs text-slate-600 space-y-2 bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <div class="font-bold text-slate-800 mb-1">Management actions:</div>
                            <ul class="list-disc list-inside space-y-1.5 text-slate-600">
                                <li>Send single-use email invitations with a 7-day expiration window.</li>
                                <li>View active members, their join dates, and contribution points in <strong>Members</strong> tab.</li>
                                <li>Temporarily suspend a disruptive member with a required audit reason.</li>
                                <li>Reactivate members when appropriate; past progress and points are restored safely.</li>
                            </ul>
                        </div>
                        <div class="text-xs text-emerald-700 font-medium flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Result: Suspended members immediately lose access to private feeds, courses, and calendar events.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Troubleshooting & Recovery -->
            <div x-show="activeTab === 'troubleshooting'" class="space-y-8" x-cloak>
                <div class="bg-white p-7 rounded-2xl border border-slate-200/80 shadow-xs space-y-6">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Common Symptoms & Recovery Steps</h3>
                        <p class="text-sm text-slate-600 mt-1">Quick resolutions for common access, payment, and rendering issues.</p>
                    </div>

                    <div class="space-y-4 text-xs sm:text-sm">
                        <!-- Issue 1 -->
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-2">
                            <div class="font-bold text-slate-900 flex items-center gap-2">
                                <span class="text-rose-600">●</span>
                                "My invitation link reports expired or invalid"
                            </div>
                            <p class="text-slate-600 text-xs leading-relaxed">
                                Invitation tokens expire after 7 days or after being accepted once. If you receive an error, contact the community creator to generate a new invitation. Ensure you are signed in with the exact email address the invitation was addressed to.
                            </p>
                        </div>

                        <!-- Issue 2 -->
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-2">
                            <div class="font-bold text-slate-900 flex items-center gap-2">
                                <span class="text-rose-600">●</span>
                                "Session mismatch or unexpected logout when navigating links"
                            </div>
                            <p class="text-slate-600 text-xs leading-relaxed">
                                Always use the canonical host <code class="bg-white px-1 py-0.5 rounded border border-slate-200 font-mono">http://127.0.0.1:8080</code>. Do not mix <code class="bg-white px-1 py-0.5 rounded border border-slate-200 font-mono">localhost</code> and <code class="bg-white px-1 py-0.5 rounded border border-slate-200 font-mono">127.0.0.1</code> in your browser bar, as session cookies are strictly host-scoped.
                            </p>
                        </div>

                        <!-- Issue 3 -->
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-2">
                            <div class="font-bold text-slate-900 flex items-center gap-2">
                                <span class="text-rose-600">●</span>
                                "Course video player shows black box or missing stream"
                            </div>
                            <p class="text-slate-600 text-xs leading-relaxed">
                                Ensure Loom or YouTube links use standard embed URLs. In local development, video files must be placed under authorized disk roots with valid MIME types. Direct document navigation to video streams is blocked by anti-sniff security guards.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Quick Links -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
                <div>
                    S-cool Learning & Community Platform &bull; Public Documentation
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ url('/') }}" class="hover:text-teal-600 font-bold transition">Home</a>
                    <a href="{{ route('communities.index') }}" class="hover:text-teal-600 font-bold transition">Communities</a>
                    <a href="{{ route('login') }}" class="hover:text-teal-600 font-bold transition">Sign In</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
