<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Usuarios\Index as UsuariosIndex;
use App\Models\Cliente;
use App\Models\Proceso;
use App\Models\Servicio;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UsuarioIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_crear_y_editar_usuarios_desde_livewire(): void
    {
        $directivo = User::factory()->create();
        $directivo->assignRole('Directivo');

        // Crear
        Livewire::actingAs($directivo)
            ->test(UsuariosIndex::class)
            ->call('createUser')
            ->assertSet('showModal', true)
            ->set('name', 'Livewire Creado')
            ->set('email', 'livewire.creado@example.com')
            ->set('selectedRoles', ['Profesional'])
            ->call('saveUser')
            ->assertHasNoErrors()
            ->assertDispatched('usuario-guardado')
            ->assertSet('showModal', false);

        $creado = User::where('email', 'livewire.creado@example.com')->firstOrFail();
        $this->assertSame('Livewire Creado', $creado->name);
        $this->assertTrue($creado->hasRole('Profesional'));
        $this->assertTrue(Hash::check('1234', $creado->password));

        // Editar
        Livewire::actingAs($directivo)
            ->test(UsuariosIndex::class)
            ->call('editUser', $creado->id)
            ->assertSet('showModal', true)
            ->set('name', 'Livewire Editado')
            ->set('email', 'livewire.editado@example.com')
            ->set('selectedRoles', ['Secretario'])
            ->call('saveUser')
            ->assertHasNoErrors();

        $creado->refresh();
        $this->assertSame('Livewire Editado', $creado->name);
        $this->assertSame('livewire.editado@example.com', $creado->email);
        $this->assertTrue($creado->hasRole('Secretario'));
        $this->assertDatabaseCount('users', 2); // directivo + creado (no duplicado)
    }

    public function test_asignar_roles_desde_livewire_respeta_bd(): void
    {
        $directivo = User::factory()->create();
        $directivo->assignRole('Directivo');
        $target = User::factory()->create();

        // Validación: rol inexistente
        Livewire::actingAs($directivo)
            ->test(UsuariosIndex::class)
            ->call('editUser', $target->id)
            ->set('selectedRoles', ['RolInexistente'])
            ->call('saveUser')
            ->assertHasErrors(['selectedRoles.0']);

        // Directivo no puede asignar Administrador -> 403
        Livewire::actingAs($directivo)
            ->test(UsuariosIndex::class)
            ->call('editUser', $target->id)
            ->set('name', $target->name)
            ->set('email', $target->email)
            ->set('selectedRoles', ['Administrador'])
            ->call('saveUser')
            ->assertForbidden();

        // Administrador sí puede
        $admin = User::factory()->create();
        $admin->assignRole('Administrador');
        Livewire::actingAs($admin)
            ->test(UsuariosIndex::class)
            ->call('editUser', $target->id)
            ->set('name', $target->name)
            ->set('email', $target->email)
            ->set('selectedRoles', ['Administrador'])
            ->call('saveUser')
            ->assertHasNoErrors();
        $this->assertTrue($target->fresh()->hasRole('Administrador'));
    }

    public function test_asociar_servicios_a_profesionales_evita_duplicados(): void
    {
        $coordinador = User::factory()->create();
        $coordinador->assignRole('Coordinador');
        $profesional = User::factory()->create();
        $profesional->assignRole('Profesional');
        $servicio = Servicio::factory()->create();
        $servicio2 = Servicio::factory()->create();

        // Asociar vía modal dedicado
        Livewire::actingAs($coordinador)
            ->test(UsuariosIndex::class)
            ->call('manageServicios', $profesional->id)
            ->assertSet('showServiciosModal', true)
            ->set('selectedServicios', [$servicio->id])
            ->call('saveServicios')
            ->assertHasNoErrors()
            ->assertDispatched('servicios-asignados');

        $this->assertTrue($profesional->fresh()->servicios->contains($servicio->id));

        // Evitar duplicados: sync con mismo id repetido no duplica
        Livewire::actingAs($coordinador)
            ->test(UsuariosIndex::class)
            ->call('manageServicios', $profesional->id)
            ->set('selectedServicios', [$servicio->id, $servicio2->id])
            ->call('saveServicios')
            ->assertHasNoErrors();
        $this->assertSame(2, $profesional->fresh()->servicios()->count());

        // Via formulario principal también permite servicios
        $directivo = User::factory()->create();
        $directivo->assignRole('Directivo');
        Livewire::actingAs($directivo)
            ->test(UsuariosIndex::class)
            ->call('editUser', $profesional->id)
            ->set('selectedServicios', [$servicio->id])
            ->call('saveUser')
            ->assertHasNoErrors();
        $this->assertSame(1, $profesional->fresh()->servicios()->count());
    }

    public function test_desactivar_usuarios_con_baja_logica_y_validacion_procesos(): void
    {
        $directivo = User::factory()->create();
        $directivo->assignRole('Directivo');
        $coordinador = User::factory()->create();
        $coordinador->assignRole('Coordinador');
        $profesional = User::factory()->create();
        $profesional->assignRole('Profesional');
        $cliente = Cliente::factory()->create();
        $servicio = Servicio::factory()->create();
        Proceso::factory()->create([
            'cliente_id' => $cliente->id,
            'profesional_id' => $profesional->id,
            'servicio_id' => $servicio->id,
            'coordinador_id' => $coordinador->id,
            'estado' => 'pendiente',
        ]);

        // Con proceso activo -> forbidden en confirmDelete y también en deleteUser
        Livewire::actingAs($directivo)
            ->test(UsuariosIndex::class)
            ->call('confirmDelete', $profesional->id)
            ->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $profesional->id, 'deleted_at' => null]);

        // También probar que deleteUser directo es forbidden si se bypass confirm
        $component = Livewire::actingAs($directivo)->test(UsuariosIndex::class);
        $component->set('deletingUserId', $profesional->id);
        $component->call('deleteUser')->assertForbidden();

        // Finalizado -> permite desactivar
        Proceso::where('profesional_id', $profesional->id)->update(['estado' => 'finalizado']);
        Livewire::actingAs($directivo)
            ->test(UsuariosIndex::class)
            ->call('confirmDelete', $profesional->id)
            ->assertSet('showDeleteModal', true)
            ->call('deleteUser')
            ->assertDispatched('usuario-eliminado')
            ->assertSet('showDeleteModal', false);
        $this->assertSoftDeleted('users', ['id' => $profesional->id]);
    }

    public function test_usuario_unico_por_email_livewire_valida(): void
    {
        $directivo = User::factory()->create();
        $directivo->assignRole('Directivo');
        User::factory()->create(['email' => 'unico@example.com']);

        Livewire::actingAs($directivo)
            ->test(UsuariosIndex::class)
            ->call('createUser')
            ->set('name', 'Duplicado')
            ->set('email', 'unico@example.com')
            ->call('saveUser')
            ->assertHasErrors(['email']);
    }

    public function test_completar_rutas_y_vistas_livewire_renderizan(): void
    {
        $directivo = User::factory()->create();
        $directivo->assignRole('Directivo');
        $this->actingAs($directivo)->get(route('users.index'))->assertOk()
            ->assertSee('wire:click="createUser"', false)
            ->assertSee('x-data', false)
            ->assertSee('x-show="formOpen"', false)
            ->assertSee('x-show="serviciosOpen"', false);

        // Livewire component renderiza búsqueda y paginación
        Livewire::actingAs($directivo)->test(UsuariosIndex::class)
            ->set('search', 'noexiste')
            ->assertSee('No hay usuarios.');
    }

    public function test_servicios_index_sigue_funcionando(): void
    {
        // Verificar que servicios no se rompió
        $admin = User::factory()->create();
        $admin->assignRole('Administrador');
        $this->actingAs($admin)->get(route('servicios.index'))->assertOk();
    }
}
