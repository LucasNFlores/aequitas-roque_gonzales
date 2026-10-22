<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
    <head>
        <script>
            (() => {
                const systemThemeQuery = window.matchMedia('(prefers-color-scheme: dark)');
                let theme = 'light';

                try {
                    const savedPreference = localStorage.getItem('aequitas-theme');

                    if (savedPreference === 'light' || savedPreference === 'dark') {
                        theme = savedPreference;
                    } else if (savedPreference === 'system') {
                        theme = systemThemeQuery.matches ? 'dark' : 'light';
                    }
                } catch {}

                const syncThemeToggle = () => {
                    const themeToggle = document.querySelector('[data-theme-toggle]');

                    if (!themeToggle) {
                        return;
                    }

                    const isDarkTheme = document.documentElement.dataset.theme === 'dark';
                    const targetTheme = isDarkTheme ? 'light' : 'dark';
                    const targetThemeLabel = targetTheme === 'dark' ? 'Oscuro' : 'Claro';

                    themeToggle.setAttribute('aria-label', `Cambiar a modo ${targetThemeLabel.toLowerCase()}`);
                    themeToggle.title = `Cambiar a modo ${targetThemeLabel.toLowerCase()}`;
                    themeToggle.querySelector('[data-theme-toggle-label]').textContent = `Modo ${targetThemeLabel.toLowerCase()}`;
                    themeToggle.querySelector('[data-theme-icon="dark"]').hidden = targetTheme !== 'dark';
                    themeToggle.querySelector('[data-theme-icon="light"]').hidden = targetTheme !== 'light';
                };
                const applyTheme = (nextTheme) => {
                    theme = nextTheme;
                    document.documentElement.dataset.theme = theme;
                    syncThemeToggle();
                    window.dispatchEvent(new CustomEvent('aequitas:theme-changed', { detail: { theme } }));
                };

                applyTheme(theme);
                document.addEventListener('DOMContentLoaded', syncThemeToggle, { once: true });
                document.addEventListener('livewire:navigated', syncThemeToggle);
                window.addEventListener('pageshow', syncThemeToggle);
                document.addEventListener('click', (event) => {
                    const themeToggle = event.target.closest('[data-theme-toggle]');

                    if (!themeToggle) {
                        return;
                    }

                    try {
                        localStorage.setItem('aequitas-theme', theme === 'dark' ? 'light' : 'dark');
                    } catch {}

                    applyTheme(theme === 'dark' ? 'light' : 'dark');
                });
            })();
        </script>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&family=playfair-display:600,700&display=swap" rel="stylesheet" />

        @livewireScriptConfig

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans text-ink antialiased">
        <a href="#main-content" class="sr-only z-50 rounded-control bg-white px-4 py-3 font-semibold text-aequitas-900 shadow-floating focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:ring-2 focus:ring-gold-600 focus:ring-offset-2">
            {{ __('Saltar al contenido') }}
        </a>
        <div class="min-h-screen bg-canvas md:flex">
            @include('layouts.navigation')
            <div class="flex min-h-screen min-w-0 flex-1 flex-col pt-16 md:pt-0">
                <!-- Page Heading -->
                @isset($header)
                    <header class="border-b border-line bg-white shadow-panel">
                        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <main id="main-content" class="flex-1">
                    {{ $slot }}
                </main>
            </div>
        </div>

    </body>
</html>
