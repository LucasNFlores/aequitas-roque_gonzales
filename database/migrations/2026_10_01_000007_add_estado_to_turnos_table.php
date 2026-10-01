<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HU-11 CU4/CU4.1/CU6/CU7: estado operativo programado/cancelado.
     * Reversible y preserva datos existentes.
     */
    public function up(): void
    {
        Schema::table('turnos', function (Blueprint $table) {
            $table->string('estado', 20)->default('programado')->after('tipo');
            $table->index(['profesional_id', 'fecha_hora', 'estado'], 'turnos_prof_fecha_estado_idx');
        });

        // Backfill: existentes -> programado; soft-deleted -> cancelado (conserva historial).
        DB::table('turnos')->whereNull('estado')->orWhere('estado', '')->update(['estado' => 'programado']);
        DB::table('turnos')->whereNotNull('deleted_at')->update(['estado' => 'cancelado']);
    }

    public function down(): void
    {
        Schema::table('turnos', function (Blueprint $table) {
            $table->dropIndex('turnos_prof_fecha_estado_idx');
            $table->dropColumn('estado');
        });
    }
};
