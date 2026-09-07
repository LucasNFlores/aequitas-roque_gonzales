<nav class="border-b border-gray-100 bg-white">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center gap-6">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <svg class="h-9 w-9 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16M9 7h1m-1 4h1m4-4h1m-1 4h1M9 21v-5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v5" />
                </svg>
                <span class="font-bold text-xl tracking-tight text-gray-900">SG Institucional</span>
            </a>

            <div class="flex flex-wrap items-center gap-4 text-sm font-medium">
                <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-indigo-600">{{ __('Dashboard') }}</a>

                @can('listar_usuarios')
                    <a href="{{ route('users.index') }}" class="text-gray-700 hover:text-indigo-600">{{ __('Usuarios y Roles') }}</a>
                @endcan

                @can('gestionar_servicios')
                    <a href="{{ route('servicios.index') }}" class="text-gray-700 hover:text-indigo-600">{{ __('Servicios') }}</a>
                @endcan

                <a href="{{ route('tutorial') }}" class="text-gray-700 hover:text-indigo-600">{{ __('Guía Técnica') }}</a>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-4 text-sm">
            <span class="font-medium text-gray-700">{{ Auth::user()->name }}</span>
            <a href="{{ route('profile.edit') }}" class="text-gray-600 hover:text-indigo-600">{{ __('Profile') }}</a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="font-medium text-gray-600 hover:text-red-600">{{ __('Log Out') }}</button>
            </form>
        </div>
    </div>
</nav>
