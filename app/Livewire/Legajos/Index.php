<?php

namespace App\Livewire\Legajos;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function mount(): void
    {
        Gate::authorize('viewAnyLegajo', Cliente::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        Gate::authorize('viewAnyLegajo', Cliente::class);

        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        $searchTerms = preg_split('/\s+/', trim($this->search), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $clientes = Cliente::query()
            ->whereHas('procesos', fn (Builder $query): Builder => $query->visibleTo($user))
            ->when($searchTerms !== [], function (Builder $query) use ($searchTerms): void {
                $query->where(function (Builder $clientQuery) use ($searchTerms): void {
                    foreach ($searchTerms as $term) {
                        $clientQuery->where(function (Builder $termQuery) use ($term): void {
                            $termQuery->where('nombre', 'like', '%'.$term.'%')
                                ->orWhere('apellido', 'like', '%'.$term.'%')
                                ->orWhere('dni', 'like', '%'.$term.'%');
                        });
                    }
                });
            })
            ->withCount(['procesos as procesos_visibles_count' => fn (Builder $query): Builder => $query->visibleTo($user)])
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->paginate(10);

        return view('livewire.legajos.index', compact('clientes'));
    }
}
