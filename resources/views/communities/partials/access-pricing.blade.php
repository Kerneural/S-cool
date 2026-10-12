<div>
    <x-input-label for="access_mode" value="Access mode (invitation required)" />
    <select id="access_mode" name="access_mode" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
        <option value="FREE" @selected(old('access_mode', $community->access_mode ?? 'FREE') === 'FREE')>Free</option>
        <option value="PAID" @selected(old('access_mode', $community->access_mode ?? 'FREE') === 'PAID')>Paid — SePay Test Mode only</option>
    </select>
    <x-input-error :messages="$errors->get('access_mode')" />
    <x-input-label for="price_vnd" value="One-time price (whole VND; required for Paid)" class="mt-3" />
    <x-text-input id="price_vnd" name="price_vnd" type="number" min="1" max="4294967295" step="1" class="mt-1 block w-full" :value="old('price_vnd', $community->price_vnd ?? null)" />
    <x-input-error :messages="$errors->get('price_vnd')" />
    <p class="mt-1 text-xs text-gray-500">Existing payment attempts retain their original price. This does not revoke existing active memberships or grant access to pending members.</p>
</div>
