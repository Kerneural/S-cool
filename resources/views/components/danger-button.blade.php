<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-danger hover:bg-red-800 active:bg-red-900 text-white text-sm font-semibold rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-danger focus:ring-offset-2 transition-all duration-150 active:scale-[0.99] disabled:opacity-50 disabled:pointer-events-none cursor-pointer']) }}>
    {{ $slot }}
</button>
