<form action="{{ $action }}" method="POST" class="space-y-6">
    @csrf
    @if(isset($method))
        @method($method)
    @endif

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Nombre *</label>
            <input id="name" type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
            @error('name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
            <input id="email" type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
            @error('email') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Contraseña {{ isset($user) ? '(vacío = mantener)' : '(vacío = 1234)' }}</label>
            <input id="password" type="password" name="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
            @error('password') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="dni" class="block text-sm font-medium text-gray-700">DNI</label>
            <input id="dni" type="text" name="dni" value="{{ old('dni', $user->dni ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
            @error('dni') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="telefono" class="block text-sm font-medium text-gray-700">Teléfono</label>
            <input id="telefono" type="text" name="telefono" value="{{ old('telefono', $user->telefono ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
            @error('telefono') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="domicilio" class="block text-sm font-medium text-gray-700">Domicilio</label>
            <input id="domicilio" type="text" name="domicilio" value="{{ old('domicilio', $user->domicilio ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
            @error('domicilio') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="fecha_nacimiento" class="block text-sm font-medium text-gray-700">Fecha nacimiento</label>
            <input id="fecha_nacimiento" type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', isset($user?->fecha_nacimiento) ? $user->fecha_nacimiento->format('Y-m-d') : '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
            @error('fecha_nacimiento') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="fecha_ingreso" class="block text-sm font-medium text-gray-700">Fecha ingreso</label>
            <input id="fecha_ingreso" type="date" name="fecha_ingreso" value="{{ old('fecha_ingreso', isset($user?->fecha_ingreso) ? $user->fecha_ingreso->format('Y-m-d') : '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
            @error('fecha_ingreso') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Roles (desde BD)</label>
        <div class="mt-2 space-y-2">
            @foreach(\Spatie\Permission\Models\Role::orderBy('name')->get() as $role)
                @if(auth()->user()->hasRole('Administrador') || $role->name !== 'Administrador')
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="roles[]" value="{{ $role->name }}" {{ in_array($role->name, old('roles', isset($user) ? $user->roles->pluck('name')->all() : [])) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                    <span class="text-sm text-gray-700">{{ $role->name }}</span>
                </label>
                @endif
            @endforeach
        </div>
        @error('roles') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    @can('asignar_especialidad_servicio')
    <div>
        <label class="block text-sm font-medium text-gray-700">Servicios / Especialidades</label>
        <div class="mt-2 max-h-40 overflow-y-auto rounded-md border border-gray-200 p-3">
            @forelse(\App\Models\Servicio::orderBy('nombre')->get() as $servicio)
                <label class="flex items-center gap-2 py-1">
                    <input type="checkbox" name="servicios[]" value="{{ $servicio->id }}" {{ in_array($servicio->id, old('servicios', isset($user) ? $user->servicios->pluck('id')->all() : [])) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                    <span class="text-sm text-gray-700">{{ $servicio->nombre }}</span>
                </label>
            @empty
                <span class="text-sm italic text-gray-500">No hay servicios.</span>
            @endforelse
        </div>
        @error('servicios') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    @endcan

    <div class="border-t border-gray-200 pt-4 flex space-x-3">
        <button type="submit" class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            {{ $submitLabel }}
        </button>
        <a href="{{ route('users.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">Cancelar</a>
    </div>
</form>
