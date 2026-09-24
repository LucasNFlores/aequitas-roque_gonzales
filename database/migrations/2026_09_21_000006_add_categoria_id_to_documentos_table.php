<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            // Nullable para no romper documentos históricos; nuevas cargas la exigen por validación.
            $table->foreignId('categoria_id')
                ->nullable()
                ->after('proceso_id')
                ->constrained('categorias_documento')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('categoria_id');
        });
    }
};
