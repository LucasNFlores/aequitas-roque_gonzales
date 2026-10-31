<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function (): void {
            $associations = [];
            $unresolvedIds = [];
            $professionalEmails = [
                'juanperez@example.com',
                'profesional.civil@example.test',
                'profesional.familia@example.test',
                'profesional.comercial@example.test',
            ];

            foreach (DB::table('turnos')->whereNull('proceso_id')->orderBy('id')->get() as $turno) {
                $processIds = collect();

                if ($turno->tipo === 'externo' && $turno->es_externo && preg_match('/^\[DEMO\] Compromiso externo ([1-4])$/D', $turno->detalle_externo ?? '', $matches) === 1) {
                    if (! DB::table('users')->where('id', $turno->profesional_id)->where('email', $professionalEmails[(int) $matches[1] - 1])->exists()) {
                        $unresolvedIds[] = $turno->id;

                        continue;
                    }

                    $dni = (string) (99010000 + (int) $matches[1]);
                    $processIds = DB::table('procesos')
                        ->join('clientes', 'clientes.id', '=', 'procesos.cliente_id')
                        ->where('procesos.cliente_id', $turno->cliente_id)
                        ->where('clientes.dni', $dni)
                        ->where('procesos.nombre', 'like', '[DEMO] Legajo '.$dni.' - %')
                        ->whereNull('procesos.deleted_at')
                        ->where(function (Builder $query) use ($turno): void {
                            $query->where('procesos.profesional_id', $turno->profesional_id)
                                ->orWhereNull('procesos.profesional_id');
                        })
                        ->limit(2)
                        ->pluck('procesos.id');
                }

                if ($processIds->count() !== 1) {
                    $unresolvedIds[] = $turno->id;

                    continue;
                }

                $associations[$turno->id] = $processIds->sole();
            }

            if ($unresolvedIds !== []) {
                throw new RuntimeException('Hay turnos sin un proceso identificable. Asocie los turnos antes de migrar: '.implode(', ', $unresolvedIds).'.');
            }

            foreach ($associations as $turnoId => $processId) {
                DB::table('turnos')->where('id', $turnoId)->update(['proceso_id' => $processId]);
            }
        });

        Schema::table('turnos', function (Blueprint $table): void {
            $table->dropForeign(['proceso_id']);
            $table->foreignId('proceso_id')->nullable(false)->change();
            $table->foreign('proceso_id')->references('id')->on('procesos')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('turnos', function (Blueprint $table): void {
            $table->dropForeign(['proceso_id']);
            $table->foreignId('proceso_id')->nullable()->change();
            $table->foreign('proceso_id')->references('id')->on('procesos')->nullOnDelete();
        });
    }
};
