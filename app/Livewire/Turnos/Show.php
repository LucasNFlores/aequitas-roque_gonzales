<?php

namespace App\Livewire\Turnos;

use App\Models\Turno;
use App\Models\User;
use App\Services\Turnos\TurnoScheduler;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Show extends Component
{
    #[Locked]
    public int $turnoId;

    #[Locked]
    public bool $confirmingCancellation = false;

    public function mount(Turno $turno): void
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        $turno = Turno::query()->findOrFail($turno->id);
        Gate::authorize('view', $turno);
        $this->turnoId = $turno->id;
    }

    public function cancelTurno(TurnoScheduler $scheduler): mixed
    {
        $turno = $this->visibleTurno();
        Gate::authorize('delete', $turno);
        abort_unless($this->confirmingCancellation, 403);

        $scheduler->cancel($turno);

        return redirect()->route('turnos.index')
            ->with('success', 'Turno cancelado. Se conserva en el historial y se liberó el horario.');
    }

    public function requestCancellation(): void
    {
        Gate::authorize('delete', $this->visibleTurno());
        $this->confirmingCancellation = true;
    }

    public function render(): View
    {
        return view('livewire.turnos.show', [
            'turno' => $this->visibleTurno()->load(['cliente', 'profesional', 'proceso']),
        ]);
    }

    private function visibleTurno(): Turno
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        $turno = Turno::query()->findOrFail($this->turnoId);
        Gate::authorize('view', $turno);

        return $turno;
    }
}
