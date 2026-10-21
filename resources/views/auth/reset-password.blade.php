<x-guest-layout>
    <div class="flex flex-col gap-3">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gold-700">Acceso seguro</p>
        <h1 class="font-editorial text-3xl font-semibold leading-tight text-aequitas-900">Restablecer contraseña</h1>
        <p class="text-sm leading-6 text-muted">Ingrese su correo electrónico y elija una nueva contraseña para su cuenta.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="mt-7 flex flex-col gap-5">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-forms.input-label for="email" :value="__('Email')" class="mb-2 font-semibold" />
            <x-forms.text-input id="email" class="block w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-forms.input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-forms.input-label for="password" :value="__('Password')" class="mb-2 font-semibold" />
            <x-forms.text-input id="password" class="block w-full" type="password" name="password" required autocomplete="new-password" />
            <x-forms.input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-forms.input-label for="password_confirmation" :value="__('Confirm Password')" class="mb-2 font-semibold" />
            <x-forms.text-input id="password_confirmation" class="block w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-forms.input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-buttons.primary-button class="w-full">
            {{ __('Reset Password') }}
        </x-buttons.primary-button>
    </form>

    <a href="{{ route('login') }}" class="mt-6 inline-flex min-h-11 items-center text-sm font-semibold text-aequitas-900 underline decoration-gold-600 underline-offset-4 transition hover:text-aequitas-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-600">
        Volver al acceso
    </a>
</x-guest-layout>
