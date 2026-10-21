<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex min-h-11 items-center justify-center rounded-control border border-transparent bg-negative px-4 py-2 text-sm font-semibold text-white shadow-panel transition duration-150 ease-in-out hover:bg-negative-hover focus:outline-none focus:ring-2 focus:ring-negative focus:ring-offset-2 active:bg-negative-active disabled:cursor-not-allowed disabled:opacity-50']) }}>
    {{ $slot }}
</button>
