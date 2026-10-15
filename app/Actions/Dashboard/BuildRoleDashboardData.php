<?php

namespace App\Actions\Dashboard;

use App\Models\Cliente;
use App\Models\Proceso;
use App\Models\Reporte;
use App\Models\Turno;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BuildRoleDashboardData
{
    private const TERMINAL_PROCESS_STATES = ['finalizado', 'rechazado'];

    private const FOLLOW_UP_PROCESS_STATES = ['iniciado', 'en_proceso', 'en_espera'];

    /**
     * @return array{
     *     roleName: string,
     *     metrics: list<array{label: string, value: int, description: string, href: string}>,
     *     processStates: list<array{label: string, value: int}>,
     *     queues: list<array{title: string, description: string, count: int, items: Collection, href: string}>,
     *     appointments: array{count: int, items: Collection, href: string}|null,
     *     managementLinks: list<array{label: string, href: string}>
     * }
     */
    public function __invoke(User $user): array
    {
        $appointments = $this->appointments($user);

        return [
            'roleName' => $user->getRoleNames()->implode(', '),
            'metrics' => $this->metrics($user, $appointments),
            'processStates' => $this->processStateCounts($user),
            'queues' => $this->queues($user),
            'appointments' => $appointments,
            'managementLinks' => $this->managementLinks($user),
        ];
    }

    /** @return list<array{label: string, value: int, description: string, href: string}> */
    private function metrics(User $user, ?array $appointments): array
    {
        $metrics = [];

        if ($this->canConsultProcesses($user)) {
            $metrics[] = $this->activeProcessesMetric($user);
        }

        if ($user->can('listar_filtrar_clientes') || $user->can('consultar_legajos')) {
            $metrics[] = $this->clientsMetric($user);
        }

        if ($user->can('ver_agenda_profesional')) {
            $metrics[] = $this->appointmentsMetric($appointments);
        }

        if ($user->can('consultar_reportes')) {
            $metrics[] = $this->reportsMetric($user);
        }

        if ($user->can('listar_usuarios')) {
            $metrics[] = $this->usersMetric();
        }

        return $metrics;
    }

    /** @return array{label: string, value: int, description: string, href: string} */
    private function activeProcessesMetric(User $user): array
    {
        $activeCount = $this->visibleProcesses($user)
            ->whereNotIn('estado', self::TERMINAL_PROCESS_STATES)
            ->count();

        return [
            'label' => 'Procesos activos',
            'value' => $activeCount,
            'description' => 'Casos que todavía están en gestión.',
            'href' => route('procesos.index'),
        ];
    }

    /** @return array{label: string, value: int, description: string, href: string} */
    private function clientsMetric(User $user): array
    {
        $href = $user->can('listar_filtrar_clientes') ? route('clientes.index') : route('legajos.index');

        return [
            'label' => $user->can('listar_filtrar_clientes') ? 'Clientes' : 'Clientes en mis legajos',
            'value' => Cliente::query()->visibleTo($user)->count(),
            'description' => 'Personas visibles según el alcance de tu rol.',
            'href' => $href,
        ];
    }

    /** @return array{label: string, value: int, description: string, href: string} */
    private function appointmentsMetric(array $appointments): array
    {
        return [
            'label' => 'Turnos próximos',
            'value' => $appointments['count'],
            'description' => 'Turnos programados durante los próximos siete días.',
            'href' => $appointments['href'],
        ];
    }

    /** @return array{label: string, value: int, description: string, href: string} */
    private function reportsMetric(User $user): array
    {
        return [
            'label' => 'Reportes recientes',
            'value' => Reporte::query()
                ->visibleTo($user)
                ->whereBetween('fecha', [now()->subDays(29)->toDateString(), now()->toDateString()])
                ->count(),
            'description' => 'Reportes fechados en los últimos 30 días.',
            'href' => route('procesos.index'),
        ];
    }

    /** @return array{label: string, value: int, description: string, href: string} */
    private function usersMetric(): array
    {
        return [
            'label' => 'Usuarios activos',
            'value' => User::query()->count(),
            'description' => 'Cuentas internas sin baja lógica.',
            'href' => route('users.index'),
        ];
    }

    /** @return list<array{label: string, value: int}> */
    private function processStateCounts(User $user): array
    {
        if (! $this->canConsultProcesses($user)) {
            return [];
        }

        return $this->visibleProcesses($user)
            ->whereNotIn('estado', self::TERMINAL_PROCESS_STATES)
            ->select('estado')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('estado')
            ->orderBy('estado')
            ->get()
            ->map(fn (Proceso $process): array => [
                'label' => Str::headline($process->estado),
                'value' => (int) $process->total,
            ])
            ->all();
    }

    /** @return list<array{title: string, description: string, count: int, items: Collection, href: string}> */
    private function queues(User $user): array
    {
        $queues = [];

        if ($user->can('admitir_procesos')) {
            $queues[] = $this->processQueue($user, 'Para admitir', 'Casos que esperan una decisión de admisión.', ['pendiente']);
        }

        if ($user->can('asignar_profesionales')) {
            $queues[] = $this->assignmentQueue($user);
        }

        if ($user->can('actualizar_estados_proceso')) {
            $queues[] = $this->processQueue($user, 'En seguimiento', 'Casos operativos que pueden requerir una actualización.', self::FOLLOW_UP_PROCESS_STATES);
        }

        return $queues;
    }

    /** @param list<string> $states
     * @return array{title: string, description: string, count: int, items: Collection, href: string}
     */
    private function processQueue(User $user, string $title, string $description, array $states): array
    {
        $query = $this->visibleProcesses($user)->whereIn('estado', $states);

        return [
            'title' => $title,
            'description' => $description,
            'count' => (clone $query)->count(),
            'items' => $query->with('cliente')->orderBy('fecha_inicio')->limit(5)->get(),
            'href' => route('procesos.index'),
        ];
    }

    /** @return array{title: string, description: string, count: int, items: Collection, href: string} */
    private function assignmentQueue(User $user): array
    {
        $query = $this->visibleProcesses($user)
            ->where('estado', 'admitido')
            ->whereNull('profesional_id');

        return [
            'title' => 'Para asignar profesional',
            'description' => 'Casos admitidos que todavía no tienen profesional asignado.',
            'count' => (clone $query)->count(),
            'items' => $query->with('cliente')->orderBy('fecha_inicio')->limit(5)->get(),
            'href' => route('procesos.index'),
        ];
    }

    /** @return array{count: int, items: Collection, href: string}|null */
    private function appointments(User $user): ?array
    {
        if (! $user->can('ver_agenda_profesional')) {
            return null;
        }

        $query = $this->upcomingAppointmentsQuery($user);

        return [
            'count' => (clone $query)->count(),
            'items' => $query->with(['cliente', 'profesional'])->orderBy('fecha_hora')->limit(5)->get(),
            'href' => route('turnos.agenda'),
        ];
    }

    private function upcomingAppointmentsQuery(User $user): Builder
    {
        return Turno::query()
            ->visibleTo($user)
            ->where('estado', 'programado')
            ->whereBetween('fecha_hora', [now(), now()->addDays(7)]);
    }

    /** @return list<array{label: string, href: string}> */
    private function managementLinks(User $user): array
    {
        $links = [];

        if ($user->can('listar_usuarios')) {
            $links[] = ['label' => 'Usuarios y roles', 'href' => route('users.index')];
        }

        if ($user->can('gestionar_servicios')) {
            $links[] = ['label' => 'Servicios', 'href' => route('servicios.index')];
        }

        if ($user->can('asignar_especialidad_servicio')) {
            $links[] = ['label' => 'Servicios por profesional', 'href' => route('profesionales.servicios.index')];
        }

        return $links;
    }

    private function canConsultProcesses(User $user): bool
    {
        return $user->can('listar_filtrar_procesos') || $user->can('consultar_legajos');
    }

    private function visibleProcesses(User $user): Builder
    {
        return Proceso::query()->visibleTo($user);
    }
}
