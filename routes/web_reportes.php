<?php
// Pegar dentro de routes/web.php (Laravel) o: require __DIR__.'/web_reportes.php';
// Requiere middleware 'role' que valide $user->rol (igual que web_categorias.php).

use App\Http\Controllers\ReporteDocumentoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:coordinador,administrador'])->group(function () {
    Route::get('/reportes/documentos', [ReporteDocumentoController::class, 'index'])->name('reportes.documentos.index');
    Route::get('/reportes/documentos/pdf', [ReporteDocumentoController::class, 'pdf'])->name('reportes.documentos.pdf');
    Route::get('/reportes/documentos/csv', [ReporteDocumentoController::class, 'csv'])->name('reportes.documentos.csv');
});
