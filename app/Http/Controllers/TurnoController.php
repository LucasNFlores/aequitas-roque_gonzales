<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTurnoRequest;
use App\Http\Requests\UpdateTurnoRequest;
use App\Models\Cliente;
use App\Models\Notificacion;
use App\Models\Proceso;
use App\Models\Turno;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class TurnoController extends Controller
{
    /**
     * CU8 + listado HU-11. Profesional solo ve su agenda (policy + scopeVisibleTo).
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Turno::class);

        $turnos = Turno::visibleTo($request->user())
            ->with(['cliente', 'profesional', 'proceso'])
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->string('estado')))
            ->when($request->filled('profesional_id'), fn ($q) => $q->where('profesional_id', $request->input('profesional_id')))
            ->when($request->filled('tipo'), fn ($q) => $q->where('tipo', $request->input('tipo')))
            ->orderBy('fecha_hora')
            ->paginate(15)
            ->withQueryString();

        return view('turnos.index', compact('turnos'));
    }

    /**
     * CU8: agenda de un profesional con rango opcional.
     */
    public function agenda(Request $request): View
    {
        $this->authorize('viewAny', Turno::class);

        $user = $request->user();
        $profesionalId = $user->hasRole('Profesional')
            ? $user->id
            : ($request->input('profesional_id') ?: null);

        $turnos = Turno::visibleTo($user)
            ->with(['cliente', 'profesional', 'proceso'])
            ->activos()
            ->when($profesionalId, fn ($q) => $q->where('profesional_id', $profesionalId))
            ->when($request->filled('desde'), fn ($q) => $q->where('fecha_hora', '>=', $request->input('desde')))
            ->when($request->filled('hasta'), fn ($q) => $q->where('fecha_hora', '<=', $request->input('hasta')))
            ->orderBy('fecha_hora')
            ->paginate(15)
            ->withQueryString();

        $profesionales = User::role('Profesional')->orderBy('name')->get(['id', 'name']);

        return view('turnos.agenda', compact('turnos', 'profesionales', 'profesionalId'));
    }

    /**
     * CU4 / CU4.1: formulario de alta (internos + seguimiento).
     */
    public function create(): View
    {
        $this->authorize('create', Turno::class);

        return view('turnos.create', [
            'clientes' => Cliente::orderBy('apellido')->orderBy('nombre')->get(),
            'profesionales' => User::role('Profesional')->orderBy('name')->get(),
            'procesos' => Proceso::with('cliente')->orderByDesc('id')->limit(200)->get(),
            'tipos' => ['consulta_inicial' => 'Consulta inicial', 'seguimiento' => 'Seguimiento'],
        ]);
    }

    /**
     * CU4 / CU4.1: registra el turno y lo vincula con cliente y proceso.
     */
    public function store(StoreTurnoRequest $request): RedirectResponse
    {
        $this->authorize('create', Turno::class);

        $data = $request->validated();
        $data['estado'] = 'programado';
        $data['es_externo'] = (bool) ($data['es_externo'] ?? false);

        $turno = DB::transaction(function () use ($data) {
            $turno = Turno::create($data);
            $this->registrarNotificacion($turno, 'creado');

            return $turno;
        });

        return redirect()->route('turnos.show', $turno)->with('success', 'Turno registrado correctamente.');
    }

    public function show(Turno $turno): View
    {
        $this->authorize('view', $turno);

        $turno->load(['cliente', 'profesional', 'proceso']);

        return view('turnos.show', compact('turno'));
    }

    /**
     * CU6: formulario de reprogramación.
     */
    public function edit(Turno $turno): View
    {
        $this->authorize('update', $turno);

        if ($turno->isCancelado()) {
            return redirect()->route('turnos.show', $turno)->with('success', 'El turno está cancelado y se conserva en el historial.');
        }

        return view('turnos.edit', [
            'turno' => $turno,
            'clientes' => Cliente::orderBy('apellido')->orderBy('nombre')->get(),
            'profesionales' => User::role('Profesional')->orderBy('name')->get(),
            'procesos' => Proceso::with('cliente')->orderByDesc('id')->limit(200)->get(),
            'tipos' => ['consulta_inicial' => 'Consulta inicial', 'seguimiento' => 'Seguimiento'],
        ]);
    }

    /**
     * CU6: actualiza agenda y libera disponibilidad anterior.
     */
    public function update(UpdateTurnoRequest $request, Turno $turno): RedirectResponse
    {
        $this->authorize('update', $turno);

        $fechaAnterior = $turno->fecha_hora?->format('d/m/Y H:i');

        DB::transaction(function () use ($request, $turno, $fechaAnterior) {
            $turno->update($request->validated());

            $detalle = $fechaAnterior
                ? "Reprogramado de {$fechaAnterior} a {$turno->fresh()->fecha_hora?->format('d/m/Y H:i')}."
                : null;

            $this->registrarNotificacion($turno->fresh(), 'reprogramado', $detalle);
        });

        return redirect()->route('turnos.show', $turno)->with('success', 'Turno reprogramado correctamente. Se liberó la disponibilidad anterior.');
    }

    /**
     * CU7: cancelación lógica. Conserva historial, libera horario.
     * Nunca llama delete() ni soft-delete.
     */
    public function destroy(Turno $turno): RedirectResponse
    {
        $this->authorize('delete', $turno);

        DB::transaction(function () use ($turno) {
            $turno->cancelar();
            $this->registrarNotificacion($turno->fresh(), 'cancelado');
        });

        return redirect()->route('turnos.index')->with('success', 'Turno cancelado. Se conserva en el historial y se liberó el horario.');
    }

    /**
     * Notificación auditable DB + intento de envío (Brevo/SMTP via Mail).
     * Si el envío falla, queda estado=fallido pero no revierte el turno.
     */
    private function registrarNotificacion(Turno $turno, string $accion, ?string $detalle = null): void
    {
        $turno->loadMissing(['cliente', 'profesional', 'proceso']);

        $clienteNombre = $turno->cliente
            ? trim("{$turno->cliente->nombre} {$turno->cliente->apellido}")
            : "cliente #{$turno->cliente_id}";
        $profesionalNombre = $turno->profesional?->name ?? "profesional #{$turno->profesional_id}";
        $fecha = $turno->fecha_hora?->format('d/m/Y H:i') ?? '-';

        $mensaje = "Turno {$accion}: {$turno->tipo} el {$fecha} — cliente {$clienteNombre}, profesional {$profesionalNombre}."
            . ($detalle ? " {$detalle}" : '');

        $notificacion = Notificacion::create([
            'user_id' => $turno->profesional_id,
            'cliente_id' => $turno->cliente_id,
            'canal' => 'email',
            'mensaje' => mb_substr($mensaje, 0, 10000),
            'fecha_envio' => now(),
            'estado' => 'enviado',
        ]);

        try {
            $destinatarios = array_filter([
                $turno->profesional?->email,
                $turno->cliente?->correo,
            ]);

            if ($destinatarios !== []) {
                Mail::raw($notificacion->mensaje, function ($mail) use ($destinatarios, $accion) {
                    $mail->to($destinatarios)->subject("Aequitas — turno {$accion}");
                });
            }
        } catch (\Throwable $e) {
            Log::warning('HU-11 notificación turno falló envío', [
                'notificacion_id' => $notificacion->id,
                'turno_id' => $turno->id,
                'error' => $e->getMessage(),
            ]);
            $notificacion->update(['estado' => 'fallido']);
        }
    }
}
