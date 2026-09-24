<?php

namespace Tests\Feature;

use App\Livewire\Categorias\Index;
use App\Models\CategoriaDocumento;
use App\Models\Documento;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_desactivar_en_uso_conserva_historial_y_oculta(): void
    {
        $user = $this->userWithRole('Coordinador');
        $categoria = CategoriaDocumento::factory()->create(['nombre' => 'Contrato']);
        $documento = Documento::factory()->create(['categoria_id' => $categoria->id]);

        Livewire::actingAs($user)->test(Index::class)
            ->call('confirmDeactivate', $categoria->id)
            ->call('deactivateCategoria')
            ->assertHasNoErrors();

        $this->assertFalse($categoria->fresh()->activo);
        $this->assertEquals($categoria->id, $documento->fresh()->categoria_id);
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

    public function test_solo_activas_en_carga_nueva(): void
    {
        CategoriaDocumento::factory()->create(['nombre' => 'A-activa', 'activo' => true]);
        CategoriaDocumento::factory()->create(['nombre' => 'B-inactiva', 'activo' => false]);

        $nombres = CategoriaDocumento::paraCargaNueva()->pluck('nombre');
        $this->assertTrue($nombres->contains('A-activa'));
        $this->assertFalse($nombres->contains('B-inactiva'));
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
