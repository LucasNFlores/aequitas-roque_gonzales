<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
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
