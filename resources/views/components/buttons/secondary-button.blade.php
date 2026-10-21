<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex min-h-11 items-center justify-center rounded-control border border-line bg-white px-4 py-2 text-sm font-semibold text-aequitas-900 shadow-panel transition duration-150 ease-in-out hover:bg-aequitas-50 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50']) }}>
    {{ $slot }}
</button>
