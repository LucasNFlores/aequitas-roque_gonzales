<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias_documento', function (Blueprint $table) {
            $table->id();
            // utf8mb4_unicode_ci => UNIQUE case-insensitive (DNI = dni)
            $table->string('nombre', 100)->unique();
            $table->string('descripcion', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['activo', 'nombre'], 'idx_cat_activo_nombre');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias_documento');
    }
};
