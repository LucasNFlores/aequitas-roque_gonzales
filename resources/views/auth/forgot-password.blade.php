<x-guest-layout>
    <div class="flex flex-col gap-3">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gold-700">Acceso seguro</p>
        <h1 class="font-editorial text-3xl font-semibold leading-tight text-aequitas-900">¿Olvidó su contraseña?</h1>
        <p class="text-sm leading-6 text-muted">
            {{ __('Ingrese la dirección de correo asociada a su cuenta. Le enviaremos un enlace para elegir una nueva contraseña.') }}
        </p>
    </div>

    <x-feedback.auth-session-status class="mt-6 rounded-card border border-line bg-canvas px-4 py-3" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="mt-7 flex flex-col gap-5">
        @csrf

        <div>
            <x-forms.input-label for="email" :value="__('Email')" class="mb-2 font-semibold" />
            <x-forms.text-input id="email" class="block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-forms.input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <x-buttons.primary-button class="w-full">
            {{ __('Email Password Reset Link') }}
        </x-buttons.primary-button>
    </form>

    <a href="{{ route('login') }}" class="mt-6 inline-flex min-h-11 items-center text-sm font-semibold text-aequitas-900 underline decoration-gold-600 underline-offset-4 transition hover:text-aequitas-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-600">
        Volver al acceso
    </a>
</x-guest-layout>
