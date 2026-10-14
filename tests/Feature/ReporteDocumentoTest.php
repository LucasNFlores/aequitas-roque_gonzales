<?php

namespace Tests\Feature;

use App\Models\CategoriaDocumento;
use App\Models\Documento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReporteDocumentoTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(string $rol): User
    {
        return User::factory()->create(['rol' => $rol]);
    }

    public function test_coordinador_ve_reporte_con_totales(): void
    {
        $u = $this->usuario('coordinador');
        $c = CategoriaDocumento::create(['nombre' => 'Identidad', 'activo' => true]);
        Documento::factory()->create(['categoria_id' => $c->id, 'created_at' => '2026-09-15 10:00:00']);

        $r = $this->actingAs($u)->get(route('reportes.documentos.index', [
            'fecha_desde' => '2026-09-01', 'fecha_hasta' => '2026-09-30',
        ]));

        $r->assertOk();
        $r->assertSee('Documentos por Categoría');
        $r->assertSee('REP-DOC-2026-001');
        $r->assertSee('Identidad');
    }

    public function test_filtro_estado_activas_excluye_inactivas(): void
    {
        $u = $this->usuario('administrador');
        CategoriaDocumento::create(['nombre' => 'A-activa', 'activo' => true]);
        CategoriaDocumento::create(['nombre' => 'B-inactiva', 'activo' => false]);

        $r = $this->actingAs($u)->get(route('reportes.documentos.index', ['estado' => 'activas']));
        $r->assertOk();
        $r->assertSee('A-activa');
        // B-inactiva puede aparecer en el <select> de filtro 2, pero no como fila de la tabla
        $r->assertDontSee('<td>B-inactiva</td>', false);
    }

    public function test_periodo_filtra_conteo(): void
    {
        $u = $this->usuario('coordinador');
        $c = CategoriaDocumento::create(['nombre' => 'Judicial', 'activo' => true]);
        Documento::factory()->create(['categoria_id' => $c->id, 'created_at' => '2026-09-10']);
        Documento::factory()->create(['categoria_id' => $c->id, 'created_at' => '2026-08-10']);

        // Septiembre: debe contar 1, no 2
        $r = $this->actingAs($u)->get(route('reportes.documentos.index', [
            'fecha_desde' => '2026-09-01', 'fecha_hasta' => '2026-09-30',
        ]));
        $r->assertOk();
        // El total del encabezado debe decir "1 documentos"
        $r->assertSee('1 documentos');
    }

    public function test_sin_datos_muestra_mensaje_no_error(): void
    {
        $u = $this->usuario('coordinador');
        $r = $this->actingAs($u)->get(route('reportes.documentos.index', [
            'fecha_desde' => '2025-01-01', 'fecha_hasta' => '2025-01-31',
        ]));
        $r->assertOk();
        $r->assertSee('Sin resultados');
    }

    public function test_pdf_descarga_con_mismos_filtros(): void
    {
        $u = $this->usuario('coordinador');
        CategoriaDocumento::create(['nombre' => 'Contrato', 'activo' => true]);

        $r = $this->actingAs($u)->get(route('reportes.documentos.pdf', [
            'fecha_desde' => '2026-09-01', 'fecha_hasta' => '2026-09-30',
        ]));
        $r->assertOk();
        $this->assertStringContainsString('application/pdf', $r->headers->get('Content-Type'));
    }

    public function test_roles_no_autorizados_reciben_403(): void
    {
        foreach (['secretario', 'profesional', 'directivo'] as $rol) {
            $u = $this->usuario($rol);
            $this->actingAs($u)->get(route('reportes.documentos.index'))->assertForbidden();
            $this->actingAs($u)->get(route('reportes.documentos.pdf'))->assertForbidden();
            $this->actingAs($u)->get(route('reportes.documentos.csv'))->assertForbidden();
        }
    }

    public function test_invitado_redirige_login(): void
    {
        $this->get(route('reportes.documentos.index'))->assertRedirect('/login');
    }
}
