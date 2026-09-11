<?php

namespace Tests\Feature;

use App\Livewire\Comprobantes\Index;
use App\Models\Cliente;
use App\Models\ComprobantePago;
use App\Models\Proceso;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ComprobantePagoUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_secretary_can_upload_a_pdf_for_a_selected_client(): void
    {
        Storage::fake('local');
        $secretary = User::factory()->create();
        $secretary->assignRole('Secretario');
        $client = Cliente::factory()->create(['dni' => '30111222']);

        Livewire::actingAs($secretary)
            ->test(Index::class)
            ->set('clienteId', $client->id)
            ->set('fechaSubida', now()->toDateString())
            ->set('descripcion', 'Pago de honorarios')
            ->set('archivo', UploadedFile::fake()->create('comprobante.pdf', 120, 'application/pdf'))
            ->call('saveComprobante')
            ->assertHasNoErrors()
            ->assertSet('successMessage', 'El comprobante se cargó correctamente.');

        $comprobante = ComprobantePago::query()->sole();

        $this->assertSame($client->id, $comprobante->cliente_id);
        $this->assertNull($comprobante->proceso_id);
        Storage::disk('local')->assertExists($comprobante->archivo_path);
    }

    public function test_the_case_link_is_optional_and_must_belong_to_the_selected_client(): void
    {
        $secretary = User::factory()->create();
        $secretary->assignRole('Secretario');
        $client = Cliente::factory()->create();
        $otherClient = Cliente::factory()->create();
        $otherProcess = Proceso::factory()->create(['cliente_id' => $otherClient->id]);

        Livewire::actingAs($secretary)
            ->test(Index::class)
            ->set('clienteId', $client->id)
            ->set('procesoId', $otherProcess->id)
            ->set('fechaSubida', now()->toDateString())
            ->set('archivo', UploadedFile::fake()->create('comprobante.pdf', 120, 'application/pdf'))
            ->call('saveComprobante')
            ->assertHasErrors('procesoId');
    }
}
