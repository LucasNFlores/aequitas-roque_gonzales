<form action="{{ $action }}" method="POST" class="space-y-6">
    @csrf
    @if(isset($method))
        @method($method)
    @endif

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <label for="name" class="block text-sm font-medium text-muted">Nombre *</label>
            <input id="name" type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required class="mt-1 block w-full rounded-control border-line shadow-sm focus:border-gold-600 focus:ring-gold-600" />
            @error('name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="email" class="block text-sm font-medium text-muted">Email *</label>
            <input id="email" type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required class="mt-1 block w-full rounded-control border-line shadow-sm focus:border-gold-600 focus:ring-gold-600" />
            @error('email') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password" class="block text-sm font-medium text-muted">Contraseña {{ isset($user) ? '(vacío = mantener)' : '(vacío = 1234)' }}</label>
            <input id="password" type="password" name="password" class="mt-1 block w-full rounded-control border-line shadow-sm focus:border-gold-600 focus:ring-gold-600" />
            @error('password') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="dni" class="block text-sm font-medium text-muted">DNI</label>
            <input id="dni" type="text" name="dni" value="{{ old('dni', $user->dni ?? '') }}" class="mt-1 block w-full rounded-control border-line shadow-sm focus:border-gold-600 focus:ring-gold-600" />
            @error('dni') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="telefono" class="block text-sm font-medium text-muted">Teléfono</label>
            <input id="telefono" type="text" name="telefono" value="{{ old('telefono', $user->telefono ?? '') }}" class="mt-1 block w-full rounded-control border-line shadow-sm focus:border-gold-600 focus:ring-gold-600" />
            @error('telefono') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="domicilio" class="block text-sm font-medium text-muted">Domicilio</label>
            <input id="domicilio" type="text" name="domicilio" value="{{ old('domicilio', $user->domicilio ?? '') }}" class="mt-1 block w-full rounded-control border-line shadow-sm focus:border-gold-600 focus:ring-gold-600" />
            @error('domicilio') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="fecha_nacimiento" class="block text-sm font-medium text-muted">Fecha nacimiento</label>
            <input id="fecha_nacimiento" type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', isset($user?->fecha_nacimiento) ? $user->fecha_nacimiento->format('Y-m-d') : '') }}" class="mt-1 block w-full rounded-control border-line shadow-sm focus:border-gold-600 focus:ring-gold-600" />
            @error('fecha_nacimiento') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="fecha_ingreso" class="block text-sm font-medium text-muted">Fecha ingreso</label>
            <input id="fecha_ingreso" type="date" name="fecha_ingreso" value="{{ old('fecha_ingreso', isset($user?->fecha_ingreso) ? $user->fecha_ingreso->format('Y-m-d') : '') }}" class="mt-1 block w-full rounded-control border-line shadow-sm focus:border-gold-600 focus:ring-gold-600" />
            @error('fecha_ingreso') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-muted">Roles (desde BD)</label>
        <div class="mt-2 space-y-2">
            @foreach(\Spatie\Permission\Models\Role::orderBy('name')->get() as $role)
                @if(auth()->user()->hasRole('Administrador') || $role->name !== 'Administrador')
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="roles[]" value="{{ $role->name }}" {{ in_array($role->name, old('roles', isset($user) ? $user->roles->pluck('name')->all() : [])) ? 'checked' : '' }} class="rounded border-line text-aequitas-900 shadow-sm focus:ring-gold-600" />
                    <span class="text-sm text-muted">{{ $role->name }}</span>
                </label>
                @endif
            @endforeach
        </div>
        @error('roles') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    @can('asignar_especialidad_servicio')
    <div>
        <label class="block text-sm font-medium text-muted">Servicios / Especialidades</label>
        <div class="mt-2 max-h-40 overflow-y-auto rounded-control border border-line p-3">
            @forelse(\App\Models\Servicio::orderBy('nombre')->get() as $servicio)
                <label class="flex items-center gap-2 py-1">
                    <input type="checkbox" name="servicios[]" value="{{ $servicio->id }}" {{ in_array($servicio->id, old('servicios', isset($user) ? $user->servicios->pluck('id')->all() : [])) ? 'checked' : '' }} class="rounded border-line text-aequitas-900 shadow-sm focus:ring-gold-600" />
                    <span class="text-sm text-muted">{{ $servicio->nombre }}</span>
                </label>
            @empty
                <span class="text-sm italic text-muted">No hay servicios.</span>
            @endforelse
        </div>
        @error('servicios') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    @endcan

    <div class="border-t border-line pt-4 flex space-x-3">
        <button type="submit" class="inline-flex items-center rounded-control border border-transparent bg-aequitas-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white shadow-sm transition hover:bg-aequitas-800 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2">
            {{ $submitLabel }}
        </button>
        <a href="{{ route('users.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-line rounded-control font-semibold text-xs text-muted uppercase tracking-widest shadow-sm hover:bg-canvas">Cancelar</a>
    </div>
</form>
