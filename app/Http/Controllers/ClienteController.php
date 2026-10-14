<?php

namespace App\Http\Controllers;

use App\Actions\RegistrarClienteInicial;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\Cliente;
use App\Models\EstadoProceso;
use App\Models\User;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClienteController extends Controller
{
    public function __construct(private readonly RegistrarClienteInicial $registrarClienteInicial) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Cliente::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'string', 'max:50'],
        ]);
        $search = trim($filters['search'] ?? '');
        $estado = $filters['estado'] ?? '';

        $clientes = Cliente::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $like = '%'.$search.'%';

                $query->where(function (Builder $query) use ($like): void {
                    $query->where('nombre', 'like', $like)
                        ->orWhere('apellido', 'like', $like)
                        ->orWhere('dni', 'like', $like);
                });
            })
            ->when($estado !== '', fn (Builder $query): Builder => $query->whereHas(
                'procesos',
                fn (Builder $processQuery): Builder => $processQuery->where('estado', $estado),
            ))
            ->latest()
            ->orderByDesc('id')
            ->paginate(10)
            ->appends(array_filter([
                'search' => $search,
                'estado' => $estado,
            ], fn (string $value): bool => $value !== ''));

        $estados = EstadoProceso::query()->ordered()->get(['nombre', 'slug']);

        return view('clientes.index', compact('clientes', 'estados', 'search', 'estado'));
    }

    public function create(): View
    {
        $this->authorize('create', Cliente::class);

        return view('clientes.create');
    }

    public function store(StoreClienteRequest $request): RedirectResponse
    {
        $this->authorize('create', Cliente::class);

        try {
            $this->registrarClienteInicial->handle($request->validated());
        } catch (DomainException $exception) {
            return back()
                ->withInput()
                ->withErrors(['initial_configuration' => $exception->getMessage()]);
        } catch (UniqueConstraintViolationException $exception) {
            $message = strtolower($exception->getMessage());
            $field = str_contains($message, 'correo') ? 'correo' : 'dni';

            return back()
                ->withInput()
                ->withErrors([
                    $field => $field === 'correo'
                        ? 'Ya existe un cliente registrado con este correo electrónico.'
                        : 'Ya existe un cliente registrado con este DNI. Verificá el número o buscá al cliente existente.',
                ]);
        }

        return redirect()->route('clientes.index')->with('success', 'Cliente creado correctamente.');
    }

    public function show(Cliente $cliente): View
    {
        $this->authorize('view', $cliente);

        $user = request()->user();
        abort_unless($user instanceof User, 403);

        $cliente->setRelation(
            'procesos',
            $cliente->procesos()
                ->visibleTo($user)
                ->with('servicio:id,nombre')
                ->latest()
                ->get(),
        );

        return view('clientes.show', compact('cliente'));
    }

    public function edit(Cliente $cliente): View
    {
        $this->authorize('update', $cliente);

        return view('clientes.edit', compact('cliente'));
    }

    public function update(UpdateClienteRequest $request, Cliente $cliente): RedirectResponse
    {
        $this->authorize('update', $cliente);

        $cliente->update($request->validated());

        return redirect()->route('clientes.index')->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Cliente $cliente): RedirectResponse
    {
        $this->authorize('delete', $cliente);

        $cliente->delete();

        return redirect()->route('clientes.index')->with('success', 'Cliente eliminado correctamente.');
    }
}
