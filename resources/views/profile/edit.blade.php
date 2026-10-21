<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h1 class="font-editorial text-2xl font-semibold leading-tight text-ink">
                {{ __('Profile') }}
            </h1>
            <p class="text-sm leading-6 text-muted">Administra tu información personal y la seguridad de tu cuenta.</p>
        </div>
    </x-slot>

    <div class="bg-canvas py-8 sm:py-10">
        <div class="mx-auto flex max-w-5xl flex-col gap-5 px-4 sm:px-6 lg:px-8">
            <section class="rounded-panel border border-line bg-white p-5 shadow-panel sm:p-8" aria-label="Información del perfil">
                <div class="max-w-2xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </section>

            <section class="rounded-panel border border-line bg-white p-5 shadow-panel sm:p-8" aria-label="Seguridad de la cuenta">
                <div class="max-w-2xl">
                    @include('profile.partials.update-password-form')
                </div>
            </section>

            <section class="rounded-panel border border-line bg-white p-5 shadow-panel sm:p-8" aria-label="Eliminar cuenta">
                <div class="max-w-2xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
