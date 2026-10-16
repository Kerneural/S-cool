<div class="border-t border-slate-100 pt-6 space-y-5">
    <div>
        <h4 class="text-sm font-bold text-slate-900 tracking-tight">Access & Pricing Settings</h4>
        <p class="text-xs text-slate-500 mt-0.5">Configure membership fee, billing structure, and referral affiliate rates.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <x-input-label for="access_mode" value="Access mode (invitation required)" />
            <select id="access_mode" name="access_mode" class="mt-1.5 block w-full rounded-xl border-slate-200 text-sm focus:border-teal-500 focus:ring-teal-500 py-2.5">
                <option value="FREE" @selected(old('access_mode', $community->access_mode ?? 'FREE') === 'FREE')>Free</option>
                <option value="PAID" @selected(old('access_mode', $community->access_mode ?? 'FREE') === 'PAID')>Paid — SePay Test Mode only</option>
            </select>
            <x-input-error :messages="$errors->get('access_mode')" class="mt-1.5" />
        </div>

        <div>
            <x-input-label for="billing_period" value="Paid access term" />
            <select id="billing_period" name="billing_period" class="mt-1.5 block w-full rounded-xl border-slate-200 text-sm focus:border-teal-500 focus:ring-teal-500 py-2.5">
                <option value="ONE_TIME" @selected(old('billing_period', $community->billing_period ?? 'ONE_TIME') === 'ONE_TIME')>One-time lifetime access</option>
                <option value="TERM_30_DAYS" @selected(old('billing_period', $community->billing_period ?? 'ONE_TIME') === 'TERM_30_DAYS')>30 UTC days — manual Test Mode renewal</option>
            </select>
            <x-input-error :messages="$errors->get('billing_period')" class="mt-1.5" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <x-input-label for="price_vnd" value="Access price (whole VND; required for Paid)" />
            <div class="relative mt-1.5">
                <x-text-input id="price_vnd" name="price_vnd" type="number" min="1" max="4294967295" step="1"
                    class="block w-full pr-14 rounded-xl border-slate-200 text-sm focus:border-teal-500 focus:ring-teal-500 py-2.5"
                    :value="old('price_vnd', $community->price_vnd ?? null)" placeholder="e.g. 500000" />
                <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-xs font-bold text-slate-400">
                    VND
                </div>
            </div>
            <x-input-error :messages="$errors->get('price_vnd')" class="mt-1.5" />
        </div>

        <div>
            <x-input-label for="referral_commission_rate" value="Referral commission rate (0–30%)" />
            <div class="relative mt-1.5">
                <x-text-input id="referral_commission_rate" name="referral_commission_rate" type="number" min="0" max="30" step="1"
                    class="block w-full pr-10 rounded-xl border-slate-200 text-sm focus:border-teal-500 focus:ring-teal-500 py-2.5"
                    :value="old('referral_commission_rate', $community->referral_commission_rate ?? 0)" />
                <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-xs font-bold text-slate-400">
                    %
                </div>
            </div>
            <x-input-error :messages="$errors->get('referral_commission_rate')" class="mt-1.5" />
        </div>
    </div>

    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/70 text-xs text-slate-600 space-y-2">
        <p>0% disables affiliate commissions. When enabled (1–30%), approved referrers earn whole-VND floor commissions on community admission and course purchases within a 30-day window. All earnings are simulated in Test Mode.</p>
        <p class="text-slate-500">Existing attempts retain their price and term snapshots. Existing lifetime memberships stay lifetime; existing term memberships keep their expiry and renew for 30 days at the current paid price. No automatic debit or grace period. Free access does not offer paid renewal.</p>
    </div>
</div>
