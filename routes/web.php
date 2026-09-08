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
// MÓDULO DE USUARIOS Y ROLES (Protegido por Spatie)
// -----------------------------------------------------------------------------

Route::middleware(['auth', 'permission:listar_usuarios'])->group(function () {
    Route::get('/usuarios', [UserController::class, 'index'])->name('users.index');
});

Route::middleware(['auth', 'permission:editar_roles'])->group(function () {
    Route::get('/usuarios/{user}/roles', [UserController::class, 'editRoles'])->name('users.roles.edit');
    Route::put('/usuarios/{user}/roles', [UserController::class, 'updateRoles'])->name('users.roles.update');
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
