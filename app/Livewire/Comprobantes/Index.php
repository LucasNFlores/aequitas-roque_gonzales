<?php

namespace App\Livewire\Comprobantes;

use App\Models\Cliente;
use App\Models\ComprobantePago;
use App\Models\Proceso;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithFileUploads, WithPagination;

    public string $dni = '';

    public ?int $clienteId = null;

    /**
     * Campo de preparación para asociar el comprobante a una causa/proceso.
     * La interfaz no lo expone hasta que ese flujo esté definido.
     */
    public ?int $procesoId = null;

    public string $fechaSubida = '';

    public string $descripcion = '';

    public ?TemporaryUploadedFile $archivo = null;

    public string $successMessage = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', ComprobantePago::class);
        $this->fechaSubida = now()->toDateString();
    }

    public function updatedDni(): void
    {
        $this->clienteId = null;
        $this->resetValidation('clienteId');
    }

    public function selectCliente(int $clienteId): void
    {
        Gate::authorize('create', ComprobantePago::class);

        $this->findVisibleCliente($clienteId);
        $this->clienteId = $clienteId;
        $this->resetValidation('clienteId');
    }

    public function clearCliente(): void
    {
        $this->clienteId = null;
        $this->dni = '';
        $this->resetValidation('clienteId');
    }

    public function saveComprobante(): void
    {
        Gate::authorize('create', ComprobantePago::class);

        $validated = $this->validate([
            'clienteId' => ['required', 'integer', Rule::exists('clientes', 'id')->whereNull('deleted_at')],
            'procesoId' => ['nullable', 'integer', Rule::exists('procesos', 'id')->whereNull('deleted_at')],
            'archivo' => ['required', 'file', 'mimes:pdf', 'max:20480'],
            'fechaSubida' => ['required', 'date', 'before_or_equal:today'],
            'descripcion' => ['nullable', 'string', 'max:5000'],
        ], [
            'clienteId.required' => 'Busca y selecciona el cliente por DNI.',
            'clienteId.exists' => 'El cliente seleccionado ya no está disponible.',
            'archivo.required' => 'Selecciona el PDF del comprobante.',
            'archivo.mimes' => 'El comprobante debe estar en formato PDF.',
            'archivo.max' => 'El PDF no puede superar los 20 MB.',
            'fechaSubida.before_or_equal' => 'La fecha de subida no puede ser futura.',
        ]);

        $cliente = $this->findVisibleCliente((int) $validated['clienteId']);
        $procesoId = $validated['procesoId'] ?? null;

        if ($procesoId !== null && ! Proceso::query()
            ->where('cliente_id', $cliente->id)
            ->whereKey($procesoId)
            ->exists()) {
            $this->addError('procesoId', 'La causa seleccionada no corresponde al cliente.');

            return;
        }

        $path = $validated['archivo']->storeAs(
            'comprobantes/'.$cliente->id,
            Str::uuid().'.pdf',
            'local',
        );

        try {
            DB::transaction(function () use ($cliente, $procesoId, $path, $validated): void {
                ComprobantePago::query()->create([
                    'cliente_id' => $cliente->id,
                    'proceso_id' => $procesoId,
                    'archivo_path' => $path,
                    'fecha_subida' => $validated['fechaSubida'],
                    'descripcion' => $validated['descripcion'] ?: null,
                ]);
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);
            report($exception);
            $this->addError('archivo', 'No pudimos guardar el comprobante. Intentalo nuevamente. Si el problema continúa, contactá al administrador.');

            return;
        }

        $this->successMessage = 'El comprobante se cargó correctamente.';
        $this->reset('clienteId', 'procesoId', 'descripcion', 'archivo');
        $this->dni = '';
        $this->fechaSubida = now()->toDateString();
        $this->resetValidation();
        $this->resetPage();
        $this->dispatch('comprobante-guardado');
    }

    public function render(): View
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        $selectedCliente = $this->clienteId === null
            ? null
            : $this->findVisibleCliente($this->clienteId);
        $dni = trim($this->dni);
        $clientes = $user->can('create', ComprobantePago::class) && mb_strlen($dni) >= 3
            ? Cliente::query()
                ->visibleTo($user)
                ->where('dni', 'like', '%'.$dni.'%')
                ->orderBy('apellido')
                ->orderBy('nombre')
                ->limit(5)
                ->get(['id', 'nombre', 'apellido', 'dni'])
            : collect();
        $comprobantes = ComprobantePago::query()
            ->with('cliente:id,nombre,apellido,dni')
            ->latest('fecha_subida')
            ->latest('id')
            ->paginate(10);

        return view('livewire.comprobantes.index', compact('clientes', 'selectedCliente', 'comprobantes'));
    }

    private function findVisibleCliente(int $clienteId): Cliente
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        return Cliente::query()
            ->visibleTo($user)
            ->findOrFail($clienteId);
    }
}
