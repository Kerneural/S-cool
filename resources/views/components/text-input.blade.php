@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-brand focus:ring-1 focus:ring-brand rounded-lg shadow-sm text-sm text-ink bg-white transition duration-150']) }}>
