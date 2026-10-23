@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-line hover:border-slate-300 focus:border-brand focus:ring-1 focus:ring-brand rounded-lg shadow-2xs text-sm text-ink bg-surface placeholder:text-muted/60 transition-colors duration-150']) }}>
