<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleSingleAssignmentMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_hierarchy_keeps_administrator_above_other_roles(): void
    {
        $this->assertSame(0, User::rolePriority('Administrador'));
        $this->assertSame(1, User::rolePriority('Directivo'));
        $this->assertSame(2, User::rolePriority('Coordinador'));
        $this->assertSame(3, User::rolePriority('Profesional'));
        $this->assertSame(4, User::rolePriority('Secretario'));
        $this->assertSame(5, User::rolePriority('Rol personalizado'));
    }

    public function test_migration_keeps_only_the_highest_priority_existing_role(): void
    {
        $secretario = Role::create(['name' => 'Secretario', 'guard_name' => 'web']);
        $administrador = Role::create(['name' => 'Administrador', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($secretario);

        Schema::table('model_has_roles', function (Blueprint $table): void {
            $table->dropUnique('model_has_roles_one_role_per_model_unique');
        });

        DB::table('model_has_roles')->insert([
            'role_id' => $administrador->id,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);

        $migration = require database_path('migrations/2026_10_22_123121_enforce_single_role_per_model.php');
        $migration->up();

        $this->assertSame(['Administrador'], $user->fresh()->getRoleNames()->all());

        $this->assertSame(1, DB::table('model_has_roles')->where('model_type', User::class)->where('model_id', $user->id)->count());

        $this->expectException(QueryException::class);
        $user->fresh()->assignRole($secretario);
    }
}
