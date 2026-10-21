<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light" data-theme-preference="light">
    <head>
        <script>
            (() => {
                let preference = 'light';

                try {
                    const savedPreference = localStorage.getItem('aequitas-theme');

                    if (['light', 'dark', 'system'].includes(savedPreference)) {
                        preference = savedPreference;
                    }
                } catch {}

                const systemThemeQuery = window.matchMedia('(prefers-color-scheme: dark)');
                const resolveTheme = (selectedPreference) => selectedPreference === 'system'
                    ? (systemThemeQuery.matches ? 'dark' : 'light')
                    : selectedPreference;
                const applyThemePreference = (selectedPreference) => {
                    preference = selectedPreference;
                    const resolvedTheme = resolveTheme(preference);

                    document.documentElement.dataset.theme = resolvedTheme;
                    document.documentElement.dataset.themePreference = preference;
                    window.dispatchEvent(new CustomEvent('aequitas:theme-changed', { detail: { theme: resolvedTheme } }));
                };
                const syncThemePreferenceControl = () => {
                    const themePreferenceSelect = document.querySelector('[data-theme-preference]');

                    if (themePreferenceSelect) {
                        themePreferenceSelect.value = preference;
                    }
                };

                applyThemePreference(preference);
                document.addEventListener('DOMContentLoaded', syncThemePreferenceControl, { once: true });
                document.addEventListener('livewire:navigated', syncThemePreferenceControl);
                window.addEventListener('pageshow', syncThemePreferenceControl);
                new MutationObserver(syncThemePreferenceControl).observe(document.documentElement, {
                    childList: true,
                    subtree: true,
                    attributes: true,
                    attributeFilter: ['data-theme-preference'],
                });
                document.addEventListener('change', (event) => {
                    if (!(event.target instanceof HTMLSelectElement) || !event.target.matches('[data-theme-preference]')) {
                        return;
                    }

                    try {
                        localStorage.setItem('aequitas-theme', event.target.value);
                    } catch {}

                    document.cookie = `aequitas-theme=${event.target.value}; Path=/; Max-Age=31536000; SameSite=Lax`;

                    applyThemePreference(event.target.value);
                });
                systemThemeQuery.addEventListener('change', () => {
                    if (preference === 'system') {
                        applyThemePreference('system');
                    }
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
            <script>
                (() => {
                    const syncThemePreferenceControl = () => {
                        const themePreferenceSelect = document.querySelector('[data-theme-preference]');

                        if (themePreferenceSelect) {
                            themePreferenceSelect.value = document.documentElement.dataset.themePreference;
                        }
                    };

                    syncThemePreferenceControl();
                    window.addEventListener('load', syncThemePreferenceControl, { once: true });
                })();
            </script>

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
