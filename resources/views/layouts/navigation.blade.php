<nav x-data="{ open: false }" class="relative md:w-64 md:shrink-0">
    <div class="fixed inset-x-0 top-0 z-30 flex h-16 items-center justify-between border-b border-line bg-white px-4 shadow-panel md:hidden">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 text-aequitas-900">
            <x-branding.application-logo class="h-8 w-8 fill-current text-aequitas-900" />
            <span class="text-sm font-semibold">{{ config('app.name', 'Laravel') }}</span>
        </a>

        <button
            type="button"
            @click="open = ! open"
            class="inline-flex h-11 w-11 items-center justify-center rounded-control text-muted transition hover:bg-aequitas-50 hover:text-aequitas-900 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2"
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
        class="fixed inset-0 z-40 bg-[var(--aequitas-overlay)] md:hidden"
        aria-hidden="true"
    ></div>

    <aside
        id="app-sidebar"
        class="fixed inset-y-0 start-0 z-50 flex h-screen w-64 shrink-0 -translate-x-full flex-col border-r border-line bg-white shadow-floating transition-transform duration-200 ease-in-out md:sticky md:inset-auto md:top-0 md:z-auto md:translate-x-0 md:shadow-none"
        :class="{ 'translate-x-0': open, '-translate-x-full': ! open }"
    >
        <div class="flex h-20 shrink-0 items-center border-b border-line px-6">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 text-aequitas-900">
                <span class="flex h-10 w-10 items-center justify-center rounded-control bg-aequitas-900 text-gold-300 shadow-panel">
                    <x-branding.application-logo class="h-6 w-6 fill-current" />
                </span>
                <span class="min-w-0">
                    <span class="block truncate font-editorial text-base font-semibold">{{ config('app.name', 'Laravel') }}</span>
                    <span class="block text-xs text-muted">{{ __('Gestión jurídica') }}</span>
                </span>
            </a>
        </div>

        <div class="flex flex-1 flex-col gap-8 overflow-y-auto px-4 py-6">
            <div>
                <div class="flex flex-col gap-3">
                    <div class="space-y-1" x-data="{ sectionOpen: @js(request()->routeIs('dashboard')) }">
                        <button type="button" @click="sectionOpen = ! sectionOpen" aria-expanded="false" x-bind:aria-expanded="sectionOpen.toString()" aria-controls="sidebar-group-home" class="flex min-h-11 w-full items-center justify-between gap-3 rounded-control px-3 text-[10px] font-semibold uppercase tracking-wide text-muted transition hover:bg-aequitas-50 hover:text-aequitas-900 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2">
                            <span>{{ __('Inicio') }}</span>
                            <svg class="h-4 w-4 shrink-0 transition-transform" :class="{ 'rotate-180': sectionOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 9 6 6 6-6" />
                            </svg>
                        </button>
                        <div id="sidebar-group-home" x-show="sectionOpen" x-cloak x-transition class="flex flex-col gap-1">
                            <x-navigation.nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 13h8V3H3v10Zm10 8h8V11h-8v10ZM3 21h8v-6H3v6Zm10-12h8V3h-8v6Z" />
                                </svg>
                                <span>{{ __('Dashboard') }}</span>
                            </x-navigation.nav-link>
                        </div>
                    </div>

                    @if (Auth::user()->canAny(['listar_filtrar_clientes', 'consultar_legajos', 'listar_filtrar_procesos', 'ver_agenda_profesional']))
                        <div class="space-y-1" x-data="{ sectionOpen: @js(request()->routeIs('clientes.*', 'legajos.*', 'procesos.*', 'turnos.*')) }">
                            <button type="button" @click="sectionOpen = ! sectionOpen" aria-expanded="false" x-bind:aria-expanded="sectionOpen.toString()" aria-controls="sidebar-group-legal" class="flex min-h-11 w-full items-center justify-between gap-3 rounded-control px-3 text-[10px] font-semibold uppercase tracking-wide text-muted transition hover:bg-aequitas-50 hover:text-aequitas-900 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2">
                                <span>{{ __('Gestión jurídica') }}</span>
                                <svg class="h-4 w-4 shrink-0 transition-transform" :class="{ 'rotate-180': sectionOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 9 6 6 6-6" />
                                </svg>
                            </button>
                            <div id="sidebar-group-legal" x-show="sectionOpen" x-cloak x-transition class="flex flex-col gap-1">
                                @can('listar_filtrar_clientes')
                                    <x-navigation.nav-link :href="route('clientes.index')" :active="request()->routeIs('clientes.*')">
                                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-6 8a6 6 0 0 1 12 0v1H6v-1Z" />
                                        </svg>
                                        <span>{{ __('Clientes') }}</span>
                                    </x-navigation.nav-link>
                                @endcan

                                @can('consultar_legajos')
                                    <x-navigation.nav-link :href="route('legajos.index')" :active="request()->routeIs('legajos.*')">
                                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 3.75h9.5L19 8.25v12A1.75 1.75 0 0 1 17.25 22h-12A1.75 1.75 0 0 1 3.5 20.25V5.5A1.75 1.75 0 0 1 5.25 3.75ZM14 4v5h5M7.5 13h7m-7 3.5h9" />
                                        </svg>
                                        <span>{{ __('Legajos') }}</span>
                                    </x-navigation.nav-link>
                                @endcan

                                @can('listar_filtrar_procesos')
                                    <x-navigation.nav-link :href="route('procesos.index')" :active="request()->routeIs('procesos.*')">
                                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 7.5A2.25 2.25 0 0 1 6 5.25h3.75L12 7.5h6A2.25 2.25 0 0 1 20.25 9.75v.75H6.5a2.75 2.75 0 0 0-2.66 2.05L2.25 18.75V7.5Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m2.25 18.75 1.59-5.95A2.75 2.75 0 0 1 6.5 10.75h15.25l-1.58 5.93a3.75 3.75 0 0 1-3.63 2.82H4.5a2.25 2.25 0 0 1-2.25-2.25v-.5Zm7.5-4.5h7.5m-7.5 3h5" />
                                        </svg>
                                        <span>{{ __('Procesos') }}</span>
                                    </x-navigation.nav-link>
                                @endcan

                                @can('ver_agenda_profesional')
                                    <x-navigation.nav-link :href="route('turnos.index')" :active="request()->routeIs('turnos.*')">
                                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 2v3m8-3v3M3.5 9.5h17M5 4.5h14a1.5 1.5 0 0 1 1.5 1.5v13A1.5 1.5 0 0 1 19 20.5H5A1.5 1.5 0 0 1 3.5 19V6A1.5 1.5 0 0 1 5 4.5Z" />
                                        </svg>
                                        <span>{{ __('Turnos') }}</span>
                                    </x-navigation.nav-link>
                                @endcan
                            </div>
                        </div>
                    @endif

                    @if (Auth::user()->canAny(['listar_usuarios', 'asignar_especialidad_servicio', 'gestionar_servicios', 'gestionar_estados_proceso', 'gestionar_categorias_documentos']))
                        <div class="space-y-1" x-data="{ sectionOpen: @js(request()->routeIs('users.*', 'profesionales.servicios.*', 'servicios.*', 'estados-proceso.*', 'categorias.*')) }">
                            <button type="button" @click="sectionOpen = ! sectionOpen" aria-expanded="false" x-bind:aria-expanded="sectionOpen.toString()" aria-controls="sidebar-group-administration" class="flex min-h-11 w-full items-center justify-between gap-3 rounded-control px-3 text-[10px] font-semibold uppercase tracking-wide text-muted transition hover:bg-aequitas-50 hover:text-aequitas-900 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2">
                                <span>{{ __('Administración') }}</span>
                                <svg class="h-4 w-4 shrink-0 transition-transform" :class="{ 'rotate-180': sectionOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 9 6 6 6-6" />
                                </svg>
                            </button>
                            <div id="sidebar-group-administration" x-show="sectionOpen" x-cloak x-transition class="flex flex-col gap-1">
                                @can('listar_usuarios')
                                    <x-navigation.nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m6-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm8-3v6m3-3h-6" />
                                        </svg>
                                        <span>{{ __('Usuarios y Roles') }}</span>
                                    </x-navigation.nav-link>
                                @endcan

                                @can('asignar_especialidad_servicio')
                                    <x-navigation.nav-link :href="route('profesionales.servicios.index')" :active="request()->routeIs('profesionales.servicios.*')">
                                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m6-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm12 2h-6m3-3v6" />
                                        </svg>
                                        <span>{{ __('Servicios por profesional') }}</span>
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

                                @can('gestionar_estados_proceso')
                                    <x-navigation.nav-link :href="route('estados-proceso.index')" :active="request()->routeIs('estados-proceso.*')">
                                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 6h14M5 12h14M5 18h14M8 6v0m4 6v0m-2 6v0" />
                                        </svg>
                                        <span>{{ __('Estados de proceso') }}</span>
                                    </x-navigation.nav-link>
                                @endcan

                                @can('gestionar_categorias_documentos')
                                    <x-navigation.nav-link :href="route('categorias.index')" :active="request()->routeIs('categorias.*')">
                                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 6H7l1.5 2h8L18 6h2.25A1.5 1.5 0 0 1 21.75 7.5V18a1.5 1.5 0 0 1-1.5 1.5H3.75A1.5 1.5 0 0 1 2.25 18V7.5A1.5 1.5 0 0 1 3.75 6Z" />
                                        </svg>
                                        <span>{{ __('Categorías') }}</span>
                                    </x-navigation.nav-link>
                                @endcan
                            </div>
                        </div>
                    @endif

                    @if (Auth::user()->canAny(['gestionar_categorias_documentos', 'ver_comprobantes_pago']))
                        <div class="space-y-1" x-data="{ sectionOpen: @js(request()->routeIs('comprobantes.*', 'reportes.documentos.*')) }">
                            <button type="button" @click="sectionOpen = ! sectionOpen" aria-expanded="false" x-bind:aria-expanded="sectionOpen.toString()" aria-controls="sidebar-group-reports" class="flex min-h-11 w-full items-center justify-between gap-3 rounded-control px-3 text-[10px] font-semibold uppercase tracking-wide text-muted transition hover:bg-aequitas-50 hover:text-aequitas-900 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2">
                                <span>{{ __('Informes') }}</span>
                                <svg class="h-4 w-4 shrink-0 transition-transform" :class="{ 'rotate-180': sectionOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 9 6 6 6-6" />
                                </svg>
                            </button>
                            <div id="sidebar-group-reports" x-show="sectionOpen" x-cloak x-transition class="flex flex-col gap-1">
                                @can('ver_comprobantes_pago')
                                    <x-navigation.nav-link :href="route('comprobantes.index')" :active="request()->routeIs('comprobantes.*')">
                                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 3.75h7.25L19 8.5v11.75A1.75 1.75 0 0 1 17.25 22h-10.5A1.75 1.75 0 0 1 5 20.25v-14.5A1.75 1.75 0 0 1 6.75 4H7Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14 3.75V9h5M8.5 13h7m-7 3.5h5" />
                                        </svg>
                                        <span>{{ __('Comprobantes') }}</span>
                                    </x-navigation.nav-link>
                                @endcan
                                @can('gestionar_categorias_documentos')
                                    <x-navigation.nav-link :href="route('reportes.documentos.index')" :active="request()->routeIs('reportes.documentos.*')">
                                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 3.75h7.25L19 8.5v11.75A1.75 1.75 0 0 1 17.25 22h-10.5A1.75 1.75 0 0 1 5 20.25v-14.5A1.75 1.75 0 0 1 6.75 4H7Zm7.25 0V9H19M8.5 13h7m-7 3.5h7" />
                                        </svg>
                                        <span>{{ __('Informe documental') }}</span>
                                    </x-navigation.nav-link>
                                @endcan
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-auto border-t border-line pt-4" x-data="{ accountMenuOpen: false }" @keydown.escape.window="accountMenuOpen = false">
                <div class="px-1" @click.outside="accountMenuOpen = false">
                    <button
                        type="button"
                        @click="accountMenuOpen = ! accountMenuOpen"
                        aria-expanded="false"
                        :aria-expanded="accountMenuOpen.toString()"
                        aria-controls="sidebar-account-actions"
                        class="flex min-h-14 w-full items-center gap-3 rounded-control px-3 py-2 text-start transition hover:bg-aequitas-50 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2"
                    >
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-aequitas-50 text-sm font-semibold text-aequitas-900">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-semibold text-ink">{{ Auth::user()->name }}</span>
                            <span class="block truncate text-xs text-muted">{{ Auth::user()->getRoleNames()->implode(', ') }}</span>
                        </span>
                        <svg class="h-4 w-4 shrink-0 text-muted transition-transform" :class="{ 'rotate-180': accountMenuOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 9 6 6 6-6" />
                        </svg>
                    </button>

                    <div id="sidebar-account-actions" x-show="accountMenuOpen" x-cloak x-transition class="mt-2 flex flex-col gap-1">
                        <div class="px-2">
                            <button
                                type="button"
                                data-theme-toggle
                                class="flex min-h-11 w-full items-center gap-3 rounded-control px-3 py-2.5 text-start text-sm font-medium text-muted transition hover:bg-aequitas-50 hover:text-aequitas-900 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2"
                            >
                                <svg data-theme-icon="dark" class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20.25 15.25A8.25 8.25 0 0 1 8.75 3.75 8.25 8.25 0 1 0 20.25 15.25Z" />
                                </svg>
                                <svg data-theme-icon="light" class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" hidden>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v2m0 14v2M5.64 5.64l1.42 1.42m9.88 9.88 1.42 1.42M3 12h2m14 0h2M5.64 18.36l-1.42-1.42m9.88-9.88 1.42-1.42M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" />
                                </svg>
                                <span data-theme-toggle-label>{{ __('Modo oscuro') }}</span>
                            </button>
                        </div>
                        <x-navigation.nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm7.4-3.5a7.42 7.42 0 0 0-.1-1.2l2-1.55-2-3.46-2.37.95a7.2 7.2 0 0 0-2.08-1.2L14.5 3h-5l-.35 2.54a7.2 7.2 0 0 0-2.08 1.2L4.7 5.8l-2 3.46 2 1.55a7.42 7.42 0 0 0-.1 1.2c0 .4.04.8.1 1.2l-2 1.55 2 3.46 2.37-.95a7.2 7.2 0 0 0 2.08 1.2L9.5 21h5l.35-2.54a7.2 7.2 0 0 0 2.08-1.2l2.37.95 2-3.46-2-1.55c.06-.4.1-.8.1-1.2Z" />
                            </svg>
                            <span>{{ __('Perfil') }}</span>
                        </x-navigation.nav-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex min-h-[44px] w-full items-center gap-3 rounded-control px-3 py-2.5 text-start text-sm font-medium text-muted transition hover:bg-red-50 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-negative">
                                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 17l5-5-5-5m5 5H3m9-9V3h9v18h-9v-2" />
                                </svg>
                                <span>{{ __('Cerrar sesión') }}</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </aside>
</nav>
