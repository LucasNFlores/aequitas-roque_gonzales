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
        $tableName = config('permission.table_names.model_has_roles', 'model_has_roles');
        $rolesTable = config('permission.table_names.roles', 'roles');
        $rolePivotKey = config('permission.column_names.role_pivot_key') ?: 'role_id';
        $modelMorphKey = config('permission.column_names.model_morph_key') ?: 'model_id';
        $hierarchy = [
            'Administrador',
            'Directivo',
            'Coordinador',
            'Profesional',
            'Secretario',
        ];

        $assignments = DB::table($tableName.' as assignments')
            ->join($rolesTable, $rolesTable.'.id', '=', 'assignments.'.$rolePivotKey)
            ->orderBy('assignments.model_type')
            ->orderBy('assignments.'.$modelMorphKey)
            ->get([
                'assignments.model_type',
                'assignments.'.$modelMorphKey.' as model_id',
                'assignments.'.$rolePivotKey.' as role_id',
                $rolesTable.'.name as role_name',
            ]);

        foreach ($assignments->groupBy(fn (object $assignment): string => $assignment->model_type.':'.$assignment->model_id) as $group) {
            if ($group->count() < 2) {
                continue;
            }

            $ordered = $group->sortBy(function (object $assignment) use ($hierarchy): array {
                $priority = array_search($assignment->role_name, $hierarchy, true);

                return [
                    $priority === false ? count($hierarchy) : $priority,
                    mb_strtolower($assignment->role_name),
                    (int) $assignment->role_id,
                ];
            })->values();
            $keep = $ordered->first();

            DB::table($tableName)
                ->where('model_type', $keep->model_type)
                ->where($modelMorphKey, $keep->model_id)
                ->where($rolePivotKey, '<>', $keep->role_id)
                ->delete();
        }

        Schema::table($tableName, function (Blueprint $table) use ($modelMorphKey): void {
            $table->unique([$modelMorphKey, 'model_type'], 'model_has_roles_one_role_per_model_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = config('permission.table_names.model_has_roles', 'model_has_roles');

        Schema::table($tableName, function (Blueprint $table): void {
            $table->dropUnique('model_has_roles_one_role_per_model_unique');
        });
    }
};
