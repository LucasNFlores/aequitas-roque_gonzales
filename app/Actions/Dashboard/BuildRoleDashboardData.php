<?php

namespace App\Actions\Dashboard;

use App\Models\Cliente;
use App\Models\HistorialEstadoProceso;
use App\Models\Proceso;
use App\Models\Reporte;
use App\Models\Turno;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BuildRoleDashboardData
{
    private const TERMINAL_PROCESS_STATES = ['finalizado', 'rechazado'];

    private const FOLLOW_UP_PROCESS_STATES = ['iniciado', 'en_proceso', 'en_espera'];

    /**
     * @return array{
     *     roleName: string,
     *     isSecretaryDashboard: bool,
     *     isProfessionalDashboard: bool,
     *     isCoordinatorDashboard: bool,
     *     isDirectivoDashboard: bool,
     *     isAdministratorDashboard: bool,
     *     metrics: list<array{label: string, value: int, description: string, href: string}>,
     *     processStates: list<array{label: string, value: int}>,
     *     activeProcessesByService: list<array{label: string, value: int}>,
     *     historicalProcessStates: list<array{label: string, value: int}>,
     *     historyPeriod: string,
     *     historyPeriodLabel: string,
     *     historyFrom: string,
     *     historyTo: string,
     *     historyIncludesFinalized: bool,
     *     historyIncludesRejected: bool,
     *     queues: list<array{title: string, description: string, count: int, items: Collection, href: string}>,
     *     appointments: array{count: int, items: Collection, href: string}|null,
     *     managementLinks: list<array{label: string, href: string}>
     * }
     */
    public function __invoke(User $user, array $historyFilters = []): array
    {
        $appointments = $this->appointments($user);
        $isDirectivoDashboard = $this->isDirectivoDashboard($user);
        $isAdministratorDashboard = $this->isAdministratorDashboard($user);
        $history = $this->historicalProcessStates($user, $historyFilters);

        return [
            'roleName' => $user->getRoleNames()->implode(', '),
            'isSecretaryDashboard' => $this->isSecretaryDashboard($user),
            'isProfessionalDashboard' => $user->requiresProfessionalAssignmentScope(),
            'isCoordinatorDashboard' => $this->isCoordinatorDashboard($user),
            'isDirectivoDashboard' => $isDirectivoDashboard,
            'isAdministratorDashboard' => $isAdministratorDashboard,
            'metrics' => $this->metrics($user, $appointments),
            'processStates' => $isDirectivoDashboard ? [] : $this->processStateCounts($user),
            'activeProcessesByService' => $isDirectivoDashboard ? $this->activeProcessesByService($user) : [],
            ...$history,
            'queues' => $this->queues($user),
            'appointments' => $appointments,
            'managementLinks' => $this->managementLinks($user),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{
     *     historicalProcessStates: list<array{label: string, value: int}>,
     *     historyPeriod: string,
     *     historyPeriodLabel: string,
     *     historyFrom: string,
     *     historyTo: string,
     *     historyIncludesFinalized: bool,
     *     historyIncludesRejected: bool
     * }
     */
    private function historicalProcessStates(User $user, array $filters): array
    {
        if (! $this->isDirectivoDashboard($user) && ! $this->isAdministratorDashboard($user)) {
            return [
                'historicalProcessStates' => [],
                'historyPeriod' => '3_months',
                'historyPeriodLabel' => '',
                'historyFrom' => '',
                'historyTo' => '',
                'historyIncludesFinalized' => false,
                'historyIncludesRejected' => false,
            ];
        }

        $period = $filters['period'] ?? '3_months';
        $includesFinalized = filter_var($filters['include_finalizado'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $includesRejected = filter_var($filters['include_rechazado'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $today = now()->startOfDay();

        if ($period === 'custom') {
            $from = Carbon::createFromFormat('Y-m-d', $filters['from'])->startOfDay();
            $to = Carbon::createFromFormat('Y-m-d', $filters['to'])->endOfDay();
        } else {
            $months = $period === '6_months' ? 6 : 3;
            $from = $today->copy()->subMonthsNoOverflow($months);
            $to = $today->copy()->endOfDay();
        }

        $query = HistorialEstadoProceso::query()
            ->whereBetween('fecha_cambio', [$from, $to])
            ->whereHas('proceso', fn (Builder $processQuery): Builder => $processQuery->visibleTo($user));

        if (! $includesFinalized) {
            $query->where('estado_nuevo', '!=', 'finalizado');
        }

        if (! $includesRejected) {
            $query->where('estado_nuevo', '!=', Proceso::ESTADO_RECHAZADO);
        }

        $states = $query
            ->select('estado_nuevo')
            ->selectRaw('COUNT(DISTINCT proceso_id) as total')
            ->groupBy('estado_nuevo')
            ->orderBy('estado_nuevo')
            ->get()
            ->map(fn (HistorialEstadoProceso $history): array => [
                'label' => Str::headline($history->estado_nuevo),
                'value' => (int) $history->total,
            ])
            ->all();

        $periodLabel = match ($period) {
            '6_months' => 'Últimos 6 meses',
            'custom' => 'Rango personalizado',
            default => 'Últimos 3 meses',
        };

        return [
            'historicalProcessStates' => $states,
            'historyPeriod' => $period,
            'historyPeriodLabel' => $periodLabel,
            'historyFrom' => $from->toDateString(),
            'historyTo' => $to->toDateString(),
            'historyIncludesFinalized' => $includesFinalized,
            'historyIncludesRejected' => $includesRejected,
        ];
    }

    /** @return list<array{label: string, value: int, description: string, href: string}> */
    private function metrics(User $user, ?array $appointments): array
    {
        $metrics = [];

        if ($this->canConsultProcesses($user)) {
            $metrics[] = $this->activeProcessesMetric($user);
        }

        if (! $this->isAdministratorDashboard($user) && ! $this->isCoordinatorDashboard($user) && ($user->can('listar_filtrar_clientes') || $user->can('consultar_legajos'))) {
            $metrics[] = $this->isSecretaryDashboard($user)
                ? $this->recentClientsMetric($user)
                : $this->clientsMetric($user);
        }

        if ($user->can('ver_agenda_profesional')) {
            $metrics[] = $this->appointmentsMetric($appointments);
        }

        if (! $this->isAdministratorDashboard($user) && $user->can('consultar_reportes') && ! $user->requiresProfessionalAssignmentScope() && ! $this->isCoordinatorDashboard($user)) {
            $metrics[] = $this->reportsMetric($user);
        }

        if ($user->can('listar_usuarios')) {
            $metrics[] = $this->usersMetric($user);
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
            'label' => match (true) {
                $this->isDirectivoDashboard($user) => 'Procesos activos vigentes',
                $this->isAdministratorDashboard($user) => 'Procesos activos globales',
                default => 'Procesos activos',
            },
            'value' => $activeCount,
            'description' => match (true) {
                $this->isDirectivoDashboard($user) => 'Casos que todavía están en gestión, al corte actual.',
                $this->isAdministratorDashboard($user) => 'Procesos en gestión en todo el sistema, al corte actual.',
                default => 'Casos que todavía están en gestión.',
            },
            'href' => route('procesos.index'),
        ];
    }

    /** @return array{label: string, value: int, description: string, href: string} */
    private function clientsMetric(User $user): array
    {
        $href = $user->can('listar_filtrar_clientes') ? route('clientes.index') : route('legajos.index');

        return [
            'label' => $this->isDirectivoDashboard($user)
                ? 'Clientes vigentes'
                : ($user->can('listar_filtrar_clientes') ? 'Clientes' : 'Clientes en mis legajos'),
            'value' => Cliente::query()->visibleTo($user)->count(),
            'description' => $this->isDirectivoDashboard($user)
                ? 'Clientes no dados de baja lógica, al corte actual.'
                : 'Personas visibles según el alcance de tu rol.',
            'href' => $href,
        ];
    }

    /** @return array{label: string, value: int, description: string, href: string} */
    private function recentClientsMetric(User $user): array
    {
        return [
            'label' => 'Clientes registrados (30 días)',
            'value' => Cliente::query()
                ->visibleTo($user)
                ->whereBetween('created_at', [now()->subDays(29)->startOfDay(), now()])
                ->count(),
            'description' => 'Altas registradas en los últimos 30 días.',
            'href' => route('clientes.index'),
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
            'label' => $this->isDirectivoDashboard($user) ? 'Reportes (últimos 30 días)' : 'Reportes recientes',
            'value' => Reporte::query()
                ->visibleTo($user)
                ->whereDate('fecha', '>=', now()->subDays(29)->toDateString())
                ->whereDate('fecha', '<=', now()->toDateString())
                ->count(),
            'description' => 'Reportes fechados en los últimos 30 días.',
            'href' => route('procesos.index'),
        ];
    }

    /** @return array{label: string, value: int, description: string, href: string} */
    private function usersMetric(User $user): array
    {
        return [
            'label' => match (true) {
                $this->isDirectivoDashboard($user) => 'Cuentas vigentes',
                $this->isAdministratorDashboard($user) => 'Cuentas internas vigentes',
                default => 'Usuarios activos',
            },
            'value' => User::query()->count(),
            'description' => ($this->isDirectivoDashboard($user) || $this->isAdministratorDashboard($user))
                ? 'Cuentas internas sin baja lógica, al corte actual.'
                : 'Cuentas internas sin baja lógica.',
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

    /** @return list<array{label: string, value: int}> */
    private function activeProcessesByService(User $user): array
    {
        if (! $this->canConsultProcesses($user)) {
            return [];
        }

        return $this->visibleProcesses($user)
            ->whereNotIn('estado', self::TERMINAL_PROCESS_STATES)
            ->select('servicio_id')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('servicio_id')
            ->orderByDesc('total')
            ->orderBy('servicio_id')
            ->with(['servicio' => fn (BelongsTo $serviceRelation): BelongsTo => $serviceRelation->withTrashed()])
            ->get()
            ->map(fn (Proceso $process): array => [
                'label' => $process->servicio?->trashed()
                    ? $process->servicio->nombre.' (dado de baja)'
                    : ($process->servicio?->nombre ?? 'Servicio no disponible'),
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

        if ($user->can('actualizar_estados_proceso') && ! $this->isAdministratorDashboard($user)) {
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

    private function isSecretaryDashboard(User $user): bool
    {
        return $user->hasRole('Secretario') && ! $user->hasRole('Administrador');
    }

    private function isCoordinatorDashboard(User $user): bool
    {
        return $user->hasRole('Coordinador') && ! $user->hasRole('Administrador');
    }

    private function isDirectivoDashboard(User $user): bool
    {
        return $user->hasRole('Directivo') && ! $user->hasRole('Administrador');
    }

    private function isAdministratorDashboard(User $user): bool
    {
        return $user->hasRole('Administrador');
    }

    private function visibleProcesses(User $user): Builder
    {
        return Proceso::query()->visibleTo($user);
    }
}
