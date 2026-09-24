<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notificaciones', function (Blueprint $table): void {
            $table->string('canal', 20)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('notificaciones')
            ->where('canal', 'interno')
            ->update(['canal' => 'email']);

        Schema::table('notificaciones', function (Blueprint $table): void {
            $table->enum('canal', ['email', 'whatsapp'])->change();
        });
    }
};
