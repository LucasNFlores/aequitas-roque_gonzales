<x-guest-layout>
    <div class="flex flex-col gap-3">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gold-700">Acceso seguro</p>
        <h1 class="font-editorial text-3xl font-semibold leading-tight text-aequitas-900">Confirme su contraseña</h1>
        <p class="text-sm leading-6 text-muted">
            {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-7 flex flex-col gap-5">
        @csrf

        <div>
            <x-forms.input-label for="password" :value="__('Password')" class="mb-2 font-semibold" />
            <x-forms.text-input id="password" class="block w-full" type="password" name="password" required autocomplete="current-password" />
            <x-forms.input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <x-buttons.primary-button class="w-full">
            {{ __('Confirm') }}
        </x-buttons.primary-button>
    </form>
</x-guest-layout>
