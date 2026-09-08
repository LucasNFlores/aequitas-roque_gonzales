<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procesos', function (Blueprint $table): void {
            $table->string('estado')->default('pendiente')->change();
        });
    }

    public function down(): void
    {
        Schema::table('procesos', function (Blueprint $table): void {
            $table->enum('estado', [
                'pendiente',
                'admitido',
                'iniciado',
                'en_proceso',
                'finalizado',
                'en_espera',
                'rechazado',
            ])->default('pendiente')->change();
        });
    }
};
