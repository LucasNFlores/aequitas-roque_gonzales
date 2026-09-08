<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_estados_proceso', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('proceso_id')->constrained('procesos')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('estado_anterior')->nullable();
            $table->string('estado_nuevo');
            $table->text('motivo')->nullable();
            $table->timestamp('fecha_cambio')->useCurrent();
            $table->timestamps();

            $table->index(['proceso_id', 'fecha_cambio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_estados_proceso');
    }
};
