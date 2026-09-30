<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $legacyServiceExists = DB::table('servicios')
            ->where('nombre', 'Consulta legal inicial')
            ->whereNull('deleted_at')
            ->exists();

        $consultoriaExists = DB::table('servicios')
            ->where('nombre', 'Consultoría')
            ->whereNull('deleted_at')
            ->exists();

        if ($legacyServiceExists && $consultoriaExists) {
            throw new RuntimeException(
                'No se puede renombrar el servicio inicial: ya existe un servicio activo llamado Consultoría.'
            );
        }

        if ($legacyServiceExists) {
            DB::table('servicios')
                ->where('nombre', 'Consulta legal inicial')
                ->whereNull('deleted_at')
                ->update([
                    'nombre' => 'Consultoría',
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $legacyServiceExists = DB::table('servicios')
            ->where('nombre', 'Consulta legal inicial')
            ->whereNull('deleted_at')
            ->exists();

        if (! $legacyServiceExists) {
            DB::table('servicios')
                ->where('nombre', 'Consultoría')
                ->whereNull('deleted_at')
                ->update([
                    'nombre' => 'Consulta legal inicial',
                    'updated_at' => now(),
                ]);
        }
    }
};
