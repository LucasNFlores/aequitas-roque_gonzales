<x-guest-layout>
    <div class="flex flex-col gap-3">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gold-700">Acceso seguro</p>
        <h1 class="font-editorial text-3xl font-semibold leading-tight text-aequitas-900">Verifique su correo electrónico</h1>
        <p class="text-sm leading-6 text-muted">
            {{ __('Gracias por registrarte. Para comenzar, verifica tu correo electrónico con el enlace que te enviamos. Si no lo recibiste, podemos enviarte otro.') }}
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mt-6 rounded-card border border-positive bg-canvas px-4 py-3 text-sm font-medium text-positive" role="status">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:items-center">
        <form method="POST" action="{{ route('verification.send') }}" class="w-full sm:w-auto">
            @csrf

            <x-buttons.primary-button class="w-full sm:w-auto">
                {{ __('Resend Verification Email') }}
            </x-buttons.primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto">
            @csrf

            <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-control px-4 text-sm font-semibold text-muted underline decoration-line underline-offset-4 transition hover:text-aequitas-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-600 sm:w-auto">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
