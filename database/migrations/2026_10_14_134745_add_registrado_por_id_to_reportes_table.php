<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reportes', function (Blueprint $table): void {
            $table->foreignId('registrado_por_id')
                ->nullable()
                ->after('profesional_id')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reportes', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('registrado_por_id');
        });
    }
};
