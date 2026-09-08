<nav x-data="{ open: false }" class="relative md:w-64 md:shrink-0">
    <div class="fixed inset-x-0 top-0 z-30 flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 md:hidden">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 text-gray-900">
            <x-branding.application-logo class="h-8 w-8 fill-current text-indigo-600" />
            <span class="text-sm font-semibold">{{ config('app.name', 'Laravel') }}</span>
        </a>

        <button
            type="button"
            @click="open = ! open"
            class="inline-flex items-center justify-center rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            :aria-expanded="open.toString()"
            aria-controls="app-sidebar"
        >
            <span class="sr-only">{{ __('Open navigation menu') }}</span>
            <svg x-show="! open" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg x-show="open" x-cloak class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div
        x-show="open"
        x-transition.opacity
        @click="open = false"
        class="fixed inset-0 z-40 bg-gray-900/40 md:hidden"
        aria-hidden="true"
    ></div>

    <aside
        id="app-sidebar"
        class="fixed inset-y-0 start-0 z-50 flex h-screen w-64 shrink-0 -translate-x-full flex-col border-r border-gray-200 bg-white shadow-xl transition-transform duration-200 ease-in-out md:sticky md:inset-auto md:top-0 md:z-auto md:translate-x-0 md:shadow-none"
        :class="{ 'translate-x-0': open, '-translate-x-full': ! open }"
    >
        <div class="flex h-20 shrink-0 items-center border-b border-gray-100 px-6">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 text-gray-900">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm">
                    <x-branding.application-logo class="h-6 w-6 fill-current" />
                </span>
                <span class="min-w-0">
                    <span class="block truncate text-sm font-bold">{{ config('app.name', 'Laravel') }}</span>
                    <span class="block text-xs text-gray-500">{{ __('Gestión jurídica') }}</span>
                </span>
            </a>
        </div>

        <div class="flex flex-1 flex-col gap-8 overflow-y-auto px-4 py-6">
            <div>
                <p class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">{{ __('Navegación') }}</p>

                <div class="mt-3 flex flex-col gap-2">
                    <x-navigation.nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 13h8V3H3v10Zm10 8h8V11h-8v10ZM3 21h8v-6H3v6Zm10-12h8V3h-8v6Z" />
                        </svg>
                        <span>{{ __('Dashboard') }}</span>
                    </x-navigation.nav-link>

                    @can('listar_usuarios')
                        <x-navigation.nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m6-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm8-3v6m3-3h-6" />
                            </svg>
                            <span>{{ __('Usuarios y Roles') }}</span>
                        </x-navigation.nav-link>
                    @endcan

                    @can('gestionar_servicios')
                        <x-navigation.nav-link :href="route('servicios.index')" :active="request()->routeIs('servicios.*')">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6V4m0 16v-2m6-6h2M4 12H2m14.24-4.24 1.42-1.42M6.34 17.66l-1.42 1.42m0-14.14 1.42 1.42m10.32 11.3 1.42 1.42M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" />
                            </svg>
                            <span>{{ __('Servicios') }}</span>
                        </x-navigation.nav-link>
                    @endcan
                    @can('listar_filtrar_clientes')
                        <x-navigation.nav-link :href="route('clientes.index')" :active="request()->routeIs('clientes.*')">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-6 8a6 6 0 0 1 12 0v1H6v-1Z" />
                            </svg>
                            <span>{{ __('Clientes') }}</span>
                        </x-navigation.nav-link>
                    @endcan

                    @can('listar_filtrar_procesos')
                        <x-navigation.nav-link :href="route('procesos.index')" :active="request()->routeIs('procesos.*')">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5v-16ZM4 5.5v16M8 7h8m-8 4h8m-8 4h5" />
                            </svg>
                            <span>{{ __('Procesos') }}</span>
                        </x-navigation.nav-link>
                    @endcan

                    @can('gestionar_estados_proceso')
                        <x-navigation.nav-link :href="route('estados-proceso.index')" :active="request()->routeIs('estados-proceso.*')">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 6h14M5 12h14M5 18h14M8 6v0m4 6v0m-2 6v0" />
                            </svg>
                            <span>{{ __('Estados de proceso') }}</span>
                        </x-navigation.nav-link>
                    @endcan

                    <x-navigation.nav-link :href="route('tutorial')" :active="request()->routeIs('tutorial')">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5v-16ZM4 5.5v16M8 7h8m-8 4h8" />
                        </svg>
                        <span>{{ __('Guía Técnica') }}</span>
                    </x-navigation.nav-link>
                </div>
            </div>

            <div class="mt-auto border-t border-gray-100 pt-5">
                <div class="flex flex-col gap-2">
                    <x-navigation.nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm7.4-3.5a7.42 7.42 0 0 0-.1-1.2l2-1.55-2-3.46-2.37.95a7.2 7.2 0 0 0-2.08-1.2L14.5 3h-5l-.35 2.54a7.2 7.2 0 0 0-2.08 1.2L4.7 5.8l-2 3.46 2 1.55a7.42 7.42 0 0 0-.1 1.2c0 .4.04.8.1 1.2l-2 1.55 2 3.46 2.37-.95a7.2 7.2 0 0 0 2.08 1.2L9.5 21h5l.35-2.54a7.2 7.2 0 0 0 2.08-1.2l2.37.95 2-3.46-2-1.55c.06-.4.1-.8.1-1.2Z" />
                        </svg>
                        <span>{{ __('Perfil') }}</span>
                    </x-navigation.nav-link>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-start text-sm font-medium text-gray-600 transition hover:bg-red-50 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 17l5-5-5-5m5 5H3m9-9V3h9v18h-9v-2" />
                            </svg>
                            <span>{{ __('Cerrar sesión') }}</span>
                        </button>
                    </form>
                </div>

                <div class="mt-5 flex items-center gap-3 border-t border-gray-100 pt-5">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </span>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-gray-900">{{ Auth::user()->name }}</p>
                        <p class="truncate text-xs text-gray-500">{{ Auth::user()->email }}</p>
                    </div>
                </div>
            </div>
        </div>
    </aside>
</nav>
