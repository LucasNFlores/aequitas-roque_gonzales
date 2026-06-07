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
        Schema::create('turnos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('cliente_id')->constrained()->onDelete('cascade');
        $table->foreignId('profesional_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('proceso_id')->nullable()->constrained()->onDelete('set null');
        $table->dateTime('fecha_hora');
        $table->boolean('es_externo')->default(false);
        $table->text('detalle_externo')->nullable();
        $table->enum('tipo', ['consulta_inicial', 'seguimiento', 'externo']);
        $table->timestamps();
        $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turnos');
    }
};
