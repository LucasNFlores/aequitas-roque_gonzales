<section class="space-y-6">
    <header>
        <h2 class="text-lg font-semibold text-ink">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm text-muted">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <x-buttons.danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Delete Account') }}</x-buttons.danger-button>

    <x-overlays.modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-semibold text-ink">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="mt-1 text-sm text-muted">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="mt-6">
                <x-forms.input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                <x-forms.text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full sm:max-w-md"
                    placeholder="{{ __('Password') }}"
                />

                <x-forms.input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-buttons.secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-buttons.secondary-button>

                <x-buttons.danger-button>
                    {{ __('Delete Account') }}
                </x-buttons.danger-button>
            </div>
        </form>
    </x-overlays.modal>
</section>
