<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoriaDocumentoRequest;
use App\Http\Requests\UpdateCategoriaDocumentoRequest;
use App\Models\CategoriaDocumento;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class CategoriaDocumentoController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $this->authorize('viewAny', CategoriaDocumento::class);

        $filtro = $request->query('estado', 'todas'); // todas|activas|inactivas
        $q = CategoriaDocumento::withCount('documentos')->orderBy('nombre');
        if ($filtro === 'activas') $q->where('activo', true);
        if ($filtro === 'inactivas') $q->where('activo', false);

        return view('categorias.index', [
            'categorias' => $q->paginate(15)->withQueryString(),
            'filtro' => $filtro,
        ]);
    }

    public function create()
    {
        $this->authorize('create', CategoriaDocumento::class);
        return view('categorias.form', ['categoria' => new CategoriaDocumento()]);
    }

    public function store(StoreCategoriaDocumentoRequest $request)
    {
        $cat = new CategoriaDocumento($request->validated());
        $cat->created_by = $request->user()->id;
        $cat->updated_by = $request->user()->id;
        $cat->activo = true;
        $cat->save();

        return redirect()->route('categorias.index')->with('ok', "Categoría «{$cat->nombre}» creada y disponible para clasificar documentos.");
    }

    public function edit(CategoriaDocumento $categoria)
    {
        $this->authorize('update', $categoria);
        $categoria->loadCount('documentos');
        return view('categorias.form', ['categoria' => $categoria]);
    }

    public function update(UpdateCategoriaDocumentoRequest $request, CategoriaDocumento $categoria)
    {
        $this->authorize('update', $categoria);
        $categoria->fill($request->validated());
        $categoria->updated_by = $request->user()->id;
        $categoria->save(); // RN3: el id no cambia, las asociaciones se conservan

        return redirect()->route('categorias.index')->with('ok', "Categoría «{$categoria->nombre}» actualizada sin perder asociaciones.");
    }

    public function deactivate(Request $request, CategoriaDocumento $categoria)
    {
        $this->authorize('deactivate', $categoria);
        $n = $categoria->documentos()->count();
        $categoria->update(['activo' => false, 'updated_by' => $request->user()->id]);

        $msg = $n > 0
            ? "Categoría desactivada. Se conservaron {$n} documentos históricos y dejará de ofrecerse para nuevas cargas."
            : "Categoría desactivada. Ya no se ofrecerá para nuevas cargas.";
        return redirect()->route('categorias.index')->with('ok', $msg);
    }

    public function activate(Request $request, CategoriaDocumento $categoria)
    {
        $this->authorize('activate', $categoria);
        $categoria->update(['activo' => true, 'updated_by' => $request->user()->id]);
        return redirect()->route('categorias.index')->with('ok', "Categoría «{$categoria->nombre}» reactivada y disponible para nuevas cargas.");
    }

    // NOTA: no hay método destroy() — borrado físico prohibido (RN6).
}
