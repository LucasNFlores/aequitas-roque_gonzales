<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Proceso;
use App\Models\User;
use Database\Seeders\EstadoProcesoSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteSearchFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_matches_name_surname_and_partial_dni(): void
    {
        $user = $this->userWithRole('Secretario');
        $byName = Cliente::factory()->create(['nombre' => 'María QA', 'apellido' => 'Gómez', 'dni' => '30000001']);
        $bySurname = Cliente::factory()->create(['nombre' => 'Lucía', 'apellido' => 'Fernández QA', 'dni' => '30000002']);
        $byDni = Cliente::factory()->create(['nombre' => 'Pedro', 'apellido' => 'Díaz', 'dni' => '30123456']);
        $unmatched = Cliente::factory()->create(['nombre' => 'Otro', 'apellido' => 'Cliente', 'dni' => '30999999']);

        foreach ([$byName, $bySurname, $byDni, $unmatched] as $cliente) {
            $this->createProcess($cliente);
        }

        $this->actingAs($user)->get(route('clientes.index', ['search' => 'María QA']))
            ->assertOk()
            ->assertSee('María QA');

        $this->actingAs($user)->get(route('clientes.index', ['search' => 'Fernández QA']))
            ->assertOk()
            ->assertSee('Fernández QA');

        $this->actingAs($user)->get(route('clientes.index', ['search' => '12345']))
            ->assertOk()
            ->assertSee('Pedro')
            ->assertDontSee('María QA');
    }

    public function test_state_filter_matches_any_process_and_lists_client_once(): void
    {
        $user = $this->userWithRole('Coordinador');
        $mixed = Cliente::factory()->create(['nombre' => 'Cliente mixto CU28']);
        $pendingOnly = Cliente::factory()->create(['nombre' => 'Solo pendiente CU28']);
        $this->createProcess($mixed, 'pendiente');
        $this->createProcess($mixed, 'admitido');
        $this->createProcess($pendingOnly, 'pendiente');

        $response = $this->actingAs($user)->get(route('clientes.index', ['estado' => 'admitido']))
            ->assertOk()
            ->assertSee('Cliente mixto CU28')
            ->assertDontSee('Solo pendiente CU28');

        $clientes = $response->viewData('clientes');
        $this->assertSame(1, $clientes->getCollection()->where('id', $mixed->id)->count());

        $this->actingAs($user)->get(route('clientes.index', ['estado' => 'pendiente']))
            ->assertOk()
            ->assertSee('Cliente mixto CU28')
            ->assertSee('Solo pendiente CU28');
    }

    public function test_search_and_state_filters_combine_and_pagination_preserves_parameters(): void
    {
        $user = $this->userWithRole('Secretario');

        for ($index = 1; $index <= 12; $index++) {
            $cliente = Cliente::factory()->create([
                'nombre' => 'Caso CU28',
                'apellido' => 'Paginación',
                'dni' => sprintf('40%06d', $index),
            ]);
            $this->createProcess($cliente, 'admitido');
        }

        $wrongState = Cliente::factory()->create(['nombre' => 'Caso CU28', 'apellido' => 'Paginación']);
        $this->createProcess($wrongState, 'pendiente');

        $response = $this->actingAs($user)->get(route('clientes.index', [
            'search' => 'Caso CU28',
            'estado' => 'admitido',
        ]))->assertOk();

        $this->assertSame(12, $response->viewData('clientes')->total());
        $this->assertSame(10, $response->viewData('clientes')->count());

        preg_match_all('/href="([^"]*page=2[^"]*)"/', $response->getContent(), $matches);
        $this->assertNotEmpty($matches[1], 'La paginación debe mostrar un enlace a la segunda página.');
        parse_str((string) parse_url(html_entity_decode($matches[1][0]), PHP_URL_QUERY), $query);
        $this->assertSame('Caso CU28', $query['search'] ?? null);
        $this->assertSame('admitido', $query['estado'] ?? null);

        $this->actingAs($user)->get(route('clientes.index', [
            'search' => 'Caso CU28',
            'estado' => 'admitido',
            'page' => 2,
        ]))->assertOk()
            ->assertSee('Caso CU28');
    }

    public function test_empty_search_result_explains_how_to_clear_filters(): void
    {
        $user = $this->userWithRole('Directivo');

        $this->actingAs($user)->get(route('clientes.index', ['search' => 'No existe CU28']))
            ->assertOk()
            ->assertSee('No encontramos clientes con esos filtros.')
            ->assertSee('Limpiar filtros')
            ->assertDontSee('No hay clientes cargados.');
    }

    public function test_only_roles_authorized_for_cu28_can_access_client_listing(): void
    {
        foreach (['Secretario', 'Coordinador', 'Directivo', 'Administrador'] as $role) {
            $this->actingAs($this->userWithRole($role))
                ->get(route('clientes.index'))
                ->assertOk();
        }

        $professional = $this->userWithRole('Profesional');

        $this->assertFalse($professional->can('listar_filtrar_clientes'));
        $this->actingAs($professional)->get(route('clientes.index'))->assertForbidden();
    }

    private function userWithRole(string $role): User
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function createProcess(Cliente $cliente, string $estado = 'pendiente'): Proceso
    {
        $this->seed(EstadoProcesoSeeder::class);

        return Proceso::factory()->create([
            'cliente_id' => $cliente->id,
            'estado' => $estado,
        ]);
    }
}
