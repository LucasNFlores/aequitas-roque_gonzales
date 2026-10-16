<?php

namespace App\Actions\Dashboard;

use App\Models\EstadoProceso;
use App\Models\HistorialEstadoProceso;
use Illuminate\Support\Str;

class BuildHistoryStateOptions
{
    /** @return list<array{slug: string, label: string}> */
    public function __invoke(): array
    {
        $options = EstadoProceso::withTrashed()
            ->ordered()
            ->get(['nombre', 'slug'])
            ->mapWithKeys(fn (EstadoProceso $state): array => [$state->slug => $state->nombre]);

        HistorialEstadoProceso::query()
            ->select('estado_nuevo')
            ->distinct()
            ->orderBy('estado_nuevo')
            ->pluck('estado_nuevo')
            ->each(function (string $slug) use ($options): void {
                if (! $options->has($slug)) {
                    $options->put($slug, Str::headline($slug));
                }
            });

        return $options
            ->map(fn (string $label, string $slug): array => ['slug' => $slug, 'label' => $label])
            ->values()
            ->all();
    }
}
