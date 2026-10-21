<?php

namespace Database\Seeders;

use App\Models\CategoriaDocumento;
use App\Models\Cliente;
use App\Models\ComprobantePago;
use App\Models\Documento;
use App\Models\DocumentoVersion;
use App\Models\HistorialEstadoProceso;
use App\Models\Proceso;
use App\Models\Reporte;
use App\Models\ReporteVersion;
use App\Models\Servicio;
use App\Models\Turno;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    /** @var list<string> */
    private const SERVICE_NAMES = [
        'Consultoría',
        'Redacción de contrato',
        'Representación en juicio civil',
        'Asesoría en derecho familiar',
        'Trámite de sucesión',
    ];

    /** @var list<string> */
    private const DOCUMENT_CATEGORIES = [
        'Identidad',
        'Comprobante ARCA',
        'Informe profesional',
        'Contrato/Honorarios',
        'Judicial',
    ];

    /** @var array<string, list<string>> */
    private const SERVICES_BY_PROFESSIONAL = [
        'juanperez@example.com' => ['Consultoría', 'Representación en juicio civil', 'Redacción de contrato'],
        'profesional.civil@example.test' => ['Consultoría', 'Representación en juicio civil', 'Redacción de contrato'],
        'profesional.familia@example.test' => ['Consultoría', 'Asesoría en derecho familiar', 'Trámite de sucesión'],
        'profesional.comercial@example.test' => ['Consultoría', 'Redacción de contrato', 'Trámite de sucesión'],
    ];

    /** @var list<string> */
    private const PROCESS_STATES = [
        'pendiente', 'admitido', 'en_proceso', 'finalizado', 'iniciado',
        'pendiente', 'en_espera', 'admitido', 'finalizado', 'iniciado',
        'pendiente', 'en_proceso', 'rechazado', 'admitido', 'en_espera',
        'iniciado', 'finalizado', 'pendiente', 'en_proceso', 'admitido',
        'en_espera',
    ];

    /** @var list<array{nombre: string, apellido: string}> */
    private const CLIENT_NAMES = [
        ['nombre' => 'Lucía', 'apellido' => 'Acosta'],
        ['nombre' => 'Mateo', 'apellido' => 'Benítez'],
        ['nombre' => 'Sofía', 'apellido' => 'Cabrera'],
        ['nombre' => 'Tomás', 'apellido' => 'Domínguez'],
        ['nombre' => 'Valentina', 'apellido' => 'Escobar'],
        ['nombre' => 'Joaquín', 'apellido' => 'Figueroa'],
        ['nombre' => 'Camila', 'apellido' => 'Giménez'],
        ['nombre' => 'Benjamín', 'apellido' => 'Herrera'],
        ['nombre' => 'Martina', 'apellido' => 'Ibarra'],
        ['nombre' => 'Thiago', 'apellido' => 'Juárez'],
        ['nombre' => 'Catalina', 'apellido' => 'Ledesma'],
        ['nombre' => 'Nicolás', 'apellido' => 'Molina'],
        ['nombre' => 'Emma', 'apellido' => 'Navarro'],
        ['nombre' => 'Bautista', 'apellido' => 'Ojeda'],
        ['nombre' => 'Renata', 'apellido' => 'Pereyra'],
        ['nombre' => 'Franco', 'apellido' => 'Quiroga'],
        ['nombre' => 'Olivia', 'apellido' => 'Romero'],
        ['nombre' => 'Lautaro', 'apellido' => 'Sosa'],
        ['nombre' => 'Julieta', 'apellido' => 'Torres'],
        ['nombre' => 'Máximo', 'apellido' => 'Vega'],
        ['nombre' => 'Alma', 'apellido' => 'Zárate'],
    ];

    public function run(): void
    {
        $coordinator = User::query()->where('email', 'coordinador@example.com')->firstOrFail();
        $professionals = $this->seedProfessionals();
        $services = Servicio::query()->whereIn('nombre', self::SERVICE_NAMES)->get()->keyBy('nombre');

        foreach (self::SERVICE_NAMES as $serviceName) {
            if (! $services->has($serviceName)) {
                throw new \RuntimeException("Falta el servicio base de demostración: {$serviceName}.");
            }
        }

        $servicesByProfessional = $this->assignServices($professionals, $services->all());
        $clients = $this->seedClients();
        $processes = $this->seedProcesses($clients, $professionals, $servicesByProfessional, $coordinator);
        $this->seedProcessHistory($processes, $coordinator);
        $this->seedDocumentsAndReceipts($processes, $coordinator);
        $reportCount = $this->seedReports($processes, $coordinator);
        $this->seedAppointments($processes, $professionals);

        if ($this->command !== null) {
            $this->command->info(sprintf(
                'Demo listo: %d profesionales, %d clientes, %d procesos, %d reportes, %d documentos y %d comprobantes.',
                count($professionals),
                count($clients),
                count($processes),
                $reportCount,
                count($processes),
                count($processes),
            ));
        }
    }

    /** @return list<User> */
    private function seedProfessionals(): array
    {
        $existingProfessional = User::query()->where('email', 'juanperez@example.com')->firstOrFail();
        $professionals = [$existingProfessional];
        $newProfessionals = [
            ['name' => 'Dra. Paula Ríos', 'email' => 'profesional.civil@example.test', 'dni' => '40990001'],
            ['name' => 'Dr. Andrés Luna', 'email' => 'profesional.familia@example.test', 'dni' => '40990002'],
            ['name' => 'Dra. Micaela Paz', 'email' => 'profesional.comercial@example.test', 'dni' => '40990003'],
        ];

        foreach ($newProfessionals as $index => $attributes) {
            $professional = User::withTrashed()->firstOrNew(['email' => $attributes['email']]);

            if ($professional->trashed()) {
                $professional->restore();
            }

            $professional->fill([
                ...$attributes,
                'password' => '1234',
                'telefono' => '11-4000-'.(100 + $index),
                'domicilio' => 'Av. de Mayo '.(100 + $index).', CABA',
                'fecha_nacimiento' => '198'.($index + 3).'-0'.($index + 1).'-15',
                'fecha_ingreso' => now()->subYears($index + 2)->toDateString(),
            ])->save();
            $professional->syncRoles(['Profesional']);
            $professionals[] = $professional;
        }

        return $professionals;
    }

    /**
     * @param  array<string, Servicio>  $services
     * @return array<int, list<Servicio>>
     */
    private function assignServices(array $professionals, array $services): array
    {
        $servicesByName = collect($services)->keyBy('nombre');
        $assignments = [];

        foreach ($professionals as $professional) {
            $serviceNames = self::SERVICES_BY_PROFESSIONAL[$professional->email];
            $professionalServices = [];

            foreach ($serviceNames as $serviceName) {
                $service = $servicesByName->get($serviceName);
                $professional->servicios()->syncWithoutDetaching([$service->id]);
                $professionalServices[] = $service;
            }

            $assignments[$professional->id] = $professionalServices;
        }

        return $assignments;
    }

    /** @return list<Cliente> */
    private function seedClients(): array
    {
        $clients = [];

        foreach (self::CLIENT_NAMES as $index => $person) {
            $dni = (string) (99010001 + $index);
            $client = Cliente::withTrashed()->firstOrNew(['dni' => $dni]);

            if ($client->trashed()) {
                $client->restore();
            }

            $client->fill([
                ...$person,
                'dni' => $dni,
                'telefono' => sprintf('11-4555-%04d', $index + 1),
                'correo' => sprintf('demo.cliente.%02d@example.test', $index + 1),
                'domicilio' => sprintf('Calle Ficticia %d, CABA', 100 + $index),
                'fecha_nacimiento' => sprintf('%04d-%02d-%02d', 1975 + ($index % 25), 1 + ($index % 12), 1 + ($index % 27)),
            ])->save();
            $clients[] = $client;
        }

        return $clients;
    }

    /**
     * @param  list<Cliente>  $clients
     * @param  list<User>  $professionals
     * @param  array<int, list<Servicio>>  $servicesByProfessional
     * @return list<array{key: string, client: Cliente, process: Proceso}>
     */
    private function seedProcesses(array $clients, array $professionals, array $servicesByProfessional, User $coordinator): array
    {
        $fixtures = [];
        $today = CarbonImmutable::now();

        foreach (self::PROCESS_STATES as $index => $status) {
            $client = $clients[$index];
            $professional = in_array($status, ['pendiente', 'rechazado'], true)
                ? null
                : $professionals[$index % count($professionals)];
            $availableServices = $professional === null
                ? $servicesByProfessional[$professionals[$index % count($professionals)]->id]
                : $servicesByProfessional[$professional->id];
            $service = $availableServices[$index % count($availableServices)];
            $key = sprintf('P%02d', $index + 1);
            $name = sprintf('[DEMO] Legajo %s - %s', $client->dni, $service->nombre);
            $process = Proceso::withTrashed()
                ->where('cliente_id', $client->id)
                ->where('nombre', $name)
                ->firstOrNew(['cliente_id' => $client->id, 'nombre' => $name]);

            if ($process->trashed()) {
                $process->restore();
            }

            Proceso::withoutEvents(function () use ($process, $professional, $service, $coordinator, $status, $today, $index): void {
                $process->fill([
                    'profesional_id' => $professional?->id,
                    'servicio_id' => $service->id,
                    'coordinador_id' => $coordinator->id,
                    'descripcion' => 'Expediente de demostración con información completamente ficticia.',
                    'fecha_inicio' => $today->subMonths(6)->toDateString(),
                    'honorarios' => $professional === null ? null : $service->costo_servicio,
                    'tipo' => ['Civil', 'Comercial', 'Familia'][$index % 3],
                    'estado' => $status,
                    'motivo_rechazo' => $status === 'rechazado' ? 'La documentación ficticia no reúne los requisitos de admisión.' : null,
                ])->save();
            });

            $fixtures[] = ['key' => $key, 'client' => $client, 'process' => $process];
        }

        return $fixtures;
    }

    /** @param list<array{key: string, client: Cliente, process: Proceso}> $processes */
    private function seedProcessHistory(array $processes, User $coordinator): void
    {
        $today = CarbonImmutable::now();

        foreach ($processes as $index => $fixture) {
            $process = $fixture['process'];
            $history = match ($process->estado) {
                'pendiente' => ['pendiente'],
                'admitido' => ['pendiente', 'admitido'],
                'iniciado' => ['pendiente', 'admitido', 'iniciado'],
                'en_proceso' => ['pendiente', 'admitido', 'iniciado', 'en_proceso'],
                'en_espera' => ['pendiente', 'admitido', 'iniciado', 'en_espera'],
                'finalizado' => ['pendiente', 'admitido', 'iniciado', 'en_proceso', 'finalizado'],
                'rechazado' => ['pendiente', 'rechazado'],
            };
            $previousState = null;

            foreach ($history as $step => $newState) {
                $reason = sprintf('[DEMO] %s transición %d', $fixture['key'], $step + 1);
                $changedAt = match ($step) {
                    0 => $today->subMonths(6),
                    1 => $today->subMonths(3),
                    2 => $today->subMonth(),
                    default => $today->subDays(4 + ($index % 5)),
                };
                $historyEntry = HistorialEstadoProceso::query()->firstOrNew([
                    'proceso_id' => $process->id,
                    'motivo' => $reason,
                ]);
                $historyEntry->fill([
                    'usuario_id' => $process->profesional_id ?? $coordinator->id,
                    'estado_anterior' => $previousState,
                    'estado_nuevo' => $newState,
                    'fecha_cambio' => $changedAt,
                ])->save();
                $previousState = $newState;
            }
        }
    }

    /**
     * @param  list<array{key: string, client: Cliente, process: Proceso}>  $processes
     */
    private function seedDocumentsAndReceipts(array $processes, User $coordinator): void
    {
        $categories = $this->documentCategories();
        $storage = Storage::disk('local');

        foreach ($processes as $index => $fixture) {
            $process = $fixture['process'];
            $category = $categories[$index % count($categories)];
            $documentName = sprintf('[DEMO] %s - documentación inicial', $fixture['key']);
            $filePath = sprintf('demo/documentos/%s-principal.pdf', strtolower($fixture['key']));
            $this->putPdf($filePath, $documentName);
            $document = Documento::withTrashed()
                ->where('proceso_id', $process->id)
                ->where('nombre', $documentName)
                ->firstOrNew(['proceso_id' => $process->id, 'nombre' => $documentName]);

            if ($document->trashed()) {
                $document->restore();
            }

            $document->fill([
                'categoria_id' => $category->id,
                'archivo_path' => $filePath,
                'tipo_documento' => $category->nombre,
            ])->save();

            if ($index % 3 === 0) {
                $versionPath = sprintf('demo/documentos/%s-version-anterior.pdf', strtolower($fixture['key']));
                $this->putPdf($versionPath, $documentName.' versión anterior');
                $version = DocumentoVersion::query()->firstOrNew([
                    'documento_id' => $document->id,
                    'archivo_path' => $versionPath,
                ]);
                $version->fill([
                    'usuario_id' => $coordinator->id,
                    'archivo_path' => $versionPath,
                    'categoria_id' => $category->id,
                    'tipo_documento' => $category->nombre,
                    'nombre' => $documentName.' versión anterior',
                    'fecha_reemplazo' => CarbonImmutable::now()->subMonths(2),
                ])->save();
            }

            $receiptPath = sprintf('demo/comprobantes/%s-pago.pdf', strtolower($fixture['key']));
            $this->putPdf($receiptPath, sprintf('[DEMO] Comprobante de pago %s', $fixture['key']));
            $receipt = ComprobantePago::query()->firstOrNew(['archivo_path' => $receiptPath]);
            $receipt->fill([
                'cliente_id' => $fixture['client']->id,
                'proceso_id' => $process->id,
                'fecha_subida' => CarbonImmutable::now()->subDays(2 + ($index % 150))->toDateString(),
                'descripcion' => sprintf('[DEMO] Pago asociado al proceso %s', $fixture['key']),
            ])->save();
        }
    }

    /**
     * @param  list<array{key: string, client: Cliente, process: Proceso}>  $processes
     */
    private function seedReports(array $processes, User $coordinator): int
    {
        $reportCount = 0;
        foreach ($processes as $index => $fixture) {
            $process = $fixture['process'];

            if ($process->profesional_id === null) {
                continue;
            }

            $professional = User::query()->findOrFail($process->profesional_id);
            $content = sprintf('[DEMO] Informe de seguimiento %s: revisión de avance y próximos pasos.', $fixture['key']);
            $report = Reporte::withTrashed()
                ->where('proceso_id', $process->id)
                ->where('contenido', $content)
                ->firstOrNew(['proceso_id' => $process->id, 'contenido' => $content]);

            if ($report->trashed()) {
                $report->restore();
            }

            $report->fill([
                'profesional_id' => $professional->id,
                'registrado_por_id' => $professional->id,
                'fecha' => $index % 2 === 0
                    ? CarbonImmutable::now()->subMonths(4)->toDateString()
                    : CarbonImmutable::now()->subDays(2 + ($index % 25))->toDateString(),
            ])->save();
            $reportCount++;

            if ($index % 4 === 0) {
                $version = ReporteVersion::query()->firstOrNew([
                    'reporte_id' => $report->id,
                    'usuario_id' => $coordinator->id,
                ]);
                $version->fill([
                    'contenido_anterior' => sprintf('[DEMO] Borrador anterior del informe %s.', $fixture['key']),
                    'fecha_anterior' => CarbonImmutable::now()->subMonths(5)->toDateString(),
                    'usuario_nombre' => $coordinator->name,
                    'usuario_id' => $coordinator->id,
                    'fecha_cambio' => CarbonImmutable::now()->subMonths(4),
                ])->save();
            }
        }

        return $reportCount;
    }

    /** @return list<CategoriaDocumento> */
    private function documentCategories(): array
    {
        $categories = [];
        $descriptions = [
            'DNI, pasaporte y documentación personal',
            'Comprobantes descargados de ARCA',
            'Reportes del profesional asignado',
            'Contratos y fijación de honorarios',
            'Escritos y resoluciones judiciales',
        ];

        foreach (self::DOCUMENT_CATEGORIES as $index => $name) {
            $category = CategoriaDocumento::withTrashed()->firstOrNew(['nombre' => $name]);

            if (! $category->exists) {
                $category->fill([
                    'descripcion' => $descriptions[$index],
                    'activo' => true,
                ])->save();
            } elseif ($category->trashed()) {
                $category->restore();
            }

            $categories[] = $category;
        }

        return $categories;
    }

    /**
     * @param  list<array{key: string, client: Cliente, process: Proceso}>  $processes
     * @param  list<User>  $professionals
     */
    private function seedAppointments(array $processes, array $professionals): void
    {
        $today = CarbonImmutable::now();
        $scheduled = 0;
        $cancelled = 0;

        foreach ($processes as $fixture) {
            $process = $fixture['process'];

            if ($process->profesional_id === null || ! in_array($process->estado, ['admitido', 'iniciado', 'en_proceso', 'en_espera'], true)) {
                continue;
            }

            $existingAppointment = Turno::query()
                ->where('proceso_id', $process->id)
                ->where('tipo', 'seguimiento')
                ->exists();

            if ($existingAppointment) {
                continue;
            }

            $professional = User::query()->findOrFail($process->profesional_id);
            $slot = $this->findAvailableInternalSlot($professional, $today, $scheduled);

            if ($slot === null) {
                continue;
            }

            Turno::query()->create([
                'cliente_id' => $fixture['client']->id,
                'profesional_id' => $professional->id,
                'coordinador_id' => $this->coordinatorId(),
                'proceso_id' => $process->id,
                'fecha_hora' => $slot,
                'es_externo' => false,
                'tipo' => 'seguimiento',
                'estado' => 'programado',
            ]);
            $scheduled++;
        }

        foreach ($processes as $fixture) {
            $process = $fixture['process'];

            if ($process->profesional_id === null || ! in_array($process->estado, ['admitido', 'iniciado', 'en_proceso', 'en_espera', 'finalizado'], true)) {
                continue;
            }

            $existingCancellation = Turno::query()
                ->where('proceso_id', $process->id)
                ->where('tipo', 'consulta_inicial')
                ->where('estado', 'cancelado')
                ->exists();

            if ($existingCancellation) {
                $cancelled++;

                if ($cancelled >= 8) {
                    break;
                }

                continue;
            }

            $professional = User::query()->findOrFail($process->profesional_id);
            Turno::query()->create([
                'cliente_id' => $fixture['client']->id,
                'profesional_id' => $professional->id,
                'coordinador_id' => $this->coordinatorId(),
                'proceso_id' => $process->id,
                'fecha_hora' => $today->subDays(12 + $cancelled)->setTime(10, 0),
                'es_externo' => false,
                'tipo' => 'consulta_inicial',
                'estado' => 'cancelado',
            ]);
            $cancelled++;
            if ($cancelled >= 8) {
                break;
            }
        }

        foreach ($professionals as $index => $professional) {
            $detail = sprintf('[DEMO] Compromiso externo %d', $index + 1);
            $existingExternal = Turno::query()
                ->where('profesional_id', $professional->id)
                ->where('detalle_externo', $detail)
                ->first();

            if ($existingExternal !== null) {
                continue;
            }

            $slot = $this->findAvailableExternalDay($professional, $today, $index);

            if ($slot === null) {
                continue;
            }

            Turno::query()->create([
                'cliente_id' => $processes[$index]['client']->id,
                'profesional_id' => $professional->id,
                'coordinador_id' => $this->coordinatorId(),
                'proceso_id' => null,
                'fecha_hora' => $slot,
                'es_externo' => true,
                'detalle_externo' => $detail,
                'tipo' => 'externo',
                'estado' => 'programado',
            ]);
        }
    }

    private function coordinatorId(): int
    {
        return (int) User::query()->where('email', 'coordinador@example.com')->value('id');
    }

    private function findAvailableInternalSlot(User $professional, CarbonImmutable $today, int $seedIndex): ?CarbonImmutable
    {
        $baseHours = [9, 10, 11, 14, 15];
        $start = $seedIndex % count($baseHours);
        $hours = array_merge(array_slice($baseHours, $start), array_slice($baseHours, 0, $start));

        for ($day = 2; $day <= 120; $day++) {
            foreach ($hours as $hourIndex => $hour) {
                $slot = $today->addDays($day)->setTime($hour, 0);

                if (! Turno::existeConflicto($professional->id, $slot)) {
                    return $slot;
                }
            }
        }

        return null;
    }

    private function findAvailableExternalDay(User $professional, CarbonImmutable $today, int $seedIndex): ?CarbonImmutable
    {
        for ($day = 3 + $seedIndex; $day <= 180; $day++) {
            $slot = $today->addDays($day)->setTime(9, 0);

            if (! Turno::existeConflicto($professional->id, $slot, esExternoNuevo: true)) {
                return $slot;
            }
        }

        return null;
    }

    private function putPdf(string $path, string $title): void
    {
        $safeTitle = Str::ascii($title);
        $safeTitle = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $safeTitle);
        $stream = "BT /F1 12 Tf 24 110 Td ({$safeTitle}) Tj ET";
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 360 144] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            sprintf("<< /Length %d >>\nstream\n%s\nendstream", strlen($stream), $stream),
        ];
        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $index => $object) {
            $objectNumber = $index + 1;
            $offsets[] = strlen($pdf);
            $pdf .= sprintf("%d 0 obj\n%s\nendobj\n", $objectNumber, $object);
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 6\n0000000000 65535 f \n";

        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= sprintf("trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n%d\n%%%%EOF", $xrefOffset);
        Storage::disk('local')->put($path, $pdf);
    }
}
