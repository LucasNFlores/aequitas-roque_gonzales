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
        Schema::create('documento_versiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documento_id')->constrained('documentos')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('archivo_path');
            $table->foreignId('categoria_id')->nullable()->constrained('categorias_documento')->nullOnDelete();
            $table->string('categoria_nombre')->nullable();
            $table->string('tipo_documento');
            $table->string('nombre');
            $table->timestamp('fecha_reemplazo');
            $table->timestamps();

            $table->index(['documento_id', 'fecha_reemplazo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documento_versiones');
    }
};
