<?php

namespace App\Livewire\Turnos;

use App\Models\Turno;
use App\Models\User;
use App\Services\Turnos\TurnoScheduler;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $estadoFiltro = '';

    public string $tipoFiltro = '';

    public string $profesionalId = '';

    public string $successMessage = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Turno::class);
    }

    public function updatingEstadoFiltro(): void
    {
        $this->resetPage();
    }

    public function updatingTipoFiltro(): void
    {
        $this->resetPage();
    }

    public function updatingProfesionalId(): void
    {
        $this->resetPage();
    }

    public function cancelTurno(int $turnoId, TurnoScheduler $scheduler): void
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        $turno = Turno::query()
            ->visibleTo($user)
            ->findOrFail($turnoId);

        Gate::authorize('delete', $turno);
        $scheduler->cancel($turno);

        $this->successMessage = 'Turno cancelado. Se conserva en el historial y se liberó el horario.';
    }

    public function render(): View
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        $profesionalId = $user->hasRole('Profesional')
            ? (string) $user->id
            : $this->profesionalId;
        $estadoFiltro = in_array($this->estadoFiltro, Turno::ESTADOS, true) ? $this->estadoFiltro : '';
        $tipoFiltro = in_array($this->tipoFiltro, ['consulta_inicial', 'seguimiento', 'externo'], true) ? $this->tipoFiltro : '';

        $turnos = Turno::query()
            ->visibleTo($user)
            ->with(['cliente', 'profesional', 'proceso'])
            ->when($estadoFiltro !== '', fn (Builder $query): Builder => $query->where('estado', $estadoFiltro))
            ->when($tipoFiltro !== '', fn (Builder $query): Builder => $query->where('tipo', $tipoFiltro))
            ->when($profesionalId !== '', fn (Builder $query): Builder => $query->where('profesional_id', $profesionalId))
            ->orderBy('fecha_hora')
            ->paginate(15);

        $profesionales = $user->hasRole('Profesional')
            ? collect()
            : User::query()->role('Profesional')->orderBy('name')->get(['id', 'name']);

        return view('livewire.turnos.index', compact('turnos', 'profesionales'));
    }
}
