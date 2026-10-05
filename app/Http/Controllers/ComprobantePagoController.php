<?php

namespace App\Http\Controllers;

use App\Models\ComprobantePago;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ComprobantePagoController extends Controller
{
    public function show(string $comprobantePagoId): StreamedResponse
    {
        $comprobantePago = ComprobantePago::query()->findOrFail($comprobantePagoId);
        Gate::authorize('view', $comprobantePago);
        abort_unless(Storage::disk('local')->exists($comprobantePago->archivo_path), 404, 'Archivo no encontrado.');

        return Storage::disk('local')->response(
            $comprobantePago->archivo_path,
            'comprobante-'.$comprobantePago->id.'.pdf',
            ['Content-Type' => 'application/pdf'],
            'inline',
        );
    }
}
