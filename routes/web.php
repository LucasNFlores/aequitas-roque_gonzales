<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ComprobantePagoController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\LegajoController;
use App\Http\Controllers\ProcesoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\UserController;
use App\Models\Proceso;
use App\Models\Turno;
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

Route::middleware(['auth', 'permission:consultar_legajos'])->group(function () {
    Route::get('/legajos', fn () => view('legajos.index'))->name('legajos.index');
    Route::get('/clientes/{cliente}/legajo', [LegajoController::class, 'show'])->name('legajos.show');
});

// HU-23: proceso adicional para un cliente existente (Secretario y Administrador vía Policy create).
Route::middleware(['auth'])->group(function () {
    Route::get('/clientes/{cliente}/procesos/create', [ProcesoController::class, 'createForCliente'])->name('clientes.procesos.create');
    Route::post('/clientes/{cliente}/procesos', [ProcesoController::class, 'storeForCliente'])->name('clientes.procesos.store');
});

Route::middleware(['auth', 'permission:ver_comprobantes_pago'])->group(function () {
    Route::get('/comprobantes', fn () => view('comprobantes.index'))->name('comprobantes.index');
});

Route::middleware(['auth', 'permission:gestionar_estados_proceso'])->group(function () {
    Route::get('/estados-proceso', fn () => view('estados-proceso.index'))->name('estados-proceso.index');
});

Route::middleware(['auth', 'permission:gestionar_categorias_documentos'])->group(function () {
    Route::get('/categorias', fn () => view('categorias.index'))->name('categorias.index');
});

// -----------------------------------------------------------------------------
// MÓDULO DOCUMENTAL — ruta anidada por proceso (CU9-12, CU19, CU37)
// -----------------------------------------------------------------------------
Route::middleware(['auth', 'permission:visualizar_documentacion'])->group(function () {
    Route::get('/procesos/{proceso}/documentos', fn (Proceso $proceso) => view('documentos.index', compact('proceso')))->name('procesos.documentos.index');
});

Route::middleware(['auth'])->group(function () {
    Route::post('/procesos/{proceso}/documentos', [DocumentoController::class, 'store'])
        ->middleware('permission:cargar_documentacion')
        ->name('procesos.documentos.store');
    Route::put('/procesos/{proceso}/documentos/{documento}', [DocumentoController::class, 'update'])
        ->middleware('permission:reemplazar_documentacion')
        ->name('procesos.documentos.update');
    Route::delete('/procesos/{proceso}/documentos/{documento}', [DocumentoController::class, 'destroy'])
        ->middleware('permission:eliminar_documentacion')
        ->name('procesos.documentos.destroy');
    Route::get('/procesos/{proceso}/documentos/{documento}/descargar', [DocumentoController::class, 'download'])
        ->name('procesos.documentos.download');
    Route::get('/procesos/{proceso}/documentos/{documento}/versiones/{version}/descargar', [DocumentoController::class, 'downloadVersion'])
        ->name('procesos.documentos.versiones.download');
    Route::get('/procesos/{proceso}/documentos/{documento}/versiones/{version}', [DocumentoController::class, 'showVersion'])
        ->name('procesos.documentos.versiones.show');
    Route::get('/procesos/{proceso}/documentos/{documento}', [DocumentoController::class, 'show'])
        ->name('procesos.documentos.show');
});

Route::middleware(['auth'])->scopeBindings()->group(function () {
    Route::post('/procesos/{proceso}/reportes', [ReporteController::class, 'store'])
        ->middleware('permission:registrar_reportes')
        ->name('procesos.reportes.store');
    Route::put('/procesos/{proceso}/reportes/{reporte}', [ReporteController::class, 'update'])
        ->middleware('permission:editar_reportes_propios')
        ->name('procesos.reportes.update');
    Route::delete('/procesos/{proceso}/reportes/{reporte}', [ReporteController::class, 'destroy'])
        ->middleware('permission:eliminar_reportes_propios')
        ->name('procesos.reportes.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::resource('clientes', ClienteController::class);
});

Route::middleware(['auth', 'permission:ver_comprobantes_pago'])->group(function () {
    Route::get('/comprobantes/{comprobantePagoId}', [ComprobantePagoController::class, 'show'])
        ->whereNumber('comprobantePagoId')
        ->name('comprobantes.show');
});

// -----------------------------------------------------------------------------
// MÓDULO TURNOS Y AGENDA — HU-11 (CU4, CU4.1, CU6, CU7, CU8)
// Permisos: Secretario/Admin escriben; Profesional/Coordinador solo agenda.
// -----------------------------------------------------------------------------
Route::middleware(['auth'])->group(function () {
    Route::get('/agenda', fn () => view('turnos.agenda'))
        ->middleware('permission:ver_agenda_profesional')
        ->name('turnos.agenda');

    Route::get('/turnos', fn () => view('turnos.index'))
        ->middleware('permission:ver_agenda_profesional')
        ->name('turnos.index');
    Route::get('/turnos/create', fn () => view('turnos.create'))
        ->middleware('permission:agendar_turnos_internos|agendar_turnos_seguimiento')
        ->name('turnos.create');
    Route::get('/turnos/externos/create', fn () => view('turnos.externos.create'))
        ->middleware('permission:agendar_turnos_externos')
        ->name('turnos.externos.create');
    Route::get('/turnos/externos/{turno}/edit', fn (Turno $turno) => view('turnos.externos.edit', compact('turno')))
        ->middleware('permission:modificar_turnos_externos')
        ->name('turnos.externos.edit');
    Route::get('/turnos/{turno}', fn (Turno $turno) => view('turnos.show', compact('turno')))
        ->middleware('permission:ver_agenda_profesional')
        ->name('turnos.show');
    Route::get('/turnos/{turno}/edit', fn (Turno $turno) => view('turnos.edit', compact('turno')))
        ->middleware('permission:modificar_turnos')
        ->name('turnos.edit');
});

Route::get('/tutorial', function () {
    return view('tutorial.index');
})->middleware(['auth'])->name('tutorial');

// -----------------------------------------------------------------------------

require __DIR__.'/auth.php';
