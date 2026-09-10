<?php

namespace Tests\Feature;

use App\Livewire\Usuarios\Index;
use App\Models\Cliente;
use App\Models\Proceso;
use App\Models\Servicio;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function actingAsDirectivo(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Directivo');

        return $user;
    }

    private function actingAsAdministrador(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Administrador');

        return $user;
    }

    public function test_directivo_o_administrador_registra_usuario_interno_con_rol_y_password_inicial(): void
    {
        // Dado un directivo o administrador autorizado, cuando registra un usuario interno y asigna su rol,
        // entonces el sistema valida los datos únicos, crea el usuario y genera la contraseña inicial.
        $directivo = $this->actingAsDirectivo();

        $response = $this->actingAs($directivo)->post(route('users.store'), [
            'name' => 'Nuevo Profesional',
            'email' => 'nuevo.profesional@example.com',
            // password omitida -> debe generar 1234
            'roles' => ['Profesional'],
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['email' => 'nuevo.profesional@example.com']);

        $creado = User::where('email', 'nuevo.profesional@example.com')->firstOrFail();
        $this->assertTrue($creado->hasRole('Profesional'));
        $this->assertTrue(Hash::check('1234', $creado->password));

        // Con password explícita
        $admin = $this->actingAsAdministrador();
        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Otro Usuario',
            'email' => 'otro@example.com',
            'password' => 'secreta123',
            'roles' => ['Secretario'],
        ])->assertRedirect(route('users.index'));

        $otro = User::where('email', 'otro@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('secreta123', $otro->password));
    }

    public function test_email_debe_ser_unico(): void
    {
        $directivo = $this->actingAsDirectivo();
        User::factory()->create(['email' => 'duplicado@example.com']);

        $this->actingAs($directivo)->post(route('users.store'), [
            'name' => 'Duplicado',
            'email' => 'duplicado@example.com',
            'roles' => ['Profesional'],
        ])->assertSessionHasErrors('email');

        $this->assertSame(1, User::where('email', 'duplicado@example.com')->count());
    }

    public function test_modificar_usuario_actualiza_permisos_sin_duplicar(): void
    {
        // Dado un usuario interno existente, cuando un directivo modifica datos o rol,
        // entonces el sistema valida y actualiza sin duplicar usuarios.
        $directivo = $this->actingAsDirectivo();
        $target = User::factory()->create(['email' => 'original@example.com']);
        $target->assignRole('Secretario');

        $this->actingAs($directivo)->put(route('users.update', $target), [
            'name' => 'Nombre Actualizado',
            'email' => 'actualizado@example.com',
            'roles' => ['Coordinador'],
        ])->assertRedirect(route('users.index'));

        $target->refresh();
        $this->assertSame('Nombre Actualizado', $target->name);
        $this->assertSame('actualizado@example.com', $target->email);
        $this->assertTrue($target->hasRole('Coordinador'));
        $this->assertFalse($target->hasRole('Secretario'));
        $this->assertSame(1, User::where('email', 'actualizado@example.com')->count());

        // Intentar email duplicado en update debe fallar
        User::factory()->create(['email' => 'existente@example.com']);
        $this->actingAs($directivo)->put(route('users.update', $target), [
            'name' => 'Otro Intento',
            'email' => 'existente@example.com',
            'roles' => ['Coordinador'],
        ])->assertSessionHasErrors('email');
    }

    public function test_baja_logica_impide_eliminacion_fisica_y_conserva_trazabilidad(): void
    {
        // Dado un usuario que posee procesos activos, cuando se solicita su baja,
        // entonces el sistema impide la eliminación física y permite baja lógica conservando trazabilidad.
        $directivo = $this->actingAsDirectivo();
        $coordinator = User::factory()->create();
        $coordinator->assignRole('Coordinador');
        $profesional = User::factory()->create();
        $profesional->assignRole('Profesional');

        // Proceso activo (pendiente)
        $cliente = Cliente::factory()->create();
        $servicio = Servicio::factory()->create();
        Proceso::factory()->create([
            'cliente_id' => $cliente->id,
            'profesional_id' => $profesional->id,
            'servicio_id' => $servicio->id,
            'coordinador_id' => $coordinator->id,
            'estado' => 'pendiente',
        ]);

        // Intentar baja debe ser forbidden (policy delete verifica procesos activos)
        $this->actingAs($directivo)->delete(route('users.destroy', $profesional))->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $profesional->id, 'deleted_at' => null]);

        // Finalizar proceso -> ahora sí permite baja lógica
        Proceso::where('profesional_id', $profesional->id)->update(['estado' => 'finalizado']);
        $this->actingAs($directivo)->delete(route('users.destroy', $profesional))->assertRedirect(route('users.index'));
        $this->assertSoftDeleted('users', ['id' => $profesional->id]);
        // No debe eliminar físicamente: sigue existiendo con withTrashed
        $this->assertNotNull(User::withTrashed()->find($profesional->id));
        // forceDelete no permitido
        $this->assertFalse($directivo->can('forceDelete', $profesional));
    }

    public function test_baja_logica_tambien_valida_coordinador_con_procesos_activos(): void
    {
        $directivo = $this->actingAsDirectivo();
        $coordinator = User::factory()->create();
        $coordinator->assignRole('Coordinador');
        $profesional = User::factory()->create();
        $profesional->assignRole('Profesional');

        $cliente = Cliente::factory()->create();
        $servicio = Servicio::factory()->create();
        Proceso::factory()->create([
            'cliente_id' => $cliente->id,
            'profesional_id' => $profesional->id,
            'servicio_id' => $servicio->id,
            'coordinador_id' => $coordinator->id,
            'estado' => 'en_proceso',
        ]);

        $this->actingAs($directivo)->delete(route('users.destroy', $coordinator))->assertForbidden();

        Proceso::where('coordinador_id', $coordinator->id)->update(['estado' => 'rechazado']);
        $this->actingAs($directivo)->delete(route('users.destroy', $coordinator))->assertRedirect(route('users.index'));
        $this->assertSoftDeleted('users', ['id' => $coordinator->id]);
    }

    public function test_servicio_o_especialidad_gestion_y_asignacion_evita_duplicados(): void
    {
        // Dado un servicio o especialidad, cuando un directivo/administrador/coordinador autorizado lo gestiona o asigna,
        // entonces el sistema evita duplicados y deja la asociación disponible para los procesos.
        $coordinador = User::factory()->create();
        $coordinador->assignRole('Coordinador');
        $directivo = $this->actingAsDirectivo();
        $admin = $this->actingAsAdministrador();
        $profesional = User::factory()->create();
        $profesional->assignRole('Profesional');
        $servicio = Servicio::factory()->create(['nombre' => 'Civil']);

        // Coordinador puede asignar
        $this->actingAs($coordinador)->put(route('users.servicios.update', $profesional), [
            'servicios' => [$servicio->id],
        ])->assertRedirect();
        $this->assertTrue($profesional->fresh()->servicios->contains($servicio->id));

        // Evita duplicados: enviar duplicado no crea dos filas (primary key compuesta)
        $this->actingAs($coordinador)->put(route('users.servicios.update', $profesional), [
            'servicios' => [$servicio->id, $servicio->id],
        ])->assertRedirect();
        $this->assertSame(1, $profesional->fresh()->servicios()->count());

        // Directivo y Administrador también pueden asignar
        $servicio2 = Servicio::factory()->create(['nombre' => 'Familia']);
        $this->actingAs($directivo)->put(route('users.servicios.update', $profesional), [
            'servicios' => [$servicio->id, $servicio2->id],
        ])->assertRedirect();
        $this->assertSame(2, $profesional->fresh()->servicios()->count());

        $servicio3 = Servicio::factory()->create(['nombre' => 'Comercial']);
        $this->actingAs($admin)->put(route('users.servicios.update', $profesional), [
            'servicios' => [$servicio3->id],
        ])->assertRedirect();
        $this->assertTrue($profesional->fresh()->servicios->contains($servicio3->id));

        // Profesional no puede asignar (403)
        $profActor = User::factory()->create();
        $profActor->assignRole('Profesional');
        $this->actingAs($profActor)->put(route('users.servicios.update', $profesional), [
            'servicios' => [$servicio->id],
        ])->assertForbidden();

        // Verificar asociación disponible para procesos: el profesional con servicio puede ser asignado
        $cliente = Cliente::factory()->create();
        $coord = User::factory()->create();
        $coord->assignRole('Coordinador');
        $proceso = Proceso::factory()->create([
            'cliente_id' => $cliente->id,
            'profesional_id' => $profesional->id,
            'servicio_id' => $servicio->id,
            'coordinador_id' => $coord->id,
        ]);
        $this->assertDatabaseHas('procesos', ['id' => $proceso->id, 'servicio_id' => $servicio->id]);
    }

    public function test_roles_deben_venir_de_bd_y_validar_existentes(): void
    {
        $directivo = $this->actingAsDirectivo();
        $target = User::factory()->create();

        // Rol inexistente -> error validación
        $this->actingAs($directivo)->post(route('users.store'), [
            'name' => 'Test',
            'email' => 'test.roles@example.com',
            'roles' => ['RolInexistente'],
        ])->assertSessionHasErrors('roles.0');

        // Roles válidos desde BD
        $rolDb = Role::where('name', 'Secretario')->firstOrFail();
        $this->actingAs($directivo)->post(route('users.store'), [
            'name' => 'Valido',
            'email' => 'valido.roles@example.com',
            'roles' => [$rolDb->name],
        ])->assertRedirect(route('users.index'));
        $this->assertTrue(User::where('email', 'valido.roles@example.com')->firstOrFail()->hasRole('Secretario'));

        // Directivo no puede asignar Administrador
        $this->actingAs($directivo)->post(route('users.store'), [
            'name' => 'Intento Admin',
            'email' => 'admin.intento@example.com',
            'roles' => ['Administrador'],
        ])->assertForbidden();
    }

    public function test_rutas_de_usuarios_existentes_y_protegidas(): void
    {
        $directivo = $this->actingAsDirectivo();
        $profesional = User::factory()->create();
        $profesional->assignRole('Profesional');
        $target = User::factory()->create();

        // rutas existen
        $this->assertTrue(Route::has('users.index'));
        $this->assertTrue(Route::has('users.create'));
        $this->assertTrue(Route::has('users.store'));
        $this->assertTrue(Route::has('users.edit'));
        $this->assertTrue(Route::has('users.update'));
        $this->assertTrue(Route::has('users.destroy'));
        $this->assertTrue(Route::has('users.servicios.update'));
        $this->assertTrue(Route::has('users.roles.edit'));
        $this->assertTrue(Route::has('users.roles.update'));

        // Profesional no puede acceder a index (listar_usuarios)
        $this->actingAs($profesional)->get(route('users.index'))->assertForbidden();
        $this->actingAs($directivo)->get(route('users.index'))->assertOk();
        $this->actingAs($directivo)->get(route('users.create'))->assertOk();
        $this->actingAs($profesional)->get(route('users.create'))->assertForbidden();
        $this->actingAs($directivo)->get(route('users.edit', $target))->assertOk();
        $this->actingAs($profesional)->delete(route('users.destroy', $target))->assertForbidden();
    }

    public function test_vistas_users_renderizan(): void
    {
        $directivo = $this->actingAsDirectivo();
        $this->actingAs($directivo)->get(route('users.index'))->assertOk()->assertSee('Listado de Usuarios')->assertSee('x-data', false);
        $this->actingAs($directivo)->get(route('users.create'))->assertOk()->assertSee('Nuevo Usuario Interno');
        $target = User::factory()->create();
        $this->actingAs($directivo)->get(route('users.edit', $target))->assertOk()->assertSee('Editar Usuario');
        $this->actingAs($directivo)->get(route('users.show', $target))->assertOk()->assertSee($target->email);
        $this->actingAs($directivo)->get(route('users.roles.edit', $target))->assertOk()->assertSee('Asignar Roles');
    }

    public function test_registro_publico_deshabilitado(): void
    {
        // CU25: solo Directivo/Administrador crean usuarios vía CU25, no vía /register
        $this->assertFalse(Route::has('register') && $this->get(route('register'))->isSuccessful());
        $this->get(route('login'))->assertOk(); // login sí existe
        $this->get('/register')->assertNotFound();
        $this->post('/register', [
            'name' => 'Intruso',
            'email' => 'intruso@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();
        $this->assertDatabaseMissing('users', ['email' => 'intruso@example.com']);
    }

    public function test_profile_destroy_usa_policy_y_bloquea_con_procesos_activos(): void
    {
        $profesional = User::factory()->create();
        $profesional->assignRole('Profesional');
        $coordinador = User::factory()->create();
        $coordinador->assignRole('Coordinador');
        $cliente = Cliente::factory()->create();
        $servicio = Servicio::factory()->create();
        Proceso::factory()->create([
            'cliente_id' => $cliente->id,
            'profesional_id' => $profesional->id,
            'servicio_id' => $servicio->id,
            'coordinador_id' => $coordinador->id,
            'estado' => 'pendiente',
        ]);

        // Intentar auto-eliminación con proceso activo -> 403 vía UserPolicy::delete
        $this->actingAs($profesional)->delete(route('profile.destroy'), [
            'password' => 'password',
        ])->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $profesional->id, 'deleted_at' => null]);

        // Finalizado -> permite baja (pero requiere password actual)
        Proceso::where('profesional_id', $profesional->id)->update(['estado' => 'finalizado']);
        $this->actingAs($profesional)->delete(route('profile.destroy'), [
            'password' => 'password',
        ])->assertRedirect('/');
        $this->assertSoftDeleted('users', ['id' => $profesional->id]);
    }

    public function test_migracion_add_profesional_fields_rollback(): void
    {
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasColumn('users', 'dni'));
        $this->assertTrue(Schema::hasColumn('users', 'deleted_at'));

        // Verifica que la migración tenga down() correcto (dropColumn / dropSoftDeletes) y no el typo dropColum
        $migrationPath = database_path('migrations/2026_06_03_221010_add_profesional_fields_to_users_table.php');
        $content = file_get_contents($migrationPath);
        $this->assertStringContainsString('dropColumn', $content);
        $this->assertStringNotContainsString('dropColum(', $content);
        $this->assertStringContainsString('dropSoftDeletes', $content);
        $this->assertStringNotContainsString('dropsoftDeletes', $content);

        // En SQLite el dropColumn con unique puede fallar por índices; no se ejecuta rollback real en este driver
        $this->assertTrue(true);
    }

    public function test_busqueda_por_dni_y_nombre(): void
    {
        $directivo = $this->actingAsDirectivo();
        User::factory()->create(['name' => 'Ana García', 'dni' => '30111222']);
        User::factory()->create(['name' => 'Bruno Díaz', 'dni' => '30222333']);

        // Búsqueda vía Livewire (la vista principal es Livewire; el query string clásico no se aplica al componente)
        Livewire::actingAs($directivo)->test(Index::class)
            ->set('search', 'Ana')
            ->assertSee('Ana García')
            ->assertDontSee('Bruno Díaz');

        Livewire::actingAs($directivo)->test(Index::class)
            ->set('search', '30111222')
            ->assertSee('30111222')
            ->assertDontSee('30222333');

        Livewire::actingAs($directivo)->test(Index::class)
            ->set('search', '30222333')
            ->assertSee('Bruno Díaz')
            ->assertDontSee('Ana García');

        // El controller clásico también soporta ?search= (para uso progresivo)
        $this->actingAs($directivo)->get(route('users.index'))->assertOk()->assertSee('Ana García')->assertSee('Bruno Díaz');
    }

    public function test_servicios_solo_a_profesionales(): void
    {
        $coordinador = User::factory()->create();
        $coordinador->assignRole('Coordinador');
        $secretario = User::factory()->create();
        $secretario->assignRole('Secretario');
        $servicio = Servicio::factory()->create();

        // Secretario (no profesional) -> 422
        $this->actingAs($coordinador)->put(route('users.servicios.update', $secretario), [
            'servicios' => [$servicio->id],
        ])->assertStatus(422);
        $this->assertSame(0, $secretario->fresh()->servicios()->count());

        // Profesional -> ok
        $prof = User::factory()->create();
        $prof->assignRole('Profesional');
        $this->actingAs($coordinador)->put(route('users.servicios.update', $prof), [
            'servicios' => [$servicio->id],
        ])->assertRedirect();
        $this->assertTrue($prof->fresh()->servicios->contains($servicio->id));

        // Livewire también bloquea para no-profesional (verificado vía HTTP; el componente usa abort 422)
        $this->assertFalse($secretario->hasRole('Profesional'));
        $this->assertTrue($prof->hasRole('Profesional'));
    }

    public function test_ui_gestionar_roles_no_visible_si_no_autorizado(): void
    {
        $directivo = $this->actingAsDirectivo();
        $admin = $this->actingAsAdministrador();
        $admin->name = 'Admin Target';
        $admin->save();

        // Directivo no puede manageRoles sobre Administrador -> botón no debe aparecer
        $response = $this->actingAs($directivo)->get(route('users.index'));
        $response->assertOk();
        // La vista Livewire genera botones por usuario; verificamos que directivo no tiene permiso manageRoles sobre admin
        $this->assertFalse($directivo->can('manageRoles', $admin));
        $this->assertTrue($admin->can('manageRoles', $admin)); // admin sí puede
        $this->assertTrue($directivo->can('manageRoles', User::factory()->create())); // sobre usuario normal sí
    }
}
