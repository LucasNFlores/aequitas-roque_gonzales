<?php

namespace Tests\Feature;

use App\Models\CategoriaDocumento;
use App\Models\Documento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoriaDocumentoTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(string $rol): User
    {
        return User::factory()->create(['rol' => $rol]);
    }

    /** CA1: autorizado crea válida y queda disponible. */
    public function test_coordinador_puede_crear_y_queda_disponible(): void
    {
        $u = $this->usuario('coordinador');
        $r = $this->actingAs($u)->post(route('categorias.store'), ['nombre' => 'Pericia', 'descripcion' => 'X']);
        $r->assertRedirect(route('categorias.index'));
        $this->assertDatabaseHas('categorias_documento', ['nombre' => 'Pericia', 'activo' => true]);
        $this->assertTrue(CategoriaDocumento::paraCargaNueva()->pluck('nombre')->contains('Pericia'));
    }

    public function test_administrador_tambien_puede_gestionar(): void
    {
        $u = $this->usuario('administrador');
        $this->actingAs($u)->get(route('categorias.index'))->assertOk();
    }

    /** CA4: duplicados case-insensitive + vacíos. */
    public function test_no_permite_duplicados_case_insensitive(): void
    {
        $u = $this->usuario('coordinador');
        CategoriaDocumento::create(['nombre' => 'DNI', 'activo' => true]);
        $r = $this->actingAs($u)->post(route('categorias.store'), ['nombre' => '  dni  ']);
        $r->assertSessionHasErrors('nombre');
    }

    public function test_valida_nombre_vacio(): void
    {
        $u = $this->usuario('coordinador');
        $this->actingAs($u)->post(route('categorias.store'), ['nombre' => ''])
            ->assertSessionHasErrors('nombre');
    }

    /** CA2: editar no rompe asociaciones. */
    public function test_editar_no_rompe_asociaciones(): void
    {
        $u = $this->usuario('coordinador');
        $c = CategoriaDocumento::create(['nombre' => 'Recibo', 'activo' => true]);
        $doc = Documento::factory()->create(['categoria_id' => $c->id]);
        $this->actingAs($u)->put(route('categorias.update', $c), ['nombre' => 'Recibo ARCA']);
        $this->assertEquals('Recibo ARCA', $c->fresh()->nombre);
        $this->assertEquals($c->id, $doc->fresh()->categoria_id);
    }

    /** CA3: desactivar en uso conserva historial y oculta en cargas. */
    public function test_desactivar_en_uso_conserva_historial_y_oculta(): void
    {
        $u = $this->usuario('coordinador');
        $c = CategoriaDocumento::create(['nombre' => 'Contrato', 'activo' => true]);
        $doc = Documento::factory()->create(['categoria_id' => $c->id]);

        $this->actingAs($u)->patch(route('categorias.deactivate', $c))->assertRedirect();
        $this->assertFalse($c->fresh()->activo);
        $this->assertEquals($c->id, $doc->fresh()->categoria_id); // historial intacto
        $this->assertFalse(CategoriaDocumento::paraCargaNueva()->pluck('id')->contains($c->id));
    }

    public function test_reactivar_vuelve_a_ofrecerse(): void
    {
        $u = $this->usuario('coordinador');
        $c = CategoriaDocumento::create(['nombre' => 'Oficio', 'activo' => false]);
        $this->actingAs($u)->patch(route('categorias.activate', $c))->assertRedirect();
        $this->assertTrue($c->fresh()->activo);
        $this->assertTrue(CategoriaDocumento::paraCargaNueva()->pluck('id')->contains($c->id));
    }

    public function test_solo_activas_en_carga_nueva(): void
    {
        CategoriaDocumento::create(['nombre' => 'A-activa', 'activo' => true]);
        CategoriaDocumento::create(['nombre' => 'B-inactiva', 'activo' => false]);
        $nombres = CategoriaDocumento::paraCargaNueva()->pluck('nombre');
        $this->assertTrue($nombres->contains('A-activa'));
        $this->assertFalse($nombres->contains('B-inactiva'));
    }

    /** SEG CU37: secretario, profesional, directivo → 403 incluso por petición directa. */
    public function test_roles_no_autorizados_reciben_403_en_todo(): void
    {
        $c = CategoriaDocumento::create(['nombre' => 'X', 'activo' => true]);
        foreach (['secretario', 'profesional', 'directivo'] as $rol) {
            $u = $this->usuario($rol);
            $this->actingAs($u)->get(route('categorias.index'))->assertForbidden();
            $this->actingAs($u)->post(route('categorias.store'), ['nombre' => 'Y-'.$rol])->assertForbidden();
            $this->actingAs($u)->put(route('categorias.update', $c), ['nombre' => 'Z'])->assertForbidden();
            $this->actingAs($u)->patch(route('categorias.deactivate', $c))->assertForbidden();
            $this->actingAs($u)->patch(route('categorias.activate', $c))->assertForbidden();
        }
    }

    public function test_invitado_redirige_login(): void
    {
        $this->get(route('categorias.index'))->assertRedirect('/login');
    }
}
