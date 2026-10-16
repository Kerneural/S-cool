<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 active:bg-gray-100 text-slate-700 text-sm font-semibold rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 transition-all duration-150 active:scale-[0.99] disabled:opacity-50 disabled:pointer-events-none cursor-pointer']) }}>
    {{ $slot }}
</button>
