<x-guest-layout>
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[#a17b42]">Portal privado</p>
        <h1 class="mt-3 font-['Playfair_Display'] text-4xl font-bold leading-tight text-[#172538]">Bienvenido</h1>
        <p class="mt-3 text-sm leading-6 text-slate-500">Ingrese sus datos para acceder a la plataforma jurídica Aequitas.</p>
    </div>

    <x-feedback.auth-session-status class="mt-6 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-7 space-y-5">
        @csrf

        <div>
            <x-forms.input-label for="email" :value="__('Correo electrónico')" class="mb-2 text-sm font-semibold text-slate-700" />
            <x-forms.text-input id="email" class="block w-full rounded-xl border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-[#a17b42] focus:ring-[#a17b42]" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="usuario@aequitas.com" />
            <x-forms.input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <div class="mb-2 flex items-center justify-between gap-4">
                <x-forms.input-label for="password" :value="__('Contraseña')" class="text-sm font-semibold text-slate-700" />
                @if (Route::has('password.request'))
                    <a class="text-xs font-semibold text-[#8c6936] transition hover:text-[#172538] focus:outline-none focus:ring-2 focus:ring-[#a17b42] focus:ring-offset-2" href="{{ route('password.request') }}">
                        ¿Olvidó su contraseña?
                    </a>
                @endif
            </div>
            <x-forms.text-input id="password" class="block w-full rounded-xl border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-[#a17b42] focus:ring-[#a17b42]" type="password" name="password" required autocomplete="current-password" placeholder="Ingrese su contraseña" />
            <x-forms.input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <label for="remember_me" class="inline-flex cursor-pointer items-center gap-2 text-sm text-slate-600">
            <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-[#172538] shadow-sm focus:ring-[#a17b42]" name="remember">
            <span>Recordar mi sesión</span>
        </label>

        <x-buttons.primary-button class="flex w-full items-center justify-center rounded-xl bg-[#172538] px-5 py-3.5 text-sm font-semibold tracking-wide text-white shadow-lg shadow-[#172538]/20 transition hover:bg-[#243850] focus:bg-[#243850] focus:ring-[#a17b42] active:bg-[#0f1c2d]">
            Ingresar al portal
        </x-buttons.primary-button>
    </form>

    @if (Route::has('register'))
        <p class="mt-7 text-center text-sm text-slate-500">
            ¿Aún no tiene una cuenta?
            <a href="{{ route('register') }}" class="font-semibold text-[#8c6936] transition hover:text-[#172538]">Solicite acceso</a>
        </p>
    @endif
</x-guest-layout>
