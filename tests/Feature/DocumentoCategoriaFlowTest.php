<?php

namespace Tests\Feature;

use App\Livewire\Documentos\Index;
use App\Models\CategoriaDocumento;
use App\Models\Cliente;
use App\Models\Documento;
use App\Models\Proceso;
use App\Models\Servicio;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DocumentoCategoriaFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        Storage::fake('local');
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function procesoWithCliente(): Proceso
    {
        $cliente = Cliente::factory()->create();
        $coordinador = $this->userWithRole('Coordinador');
        $profesional = $this->userWithRole('Profesional');

        return Proceso::factory()->create([
            'cliente_id' => $cliente->id,
            'coordinador_id' => $coordinador->id,
            'profesional_id' => $profesional->id,
            'servicio_id' => Servicio::factory()->create()->id,
        ]);
    }

    public function test_ruta_anidada_exige_visualizar_documentacion(): void
    {
        $proceso = $this->procesoWithCliente();
        $secretario = $this->userWithRole('Secretario');
        $directivo = $this->userWithRole('Directivo');

        $this->actingAs($secretario)->get(route('procesos.documentos.index', $proceso))->assertOk();
        $this->actingAs($directivo)->get(route('procesos.documentos.index', $proceso))->assertForbidden();
    }

    public function test_profesional_solo_ve_su_proceso(): void
    {
        $proceso = $this->procesoWithCliente();
        $profesionalAsignado = User::find($proceso->profesional_id);
        $otroProfesional = $this->userWithRole('Profesional');

        $this->actingAs($profesionalAsignado)->get(route('procesos.documentos.index', $proceso))->assertOk();
        $this->actingAs($otroProfesional)->get(route('procesos.documentos.index', $proceso))->assertForbidden();
    }

    public function test_livewire_crea_documento_con_categoria_activa_y_auto_tipo(): void
    {
        $proceso = $this->procesoWithCliente();
        $secretario = $this->userWithRole('Secretario');
        $categoria = CategoriaDocumento::factory()->create(['activo' => true, 'nombre' => 'Judicial']);

        Livewire::actingAs($secretario)
            ->test(Index::class, ['proceso' => $proceso])
            ->set('nombre', 'Escrito demanda')
            ->set('categoriaId', $categoria->id)
            ->set('archivo', UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'))
            ->call('saveDocumento')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('documentos', [
            'proceso_id' => $proceso->id,
            'categoria_id' => $categoria->id,
            'tipo_documento' => 'Judicial',
            'nombre' => 'Escrito demanda',
        ]);

        $doc = Documento::first();
        Storage::disk('local')->assertExists($doc->archivo_path);
    }

    public function test_livewire_rechaza_categoria_inactiva(): void
    {
        $proceso = $this->procesoWithCliente();
        $secretario = $this->userWithRole('Secretario');
        $categoria = CategoriaDocumento::factory()->create(['activo' => false]);

        Livewire::actingAs($secretario)
            ->test(Index::class, ['proceso' => $proceso])
            ->set('nombre', 'Doc test')
            ->set('categoriaId', $categoria->id)
            ->set('archivo', UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'))
            ->call('saveDocumento')
            ->assertHasErrors(['categoriaId']);
    }

    public function test_categoria_dada_de_baja_no_se_ofrece_ni_se_acepta_en_una_nueva_carga(): void
    {
        $proceso = $this->procesoWithCliente();
        $secretario = $this->userWithRole('Secretario');
        $categoria = CategoriaDocumento::factory()->create(['nombre' => 'Categoría dada de baja']);
        $categoria->delete();

        Livewire::actingAs($secretario)
            ->test(Index::class, ['proceso' => $proceso])
            ->assertDontSee('Categoría dada de baja')
            ->set('nombre', 'Nuevo documento')
            ->set('categoriaId', $categoria->id)
            ->set('archivo', UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'))
            ->call('saveDocumento')
            ->assertHasErrors(['categoriaId']);
    }

    public function test_livewire_rechaza_sin_categoria(): void
    {
        $proceso = $this->procesoWithCliente();
        $secretario = $this->userWithRole('Secretario');

        Livewire::actingAs($secretario)
            ->test(Index::class, ['proceso' => $proceso])
            ->set('nombre', 'Doc test')
            ->set('categoriaId', null)
            ->set('archivo', UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'))
            ->call('saveDocumento')
            ->assertHasErrors(['categoriaId']);
    }

    public function test_livewire_edita_categoria_actualiza_tipo(): void
    {
        $proceso = $this->procesoWithCliente();
        $secretario = $this->userWithRole('Secretario');
        $cat1 = CategoriaDocumento::factory()->create(['nombre' => 'Identidad']);
        $cat2 = CategoriaDocumento::factory()->create(['nombre' => 'Judicial']);
        $doc = Documento::factory()->create(['proceso_id' => $proceso->id, 'categoria_id' => $cat1->id, 'tipo_documento' => 'Identidad']);

        Livewire::actingAs($secretario)
            ->test(Index::class, ['proceso' => $proceso])
            ->call('editDocumento', $doc->id)
            ->set('nombre', 'Actualizado')
            ->set('categoriaId', $cat2->id)
            ->call('saveDocumento')
            ->assertHasNoErrors();

        $this->assertEquals($cat2->id, $doc->fresh()->categoria_id);
        $this->assertEquals('Judicial', $doc->fresh()->tipo_documento);
        $this->assertEquals('Actualizado', $doc->fresh()->nombre);
    }

    public function test_livewire_elimina_con_baja_logica_y_conserva_archivo(): void
    {
        $proceso = $this->procesoWithCliente();
        $secretario = $this->userWithRole('Secretario');
        $doc = Documento::factory()->create(['proceso_id' => $proceso->id]);
        Storage::disk('local')->put($doc->archivo_path, 'fake pdf');

        Livewire::actingAs($secretario)
            ->test(Index::class, ['proceso' => $proceso])
            ->call('confirmDelete', $doc->id)
            ->call('deleteDocumento')
            ->assertHasNoErrors();

        $this->assertSoftDeleted($doc);
        Storage::disk('local')->assertExists($doc->archivo_path);
    }

    public function test_documentos_historicos_muestran_categoria_dada_de_baja_y_conservan_archivo(): void
    {
        $proceso = $this->procesoWithCliente();
        $secretario = $this->userWithRole('Secretario');
        $coordinador = $this->userWithRole('Coordinador');
        $categoria = CategoriaDocumento::factory()->create(['nombre' => 'Identidad histórica']);
        $documento = Documento::factory()->create([
            'proceso_id' => $proceso->id,
            'categoria_id' => $categoria->id,
            'tipo_documento' => 'Identidad histórica',
        ]);
        Storage::disk('local')->put($documento->archivo_path, 'archivo histórico');

        Livewire::actingAs($coordinador)->test(\App\Livewire\Categorias\Index::class)
            ->call('confirmDelete', $categoria->id)
            ->call('deleteCategoria')
            ->assertHasNoErrors();

        Livewire::actingAs($secretario)->test(Index::class, ['proceso' => $proceso])
            ->assertSee('Identidad histórica');

        $this->assertSame($categoria->id, $documento->fresh()->categoria_id);
        $this->assertSame('Identidad histórica', $documento->fresh()->categoria->nombre);
        Storage::disk('local')->assertExists($documento->archivo_path);
    }

    public function test_documentos_historicos_sin_categoria_conservan_tipo_y_archivo(): void
    {
        $proceso = $this->procesoWithCliente();
        $secretario = $this->userWithRole('Secretario');
        $documento = Documento::factory()->create([
            'proceso_id' => $proceso->id,
            'categoria_id' => null,
            'tipo_documento' => 'Tipo legado sin categoría',
        ]);
        Storage::disk('local')->put($documento->archivo_path, 'archivo legado');

        Livewire::actingAs($secretario)->test(Index::class, ['proceso' => $proceso])
            ->assertSee('Tipo legado sin categoría');

        $this->assertNull($documento->fresh()->categoria_id);
        $this->assertSame('Tipo legado sin categoría', $documento->fresh()->tipo_documento);
        Storage::disk('local')->assertExists($documento->archivo_path);
    }

    public function test_descarga_autorizada_por_rol_y_alcance(): void
    {
        $proceso = $this->procesoWithCliente();
        $profesionalAsignado = User::find($proceso->profesional_id);
        $profesionalAsignado->assignRole('Profesional');
        // ensure profesional has descargar_documentacion via role (already)
        $otroProfesional = $this->userWithRole('Profesional');
        $secretario = $this->userWithRole('Secretario');
        $coordinador = User::find($proceso->coordinador_id);

        $doc = Documento::factory()->create(['proceso_id' => $proceso->id]);
        Storage::disk('local')->put($doc->archivo_path, 'pdf content');

        $this->actingAs($profesionalAsignado)->get(route('procesos.documentos.download', [$proceso, $doc]))->assertOk();
        $this->actingAs($otroProfesional)->get(route('procesos.documentos.download', [$proceso, $doc]))->assertForbidden();
        $this->actingAs($secretario)->get(route('procesos.documentos.download', [$proceso, $doc]))->assertForbidden();
        $this->actingAs($coordinador)->get(route('procesos.documentos.download', [$proceso, $doc]))->assertOk();
    }

    public function test_controller_store_valida_categoria_activa(): void
    {
        $proceso = $this->procesoWithCliente();
        $secretario = $this->userWithRole('Secretario');
        $categoriaInactiva = CategoriaDocumento::factory()->create(['activo' => false]);

        $response = $this->actingAs($secretario)->post(route('procesos.documentos.store', $proceso), [
            'proceso_id' => $proceso->id,
            'categoria_id' => $categoriaInactiva->id,
            'nombre' => 'Test doc',
            'archivo' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors(['categoria_id']);
    }

    public function test_controller_store_rechaza_categoria_dada_de_baja(): void
    {
        $proceso = $this->procesoWithCliente();
        $secretario = $this->userWithRole('Secretario');
        $categoriaDadaDeBaja = CategoriaDocumento::factory()->create();
        $categoriaDadaDeBaja->delete();

        $response = $this->actingAs($secretario)->post(route('procesos.documentos.store', $proceso), [
            'proceso_id' => $proceso->id,
            'categoria_id' => $categoriaDadaDeBaja->id,
            'nombre' => 'Documento nuevo',
            'archivo' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors(['categoria_id']);
    }

    public function test_profesional_no_puede_crear_documento_por_permiso(): void
    {
        $proceso = $this->procesoWithCliente();
        $profesional = User::find($proceso->profesional_id);
        $categoria = CategoriaDocumento::factory()->create(['activo' => true]);

        Livewire::actingAs($profesional)
            ->test(Index::class, ['proceso' => $proceso])
            ->set('nombre', 'Intento')
            ->set('categoriaId', $categoria->id)
            ->set('archivo', UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'))
            ->call('saveDocumento')
            ->assertForbidden();
    }
}
