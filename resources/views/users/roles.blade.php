<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">
            Asignar rol a: <span class="text-aequitas-900">{{ $user->nombre_completo }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="rounded-panel border border-line bg-white p-5 shadow-panel sm:p-6">

                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-positive px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                <form action="{{ route('users.roles.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <label class="mb-4 block text-lg font-bold text-muted" for="role">Selecciona un rol para este usuario:</label>
                    <select class="mb-6 block w-full rounded-control border-line shadow-sm focus:border-gold-600 focus:ring-gold-600" name="role" id="role">
                        <option value="">Sin rol</option>
                        @foreach($roles as $role)
                            @if(auth()->user()->hasRole('Administrador') || $role->name !== 'Administrador')
                                <option value="{{ $role->name }}" @selected(old('role', $user->roles->first()?->name ?? '') === $role->name)>
                                    {{ $role->name }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                    @error('role') <p class="mb-4 text-sm text-red-600">{{ $message }}</p> @enderror

                    <div class="border-t border-line pt-4 flex space-x-3">
                        <x-buttons.primary-button>
                            Guardar Cambios
                        </x-buttons.primary-button>

                        <a href="{{ route('users.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-line rounded-control font-semibold text-xs text-muted uppercase tracking-widest shadow-sm hover:bg-canvas focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                            Cancelar
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
