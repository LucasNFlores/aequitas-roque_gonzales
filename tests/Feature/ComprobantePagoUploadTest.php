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
        Storage::fake('local');
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

    public function test_users_with_cu14_can_open_a_private_receipt_pdf_inline(): void
    {
        $secretary = User::factory()->create();
        $secretary->assignRole('Secretario');
        $comprobante = ComprobantePago::factory()->create(['archivo_path' => 'comprobantes/recibo.pdf']);
        Storage::disk('local')->put($comprobante->archivo_path, '%PDF-1.4 test');

        $this->actingAs($secretary)
            ->get(route('comprobantes.show', $comprobante))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'inline; filename=comprobante-'.$comprobante->id.'.pdf');
    }

    public function test_users_without_cu14_cannot_open_a_receipt_pdf(): void
    {
        $professional = User::factory()->create();
        $professional->assignRole('Profesional');
        $comprobante = ComprobantePago::factory()->create();

        $this->actingAs($professional)
            ->get(route('comprobantes.show', $comprobante))
            ->assertForbidden();
    }

    public function test_missing_receipt_file_returns_not_found_after_authorization(): void
    {
        $coordinator = User::factory()->create();
        $coordinator->assignRole('Coordinador');
        $comprobante = ComprobantePago::factory()->create(['archivo_path' => 'comprobantes/inexistente.pdf']);

        $this->actingAs($coordinator)
            ->get(route('comprobantes.show', $comprobante))
            ->assertNotFound();
    }
}
