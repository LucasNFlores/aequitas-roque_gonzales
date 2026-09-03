<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Servicios\Index as ServiciosIndex;
use App\Models\Servicio;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ServicioIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_services_page_renders_livewire_and_classic_page_remains_available(): void
    {
        $this->seed(RoleSeeder::class);
        $administrator = User::factory()->create();
        $administrator->assignRole('Administrador');

        $this->actingAs($administrator)
            ->get(route('servicios.index'))
            ->assertOk()
            ->assertSee('wire:click="createServicio"', false)
            ->assertSee('x-data', false)
            ->assertSee('x-show="formOpen"', false);

        $this->actingAs($administrator)
            ->get(route('servicios-viejo.index'))
            ->assertOk()
            ->assertSee('Versión clásica');

        $servicio = Servicio::factory()->create();

        $this->actingAs($administrator)
            ->get(route('servicios-viejo.create'))
            ->assertOk()
            ->assertSee('Nuevo Servicio - Versión clásica');

        $this->actingAs($administrator)
            ->get(route('servicios-viejo.edit', $servicio))
            ->assertOk()
            ->assertSee($servicio->nombre);
    }

    public function test_service_can_be_created_updated_and_deleted_from_livewire(): void
    {
        $this->seed(RoleSeeder::class);
        $administrator = User::factory()->create();
        $administrator->assignRole('Administrador');

        Livewire::actingAs($administrator)
            ->test(ServiciosIndex::class)
            ->call('createServicio')
            ->assertSet('showModal', true)
            ->set('nombre', 'Asesoría inicial')
            ->set('costoServicio', '15000')
            ->call('saveServicio')
            ->assertHasNoErrors()
            ->assertDispatched('servicio-guardado')
            ->assertSet('showModal', false);

        $servicio = Servicio::where('nombre', 'Asesoría inicial')->firstOrFail();
        $this->assertSame('15000.00', $servicio->costo_servicio);

        Livewire::actingAs($administrator)
            ->test(ServiciosIndex::class)
            ->call('editServicio', $servicio->id)
            ->assertSet('showModal', true)
            ->set('nombre', 'Asesoría actualizada')
            ->set('costoServicio', '17500')
            ->call('saveServicio')
            ->assertHasNoErrors();

        $servicio->refresh();
        $this->assertSame('Asesoría actualizada', $servicio->nombre);
        $this->assertSame('17500.00', $servicio->costo_servicio);

        Livewire::actingAs($administrator)
            ->test(ServiciosIndex::class)
            ->call('confirmDelete', $servicio->id)
            ->assertSet('showDeleteModal', true)
            ->call('deleteServicio')
            ->assertDispatched('servicio-eliminado')
            ->assertSet('showDeleteModal', false);

        $this->assertSoftDeleted('servicios', ['id' => $servicio->id]);
    }

    public function test_service_modal_validates_required_fields(): void
    {
        $this->seed(RoleSeeder::class);
        $administrator = User::factory()->create();
        $administrator->assignRole('Administrador');

        Livewire::actingAs($administrator)
            ->test(ServiciosIndex::class)
            ->call('createServicio')
            ->call('saveServicio')
            ->assertHasErrors(['nombre', 'costoServicio']);
    }
}
