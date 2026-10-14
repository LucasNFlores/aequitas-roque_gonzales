<?php
// Pegar dentro de routes/web.php (Laravel). Requiere middleware 'role' que valide $user->rol.
// Registrar policy en App\Providers\AuthServiceProvider:
//   CategoriaDocumento::class => CategoriaDocumentoPolicy::class,

use App\Http\Controllers\CategoriaDocumentoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:coordinador,administrador'])->group(function () {
    Route::get('/categorias', [CategoriaDocumentoController::class, 'index'])->name('categorias.index');
    Route::get('/categorias/crear', [CategoriaDocumentoController::class, 'create'])->name('categorias.create');
    Route::post('/categorias', [CategoriaDocumentoController::class, 'store'])->name('categorias.store');
    Route::get('/categorias/{categoria}/editar', [CategoriaDocumentoController::class, 'edit'])->name('categorias.edit');
    Route::put('/categorias/{categoria}', [CategoriaDocumentoController::class, 'update'])->name('categorias.update');
    Route::patch('/categorias/{categoria}/activar', [CategoriaDocumentoController::class, 'activate'])->name('categorias.activate');
    Route::patch('/categorias/{categoria}/desactivar', [CategoriaDocumentoController::class, 'deactivate'])->name('categorias.deactivate');
    // Sin Route::delete -> borrado físico bloqueado
});
