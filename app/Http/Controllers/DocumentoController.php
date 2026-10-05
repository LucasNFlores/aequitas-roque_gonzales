<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentoRequest;
use App\Http\Requests\UpdateDocumentoRequest;
use App\Models\CategoriaDocumento;
use App\Models\Documento;
use App\Models\DocumentoVersion;
use App\Models\Proceso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentoController extends Controller
{
    public function store(StoreDocumentoRequest $request, Proceso $proceso): RedirectResponse
    {
        Gate::authorize('create', Documento::class);
        abort_unless((int) $request->input('proceso_id') === $proceso->id, 422, 'El proceso no coincide con la ruta.');

        $validated = $request->validated();

        $categoria = CategoriaDocumento::query()->findOrFail($validated['categoria_id']);
        abort_unless($categoria->activo, 422, 'La categoría seleccionada no está disponible para nuevas cargas.');

        $tipoDocumento = $validated['tipo_documento'] ?? $categoria->nombre;
        $nombre = $validated['nombre'];
        $file = $request->file('archivo');

        $path = $file->storeAs('documentos/'.$proceso->id, Str::uuid().'.pdf', 'local');

        Documento::query()->create([
            'proceso_id' => $proceso->id,
            'categoria_id' => $categoria->id,
            'archivo_path' => $path,
            'tipo_documento' => $tipoDocumento,
            'nombre' => $nombre,
        ]);

        return redirect()->route('procesos.documentos.index', $proceso)->with('success', 'Documento cargado correctamente.');
    }

    public function update(UpdateDocumentoRequest $request, Proceso $proceso, Documento $documento): RedirectResponse
    {
        abort_unless($documento->proceso_id === $proceso->id, 404);
        Gate::authorize('update', $documento);

        $validated = $request->validated();

        $data = [];

        if (array_key_exists('proceso_id', $validated)) {
            abort_unless((int) $validated['proceso_id'] === $proceso->id, 422, 'El proceso no coincide.');
        }

        if (array_key_exists('categoria_id', $validated)) {
            $categoria = CategoriaDocumento::query()->findOrFail($validated['categoria_id']);
            abort_unless($categoria->activo, 422, 'La categoría seleccionada no está disponible.');
            $data['categoria_id'] = $categoria->id;
            $data['tipo_documento'] = $validated['tipo_documento'] ?? $categoria->nombre;
        } elseif (array_key_exists('tipo_documento', $validated)) {
            $data['tipo_documento'] = $validated['tipo_documento'];
        }

        if (array_key_exists('nombre', $validated)) {
            $data['nombre'] = $validated['nombre'];
        }

        $replacementPath = null;

        if ($request->hasFile('archivo')) {
            $replacementPath = $request->file('archivo')->storeAs('documentos/'.$proceso->id, Str::uuid().'.pdf', 'local');
            $data['archivo_path'] = $replacementPath;
        }

        $documento->loadMissing('categoria');

        try {
            DB::transaction(function () use ($documento, $data, $replacementPath): void {
                if ($replacementPath !== null) {
                    DocumentoVersion::query()->create([
                        'documento_id' => $documento->id,
                        'usuario_id' => auth()->id(),
                        'archivo_path' => $documento->archivo_path,
                        'categoria_id' => $documento->categoria_id,
                        'categoria_nombre' => $documento->categoria?->nombre,
                        'tipo_documento' => $documento->tipo_documento,
                        'nombre' => $documento->nombre,
                        'fecha_reemplazo' => now(),
                    ]);
                }

                if ($data !== []) {
                    $documento->update($data);
                }
            });
        } catch (\Throwable $exception) {
            if ($replacementPath !== null) {
                Storage::disk('local')->delete($replacementPath);
            }

            throw $exception;
        }

        return redirect()->route('procesos.documentos.index', $proceso)->with('success', 'Documento actualizado.');
    }

    public function destroy(Proceso $proceso, Documento $documento): RedirectResponse
    {
        abort_unless($documento->proceso_id === $proceso->id, 404);
        Gate::authorize('delete', $documento);

        $documento->delete();

        return redirect()->route('procesos.documentos.index', $proceso)->with('success', 'Documento eliminado. Se conserva auditoría y archivo físico.');
    }

    public function download(Proceso $proceso, Documento $documento): StreamedResponse
    {
        abort_unless($documento->proceso_id === $proceso->id, 404);
        Gate::authorize('download', $documento);

        abort_unless(Storage::disk('local')->exists($documento->archivo_path), 404, 'Archivo no encontrado.');

        return Storage::disk('local')->download($documento->archivo_path, 'documento-'.$documento->id.'.pdf');
    }

    public function show(Proceso $proceso, Documento $documento): StreamedResponse
    {
        abort_unless($documento->proceso_id === $proceso->id, 404);
        Gate::authorize('view', $documento);
        abort_unless(Storage::disk('local')->exists($documento->archivo_path), 404, 'Archivo no encontrado.');

        return Storage::disk('local')->response(
            $documento->archivo_path,
            'documento-'.$documento->id.'.pdf',
            ['Content-Type' => 'application/pdf'],
            'inline',
        );
    }

    public function showVersion(Proceso $proceso, Documento $documento, DocumentoVersion $version): StreamedResponse
    {
        $this->authorizeVersion($proceso, $documento, $version, 'view');

        return Storage::disk('local')->response(
            $version->archivo_path,
            'documento-'.$documento->id.'-version-'.$version->id.'.pdf',
            ['Content-Type' => 'application/pdf'],
            'inline',
        );
    }

    public function downloadVersion(Proceso $proceso, Documento $documento, DocumentoVersion $version): StreamedResponse
    {
        $this->authorizeVersion($proceso, $documento, $version, 'download');

        return Storage::disk('local')->download(
            $version->archivo_path,
            'documento-'.$documento->id.'-version-'.$version->id.'.pdf',
        );
    }

    private function authorizeVersion(
        Proceso $proceso,
        Documento $documento,
        DocumentoVersion $version,
        string $ability,
    ): void {
        abort_unless($documento->proceso_id === $proceso->id, 404);
        abort_unless($version->documento_id === $documento->id, 404);
        Gate::authorize($ability, $documento);
        abort_unless(Storage::disk('local')->exists($version->archivo_path), 404, 'Archivo no encontrado.');
    }
}
