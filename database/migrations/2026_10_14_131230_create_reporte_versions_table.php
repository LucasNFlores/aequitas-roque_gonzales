<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reporte_versiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporte_id')->constrained('reportes')->cascadeOnDelete();
            $table->text('contenido_anterior');
            $table->date('fecha_anterior');
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('usuario_nombre');
            $table->timestamp('fecha_cambio')->index();
            $table->timestamps();
            $table->index(['reporte_id', 'fecha_cambio']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reporte_versiones');
    }
};
