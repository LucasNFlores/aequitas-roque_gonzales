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
        Schema::create('procesos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained()->onDelete('cascade');
            $table->foreignId('profesional_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('servicio_id')->constrained()->onDelete('cascade');
            $table->foreignId('coordinador_id')->constrained('users')->onDelete('cascade');
            $table->string('nombre');
            $table->text('descripcion');
            $table->date('fecha_inicio');
            $table->enum('tipo', ['Civil', 'Comercial', 'Familia']);
            $table->enum('estado', ['pendiente', 'admitido', 'iniciado', 'en_proceso', 'finalizado', 'en_espera', 'rechazado'])->default('pendiente');
            $table->text('motivo_rechazo')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procesos');
    }
};
