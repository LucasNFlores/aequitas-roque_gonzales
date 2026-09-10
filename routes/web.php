<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// -----------------------------------------------------------------------------
// RUTAS PÚBLICAS
// -----------------------------------------------------------------------------
Route::get('/', function () {
    return view('welcome');
});

// -----------------------------------------------------------------------------
// RUTAS BÁSICAS DE AUTENTICACIÓN (Cualquiera que inicie sesión)
// -----------------------------------------------------------------------------
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Rutas del perfil nativas de Laravel Breeze
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// -----------------------------------------------------------------------------
// MÓDULO DE USUARIOS Y ROLES (Protegido por Spatie) - CU25, CU26, CU27, CU34
// -----------------------------------------------------------------------------

Route::middleware(['auth', 'permission:listar_usuarios'])->group(function () {
    Route::get('/usuarios', [UserController::class, 'index'])->name('users.index');
});

Route::middleware(['auth', 'permission:agregar_usuarios'])->group(function () {
    Route::get('/usuarios/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/usuarios', [UserController::class, 'store'])->name('users.store');
});

Route::middleware(['auth', 'permission:modificar_usuarios'])->group(function () {
    Route::get('/usuarios/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/usuarios/{user}', [UserController::class, 'update'])->name('users.update');
    Route::patch('/usuarios/{user}', [UserController::class, 'update']);
});

Route::middleware(['auth', 'permission:eliminar_usuarios'])->group(function () {
    Route::delete('/usuarios/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/usuarios/{user}', [UserController::class, 'show'])->name('users.show')->middleware('permission:listar_usuarios');
});

Route::middleware(['auth', 'permission:editar_roles'])->group(function () {
    Route::get('/usuarios/{user}/roles', [UserController::class, 'editRoles'])->name('users.roles.edit');
    Route::put('/usuarios/{user}/roles', [UserController::class, 'updateRoles'])->name('users.roles.update');
});

Route::middleware(['auth', 'permission:asignar_especialidad_servicio'])->group(function () {
    Route::put('/usuarios/{user}/servicios', [UserController::class, 'updateServicios'])->name('users.servicios.update');
});

Route::middleware(['auth', 'permission:gestionar_servicios'])->group(function () {
    Route::get('/servicios', fn () => view('servicios.index'))->name('servicios.index');
    Route::resource('servicios-viejo', ServicioController::class)
        ->except(['show'])
        ->names('servicios-viejo')
        ->parameters(['servicios-viejo' => 'servicio']);
});

Route::middleware(['auth', 'permission:listar_filtrar_procesos'])->group(function () {
    Route::get('/procesos', fn () => view('procesos.index'))->name('procesos.index');
});

Route::middleware(['auth', 'permission:gestionar_estados_proceso'])->group(function () {
    Route::get('/estados-proceso', fn () => view('estados-proceso.index'))->name('estados-proceso.index');
});

Route::middleware(['auth'])->group(function () {
    Route::resource('clientes', ClienteController::class);
});

Route::get('/tutorial', function () {
    return view('tutorial.index');
})->middleware(['auth'])->name('tutorial');

// -----------------------------------------------------------------------------

require __DIR__.'/auth.php';
