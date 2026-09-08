<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estados_proceso', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->boolean('activo')->default(true)->index();
            $table->unsignedInteger('posicion')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['activo', 'posicion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estados_proceso');
    }
};
