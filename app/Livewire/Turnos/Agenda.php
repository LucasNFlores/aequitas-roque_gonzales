<?php

namespace App\Livewire\Turnos;

use App\Models\Turno;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class Agenda extends Component
{
    use WithPagination;

    private const MAX_RANGE_DAYS = 90;

    public string $profesionalId = '';

    public string $desde = '';

    public string $hasta = '';

    #[Locked]
    public string $profesionalAplicado = '';

    #[Locked]
    public string $desdeAplicado = '';

    #[Locked]
    public string $hastaAplicado = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Turno::class);

        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        if ($user->hasRole('Profesional')) {
            $this->profesionalId = (string) $user->id;
        }

        $this->desde = now()->toDateString();
        $this->hasta = now()->addDays(30)->toDateString();
        $this->desdeAplicado = $this->desde;
        $this->hastaAplicado = $this->hasta;
        $this->profesionalAplicado = $user->hasRole('Profesional') ? (string) $user->id : '';
    }

    public function applyFilters(): void
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        $validated = $this->validate([
            'profesionalId' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->whereNull('deleted_at'),
            ],
            'desde' => ['required', 'date'],
            'hasta' => ['required', 'date', 'after_or_equal:desde'],
        ], [
            'profesionalId.exists' => 'El profesional seleccionado no existe o está inactivo.',
            'desde.required' => 'Indica una fecha inicial para acotar la agenda.',
            'hasta.required' => 'Indica una fecha final para acotar la agenda.',
            'hasta.after_or_equal' => 'La fecha final debe ser igual o posterior a la inicial.',
        ]);

        $rangeDays = Carbon::parse($validated['desde'])->startOfDay()
            ->diffInDays(Carbon::parse($validated['hasta'])->startOfDay());

        if ($rangeDays >= self::MAX_RANGE_DAYS) {
            $this->addError('hasta', 'El rango de agenda no puede superar los 90 días.');

            return;
        }

        $profesionalId = (string) ($validated['profesionalId'] ?? '');

        if (! $user->hasRole('Profesional') && $profesionalId !== '') {
            $isActiveProfessional = User::query()
                ->role('Profesional')
                ->whereNull('deleted_at')
                ->whereKey($profesionalId)
                ->exists();

            if (! $isActiveProfessional) {
                $this->addError('profesionalId', 'El usuario seleccionado no es un profesional activo.');

                return;
            }
        }

        $this->profesionalAplicado = $user->hasRole('Profesional')
            ? (string) $user->id
            : $profesionalId;
        $this->desdeAplicado = (string) $validated['desde'];
        $this->hastaAplicado = (string) $validated['hasta'];
        $this->resetPage();
    }

    public function render(): View
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        $turnos = Turno::query()
            ->visibleTo($user)
            ->with(['cliente', 'profesional', 'proceso'])
            ->when($this->profesionalAplicado !== '', fn (Builder $query): Builder => $query->where('profesional_id', $this->profesionalAplicado))
            ->when($this->desdeAplicado !== '', fn (Builder $query): Builder => $query->where('fecha_hora', '>=', Carbon::parse($this->desdeAplicado)->startOfDay()))
            ->when($this->hastaAplicado !== '', fn (Builder $query): Builder => $query->where('fecha_hora', '<=', Carbon::parse($this->hastaAplicado)->endOfDay()))
            ->orderBy('fecha_hora')
            ->paginate(15);

        $profesionales = $user->hasRole('Profesional')
            ? collect()
            : User::query()->role('Profesional')->orderBy('name')->get(['id', 'name']);

        return view('livewire.turnos.agenda', compact('turnos', 'profesionales'));
    }
}
