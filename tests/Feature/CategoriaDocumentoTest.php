<?php

namespace Tests\Feature;

use App\Livewire\Categorias\Index;
use App\Models\CategoriaDocumento;
use App\Models\Documento;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CategoriaDocumentoTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $role): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_coordinador_y_administrador_pueden_ver_categorias(): void
    {
        foreach (['Coordinador', 'Administrador'] as $role) {
            $user = $this->userWithRole($role);
            $this->actingAs($user)->get(route('categorias.index'))->assertOk();
        }
    }

    public function test_secretario_profesional_directivo_reciben_403(): void
    {
        foreach (['Secretario', 'Profesional', 'Directivo'] as $role) {
            $user = $this->userWithRole($role);
            $this->actingAs($user)->get(route('categorias.index'))->assertForbidden();
        }
    }

    public function test_crear_categoria_valida_queda_disponible(): void
    {
        $user = $this->userWithRole('Coordinador');

        Livewire::actingAs($user)->test(Index::class)
            ->set('nombre', 'Pericia')
            ->set('descripcion', 'Informe pericial')
            ->call('saveCategoria')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categorias_documento', ['nombre' => 'Pericia', 'activo' => true]);
        $this->assertTrue(CategoriaDocumento::paraCargaNueva()->pluck('nombre')->contains('Pericia'));
    }

    public function test_no_permite_duplicados_ni_vacios(): void
    {
        $user = $this->userWithRole('Coordinador');
        CategoriaDocumento::factory()->create(['nombre' => 'DNI']);

        Livewire::actingAs($user)->test(Index::class)
            ->set('nombre', '  dni  ')
            ->set('descripcion', '')
            ->call('saveCategoria')
            ->assertHasErrors(['nombre']);

        Livewire::actingAs($user)->test(Index::class)
            ->set('nombre', '')
            ->call('saveCategoria')
            ->assertHasErrors(['nombre']);
    }

    public function test_editar_no_rompe_asociaciones(): void
    {
        $user = $this->userWithRole('Coordinador');
        $categoria = CategoriaDocumento::factory()->create(['nombre' => 'Recibo']);
        $documento = Documento::factory()->create(['categoria_id' => $categoria->id]);

        Livewire::actingAs($user)->test(Index::class)
            ->call('editCategoria', $categoria->id)
            ->set('nombre', 'Recibo ARCA')
            ->call('saveCategoria')
            ->assertHasNoErrors();

        $this->assertSame('Recibo ARCA', $categoria->fresh()->nombre);
        $this->assertEquals($categoria->id, $documento->fresh()->categoria_id);
    }

    public function test_desactivar_en_uso_queda_bloqueada(): void
    {
        $user = $this->userWithRole('Coordinador');
        $categoria = CategoriaDocumento::factory()->create(['nombre' => 'Contrato']);
        $documento = Documento::factory()->create(['categoria_id' => $categoria->id]);

        Livewire::actingAs($user)->test(Index::class)
            ->call('confirmDeactivate', $categoria->id)
            ->call('deactivateCategoria')
            ->assertHasErrors(['deactivate']);

        $this->assertTrue($categoria->fresh()->activo);
        $this->assertEquals($categoria->id, $documento->fresh()->categoria_id);
        $this->assertTrue(CategoriaDocumento::paraCargaNueva()->pluck('id')->contains($categoria->id));
    }

    public function test_desactivar_sin_uso_oculta_de_cargas(): void
    {
        $user = $this->userWithRole('Coordinador');
        $categoria = CategoriaDocumento::factory()->create(['nombre' => 'Temporal']);

        Livewire::actingAs($user)->test(Index::class)
            ->call('confirmDeactivate', $categoria->id)
            ->call('deactivateCategoria')
            ->assertHasNoErrors();

        $this->assertFalse($categoria->fresh()->activo);
        $this->assertFalse(CategoriaDocumento::paraCargaNueva()->pluck('id')->contains($categoria->id));
    }

    public function test_reactivar_vuelve_a_ofrecerse(): void
    {
        $user = $this->userWithRole('Coordinador');
        $categoria = CategoriaDocumento::factory()->create(['activo' => false]);

        Livewire::actingAs($user)->test(Index::class)
            ->call('activateCategoria', $categoria->id);

        $this->assertTrue($categoria->fresh()->activo);
        $this->assertTrue(CategoriaDocumento::paraCargaNueva()->pluck('id')->contains($categoria->id));
    }

    public function test_baja_logica_conserva_documentos_archivos_y_lectura_historica(): void
    {
        Storage::fake('local');
        $coordinador = $this->userWithRole('Coordinador');
        $categoria = CategoriaDocumento::factory()->create(['nombre' => 'Archivo histórico']);
        $documento = Documento::factory()->create([
            'categoria_id' => $categoria->id,
            'tipo_documento' => 'Tipo de documento legado',
        ]);
        Storage::disk('local')->put($documento->archivo_path, 'contenido de prueba');

        Livewire::actingAs($coordinador)->test(Index::class)
            ->call('confirmDelete', $categoria->id)
            ->assertSet('showDeleteModal', true)
            ->call('deleteCategoria')
            ->assertHasNoErrors();

        $this->assertSoftDeleted($categoria);
        $this->assertSame($categoria->id, $documento->fresh()->categoria_id);
        $this->assertSame('Archivo histórico', $documento->fresh()->categoria->nombre);
        $this->assertTrue($documento->fresh()->categoria->trashed());
        $this->assertSame('Tipo de documento legado', $documento->fresh()->tipo_documento);
        $this->assertFalse(CategoriaDocumento::paraCargaNueva()->contains('id', $categoria->id));
        Storage::disk('local')->assertExists($documento->archivo_path);

        Livewire::actingAs($coordinador)->test(Index::class)
            ->set('filtro', 'baja')
            ->assertSee('Archivo histórico')
            ->assertSee('Restaurar');
    }

    public function test_restaurar_categoria_no_cambia_su_estado_activo(): void
    {
        $coordinador = $this->userWithRole('Coordinador');
        $activa = CategoriaDocumento::factory()->create(['activo' => true]);
        $inactiva = CategoriaDocumento::factory()->create(['activo' => false]);
        $activa->delete();
        $inactiva->delete();

        Livewire::actingAs($coordinador)->test(Index::class)
            ->call('confirmRestore', $activa->id)
            ->assertSet('showRestoreModal', true)
            ->call('restoreCategoria')
            ->assertHasNoErrors();

        Livewire::actingAs($coordinador)->test(Index::class)
            ->call('confirmRestore', $inactiva->id)
            ->call('restoreCategoria')
            ->assertHasNoErrors();

        $this->assertFalse($activa->fresh()->trashed());
        $this->assertTrue($activa->fresh()->activo);
        $this->assertTrue(CategoriaDocumento::paraCargaNueva()->contains('id', $activa->id));
        $this->assertFalse($inactiva->fresh()->trashed());
        $this->assertFalse($inactiva->fresh()->activo);
        $this->assertFalse(CategoriaDocumento::paraCargaNueva()->contains('id', $inactiva->id));
    }

    public function test_force_delete_de_categoria_esta_denegado_incluso_para_administrador(): void
    {
        $administrador = $this->userWithRole('Administrador');
        $categoria = CategoriaDocumento::factory()->create();

        $this->assertFalse($administrador->can('forceDelete', $categoria));
    }

    public function test_solo_activas_en_carga_nueva(): void
    {
        CategoriaDocumento::factory()->create(['nombre' => 'A-activa', 'activo' => true]);
        CategoriaDocumento::factory()->create(['nombre' => 'B-inactiva', 'activo' => false]);

        $nombres = CategoriaDocumento::paraCargaNueva()->pluck('nombre');
        $this->assertTrue($nombres->contains('A-activa'));
        $this->assertFalse($nombres->contains('B-inactiva'));
    }

    public function test_no_autorizado_denegado_en_livewire_directo(): void
    {
        // Secretario no tiene CU37: el mount deniega con 403 y nada persiste.
        $user = $this->userWithRole('Secretario');
        $categoria = CategoriaDocumento::factory()->create(['nombre' => 'Reservada']);

        Livewire::actingAs($user)->test(Index::class)->assertForbidden();

        $this->assertTrue($categoria->fresh()->activo);
        $this->assertFalse(CategoriaDocumento::query()->where('nombre', 'Intento')->exists());
    }

    public function test_no_autorizado_no_puede_gestionar_por_ruta(): void
    {
        // El proyecto verifica Livewire no autorizado vía ruta 403 (ver EstadoProcesoIndexTest).
        // La policy + middleware ya deniegan URL/petición directa (CU37).
        $user = $this->userWithRole('Secretario');
        $categoria = CategoriaDocumento::factory()->create();

        $this->actingAs($user)->get(route('categorias.index'))->assertForbidden();
        $this->assertTrue($categoria->fresh()->activo);
    }
}
