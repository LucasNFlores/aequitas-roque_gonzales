<?php

namespace Tests\Feature;

use App\Models\CategoriaDocumento;
use App\Models\Documento;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReporteDocumentoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->travelTo(Carbon::parse('2026-10-20 12:00:00'));
    }

    public function test_coordinator_and_administrator_can_open_the_report(): void
    {
        foreach (['Coordinador', 'Administrador'] as $role) {
            $user = User::factory()->create();
            $user->assignRole($role);

            $this->actingAs($user)
                ->get(route('reportes.documentos.index'))
                ->assertOk()
                ->assertSee('Informe de documentos por categoría');
        }
    }

    public function test_default_period_is_the_last_30_calendar_days_including_today(): void
    {
        $coordinator = User::factory()->create();
        $coordinator->assignRole('Coordinador');
        $category = CategoriaDocumento::factory()->create(['nombre' => 'Identidad']);

        Documento::factory()->create(['categoria_id' => $category->id, 'created_at' => '2026-09-20 23:59:59']);
        Documento::factory()->create(['categoria_id' => $category->id, 'created_at' => '2026-09-21 00:00:00']);
        Documento::factory()->create(['categoria_id' => $category->id, 'created_at' => '2026-10-20 23:59:59.999999']);
        Documento::factory()->create(['categoria_id' => $category->id, 'created_at' => '2026-10-21 00:00:00']);

        $this->actingAs($coordinator)
            ->get(route('reportes.documentos.index'))
            ->assertOk()
            ->assertSee('2026-09-21')
            ->assertSee('2026-10-20')
            ->assertSee('>2</p>', false);
    }

    public function test_manual_period_filters_documents_through_the_end_of_the_final_day(): void
    {
        $coordinator = User::factory()->create();
        $coordinator->assignRole('Coordinador');
        $category = CategoriaDocumento::factory()->create(['nombre' => 'Judicial']);
        Documento::factory()->create(['categoria_id' => $category->id, 'created_at' => '2026-09-01 00:00:00']);
        Documento::factory()->create(['categoria_id' => $category->id, 'created_at' => '2026-09-30 23:59:59.999999']);

        $this->actingAs($coordinator)
            ->get(route('reportes.documentos.index', [
                'fecha_desde' => '2026-09-01',
                'fecha_hasta' => '2026-09-30',
            ]))
            ->assertOk()
            ->assertSee('>2</td>', false);
    }

    public function test_invalid_periods_redirect_back_with_validation_errors(): void
    {
        $coordinator = User::factory()->create();
        $coordinator->assignRole('Coordinador');
        $cases = [
            [['fecha_desde' => '2026-10-01'], 'fecha_hasta'],
            [['fecha_hasta' => '2026-10-20'], 'fecha_desde'],
            [['fecha_desde' => '2026-02-30', 'fecha_hasta' => '2026-03-01'], 'fecha_desde'],
            [['fecha_desde' => '2026-10-21', 'fecha_hasta' => '2026-10-21'], 'fecha_desde'],
            [['fecha_desde' => '2026-10-10', 'fecha_hasta' => '2026-10-09'], 'fecha_hasta'],
            [['fecha_desde' => '2025-10-19', 'fecha_hasta' => '2026-10-20'], 'fecha_hasta'],
        ];

        foreach ($cases as [$filters, $errorField]) {
            $this->actingAs($coordinator)
                ->get(route('reportes.documentos.index', $filters))
                ->assertRedirect(route('reportes.documentos.index'))
                ->assertSessionHasErrors($errorField);
        }
    }

    public function test_coordinator_can_download_pdf_with_the_same_filters(): void
    {
        $coordinator = User::factory()->create();
        $coordinator->assignRole('Coordinador');

        $response = $this->actingAs($coordinator)->get(route('reportes.documentos.pdf', [
            'fecha_desde' => '2026-10-01',
            'fecha_hasta' => '2026-10-20',
            'estado' => 'activas',
        ]));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_directive_is_denied_and_guest_is_redirected_to_login(): void
    {
        $directive = User::factory()->create();
        $directive->assignRole('Directivo');

        $this->actingAs($directive)->get(route('reportes.documentos.index'))->assertForbidden();
        $this->actingAs($directive)->get(route('reportes.documentos.pdf'))->assertForbidden();

        $this->app['auth']->forgetGuards();

        $this->get(route('reportes.documentos.index'))->assertRedirect('/login');
    }
}
