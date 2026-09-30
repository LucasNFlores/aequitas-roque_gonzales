<?php

use Illuminate\Database\Migrations\Migration;
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
        $duplicateEmailsExist = DB::table('clientes')
            ->select('correo')
            ->groupBy('correo')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($duplicateEmailsExist) {
            throw new RuntimeException(
                'No se puede agregar el índice único de correo: hay clientes con correos duplicados, incluidos registros dados de baja.'
            );
        }

        Schema::table('clientes', function (Blueprint $table) {
            $table->unique('correo', 'clientes_correo_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropUnique('clientes_correo_unique');
        });
    }
};
