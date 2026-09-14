<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\Cliente;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClienteController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Cliente::class);

        $clientes = Cliente::latest()->paginate(10);

        return view('clientes.index', compact('clientes'));
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
            Cliente::create($request->validated());
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
