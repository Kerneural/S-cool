<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-surface hover:bg-slate-50/80 active:bg-slate-100 border border-line hover:border-slate-300 text-ink text-sm font-semibold rounded-lg shadow-xs hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 transition-all duration-150 active:scale-[0.99] disabled:opacity-50 disabled:pointer-events-none cursor-pointer']) }}>
    {{ $slot }}
</button>
