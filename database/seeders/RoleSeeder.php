<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * @var array<string, list<string>>
     */
    private const PERMISSIONS_BY_ROLE = [
        'Secretario' => [
            'registrar_clientes', 'modificar_clientes', 'eliminar_clientes',
            'agendar_turnos_internos', 'agendar_turnos_seguimiento', 'agendar_turnos_externos',
            'modificar_turnos', 'modificar_turnos_externos', 'eliminar_turnos',
            'ver_agenda_profesional', 'cargar_documentacion', 'reemplazar_documentacion',
            'eliminar_documentacion', 'visualizar_documentacion', 'registrar_comprobantes_pago',
            'ver_comprobantes_pago', 'asignar_profesionales', 'reasignar_profesionales',
            'listar_filtrar_clientes', 'listar_filtrar_procesos', 'consultar_historial_estados',
            'cambiar_perfil', 'consultar_notificaciones',
        ],
        'Profesional' => [
            'ver_agenda_profesional', 'visualizar_documentacion', 'descargar_documentacion',
            'consultar_legajos', 'actualizar_estados_proceso', 'registrar_reportes',
            'editar_reportes_propios', 'eliminar_reportes_propios', 'consultar_reportes',
            'listar_filtrar_procesos', 'consultar_historial_estados', 'cambiar_perfil',
        ],
        'Coordinador' => [
            'ver_agenda_profesional', 'visualizar_documentacion', 'descargar_documentacion',
            'ver_comprobantes_pago', 'admitir_procesos', 'registrar_motivo_rechazo',
            'asignar_profesionales', 'reasignar_profesionales', 'consultar_legajos',
            'actualizar_estados_proceso', 'consultar_reportes', 'listar_filtrar_clientes',
            'listar_filtrar_procesos', 'consultar_historial_estados', 'cambiar_perfil',
            'asignar_especialidad_servicio', 'consultar_notificaciones',
            'gestionar_estados_proceso', 'gestionar_categorias_documentos',
        ],
        'Directivo' => [
            'listar_usuarios', 'agregar_usuarios', 'modificar_usuarios', 'eliminar_usuarios',
            'editar_roles', 'ver_comprobantes_pago', 'consultar_legajos', 'consultar_reportes',
            'listar_filtrar_clientes', 'listar_filtrar_procesos', 'consultar_historial_estados',
            'cambiar_perfil', 'gestionar_servicios', 'asignar_especialidad_servicio',
            'consultar_notificaciones',
        ],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionNames = array_values(array_unique(array_merge(...array_values(self::PERMISSIONS_BY_ROLE))));
        $permissions = [];

        foreach ($permissionNames as $permissionName) {
            $permissions[$permissionName] = Permission::findOrCreate($permissionName, 'web');
        }

        foreach (self::PERMISSIONS_BY_ROLE as $roleName => $rolePermissionNames) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions(array_map(
                fn (string $permissionName): Permission => $permissions[$permissionName],
                $rolePermissionNames,
            ));
        }

        Role::findOrCreate('Administrador', 'web')->syncPermissions(array_values($permissions));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
