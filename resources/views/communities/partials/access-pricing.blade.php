<div>
    <x-input-label for="access_mode" value="Access mode (invitation required)" />
    <select id="access_mode" name="access_mode" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
        <option value="FREE" @selected(old('access_mode', $community->access_mode ?? 'FREE') === 'FREE')>Free</option>
        <option value="PAID" @selected(old('access_mode', $community->access_mode ?? 'FREE') === 'PAID')>Paid — SePay Test Mode only</option>
    </select>
    <x-input-error :messages="$errors->get('access_mode')" />
    <x-input-label for="billing_period" value="Paid access term" class="mt-3" />
    <select id="billing_period" name="billing_period" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        <option value="ONE_TIME" @selected(old('billing_period', $community->billing_period ?? 'ONE_TIME') === 'ONE_TIME')>One-time lifetime access</option>
        <option value="TERM_30_DAYS" @selected(old('billing_period', $community->billing_period ?? 'ONE_TIME') === 'TERM_30_DAYS')>30 UTC days — manual Test Mode renewal</option>
    </select>
    <x-input-error :messages="$errors->get('billing_period')" />
    <x-input-label for="price_vnd" value="Access price (whole VND; required for Paid)" class="mt-3" />
    <x-text-input id="price_vnd" name="price_vnd" type="number" min="1" max="4294967295" step="1" class="mt-1 block w-full" :value="old('price_vnd', $community->price_vnd ?? null)" />
    <x-input-error :messages="$errors->get('price_vnd')" />
    <x-input-label for="referral_commission_rate" value="Referral commission rate (0–30%)" class="mt-3" />
    <x-text-input id="referral_commission_rate" name="referral_commission_rate" type="number" min="0" max="30" step="1" class="mt-1 block w-full" :value="old('referral_commission_rate', $community->referral_commission_rate ?? 0)" />
    <x-input-error :messages="$errors->get('referral_commission_rate')" />
    <p class="mt-1 text-xs text-gray-500">0% disables affiliate commissions. When enabled (1–30%), approved referrers earn whole-VND floor commissions on community admission and course purchases within a 30-day window. All earnings are simulated in Test Mode.</p>
    <p class="mt-2 text-xs text-gray-500">Existing attempts retain their price and term snapshots. Existing lifetime memberships stay lifetime; existing term memberships keep their expiry and renew for 30 days at the current paid price. No automatic debit or grace period. Free access does not offer paid renewal.</p>
</div>
