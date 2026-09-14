<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Aequitas · Gestión jurídica</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&family=playfair-display:600,700&display=swap" rel="stylesheet" />

        @livewireScriptConfig
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#101a2a] font-sans text-slate-900 antialiased">
        <main class="relative isolate flex min-h-screen items-center justify-center overflow-hidden px-4 py-6 sm:px-8 lg:px-12">
            <img src="{{ asset('images/auth/estudio-juridico-login.png') }}" alt="" class="absolute inset-0 h-full w-full scale-105 object-cover blur-[2px]" aria-hidden="true">
            <div class="absolute inset-0 bg-[#07111e]/55"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_42%,transparent_10%,rgba(3,9,17,0.48)_100%)]"></div>

            <section class="relative grid w-full max-w-6xl overflow-hidden rounded-[2.75rem] border-[10px] border-white bg-white shadow-[0_30px_80px_rgba(0,0,0,0.45)] lg:min-h-[660px] lg:grid-cols-[0.92fr_1.08fr]">
                <div class="flex min-h-[580px] flex-col justify-between bg-white px-7 py-8 sm:px-12 sm:py-10 lg:px-14 lg:py-12">
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-3 self-start text-[#172538]" aria-label="Ir al inicio">
                        <span class="grid h-10 w-10 place-items-center rounded-full border border-[#b28a4b]/50 bg-[#172538] text-[#e4c687] shadow-sm">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                <path stroke-linecap="round" d="M12 3v18M5 7h14M7 7l-3 7h6L7 7Zm10 0-3 7h6l-3-7ZM4 14h6a3 3 0 0 1-6 0Zm10 0h6a3 3 0 0 1-6 0Z" />
                            </svg>
                        </span>
                        <span class="leading-tight">
                            <span class="block font-['Playfair_Display'] text-xl font-bold tracking-[0.02em]">Aequitas</span>
                            <span class="block text-[0.58rem] font-semibold uppercase tracking-[0.22em] text-[#9a763e]">Gestión jurídica</span>
                        </span>
                    </a>

                    <div class="my-10 w-full max-w-sm">
                        {{ $slot }}
                    </div>

                    <p class="text-xs leading-5 text-slate-500">Acceso seguro para clientes y profesionales del estudio.</p>
                </div>

                <aside class="relative hidden overflow-hidden rounded-[2rem] lg:block" aria-label="Aequitas, gestión jurídica">
                    <img src="{{ asset('images/auth/estudio-juridico-login.png') }}" alt="Despacho jurídico con una balanza de justicia frente a un edificio institucional" class="absolute inset-0 h-full w-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#09121f]/80 via-[#09121f]/15 to-[#07101b]/40"></div>
                    <div class="absolute inset-x-10 top-10 flex items-center justify-between text-[0.65rem] font-medium uppercase tracking-[0.18em] text-white/75">
                        <span>Confianza · Estrategia · Justicia</span>
                        <span class="h-px w-14 bg-[#d8b776]/80"></span>
                    </div>
                    <div class="absolute inset-x-10 bottom-10 max-w-sm text-white">
                        <span class="mb-4 block h-px w-12 bg-[#d8b776]"></span>
                        <p class="font-['Playfair_Display'] text-3xl font-semibold leading-tight">Defendemos sus intereses con rigor y cercanía.</p>
                        <p class="mt-4 max-w-xs text-sm leading-6 text-slate-200/85">Gestión legal clara, responsable y a la altura de cada asunto.</p>
                    </div>
                </aside>
            </section>
        </main>
    </body>
</html>
