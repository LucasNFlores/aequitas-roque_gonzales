# Habilitar la creación de procesos adicionales

## Objetivo

Planificar CU 38 para que un cliente ya registrado pueda recibir un nuevo proceso sin volver a registrarlo. La capacidad corresponde a Secretario y Administrador; Administrador conserva su alcance de superadministrador.

## Autoridad funcional

- **Fuente de verdad:** [Aequitas - Matriz de Módulos, Funciones y Permisos por Rol V2](https://docs.google.com/spreadsheets/d/13L_GwiJ1DvFIU2CaaXYfjaDDXHQEhSmcOST4z9bl1Oc/edit), pestaña homónima, CU 38.
- **Especificación vigente:** `openspec/specs/gestion-juridica/spec.md`.
- **Documentación ampliada:** `docs/aequitas-matriz-modulos-funciones-permisos-v2.md`, `docs/transcripcion_de_docs.md` y `docs/explicacion_sistema.md`.

## Problema

El alta de cliente sólo cubre el primer proceso: crea una Consultoría pendiente y un turno inicial con el Coordinador. Reutilizar ese flujo para una nueva necesidad de un cliente existente duplicaría sus datos administrativos y generaría un turno que no corresponde.

## Alcance

- Incorporar el caso de uso CU 38: crear un proceso adicional asociado a un cliente existente.
- Autorizar la operación exclusivamente a Secretario y Administrador.
- Crear el proceso en estado `pendiente`, para que continúe por el flujo existente de admisión o rechazo.
- Requerir los datos propios de un proceso y referencias activas válidas para cliente, servicio y coordinador.
- Mantener intacto el registro del cliente y no generar un turno automático.
- Definir autorización técnica, interfaz, validación y pruebas antes de implementar código.

## Fuera de alcance

- Registrar un nuevo cliente o modificar sus datos desde este flujo.
- Crear, reservar o modificar turnos automáticamente.
- Cambiar los permisos existentes de asignación, admisión, agenda o gestión de servicios.
- Implementar código, migraciones, rutas o vistas como parte de este cambio de planificación.

## Decisiones confirmadas

| Capacidad | Roles autorizados además de Administrador |
| --- | --- |
| CU 38: Crear Proceso Adicional | Secretario |

- El cliente debe existir y estar disponible para la operación; el flujo no puede crear un segundo cliente.
- El proceso nuevo comienza en estado `pendiente` y pasa por la admisión o rechazo vigente.
- El turno inicial es exclusivo del alta de un cliente nuevo. Para el proceso adicional, cualquier turno se crea explícitamente mediante los casos de uso de agenda.
- La autorización técnica debe ser específica de CU 38, aunque inicialmente sus roles coincidan con los que registran clientes.

## Áreas afectadas al aplicar

- `Proceso`, estado de proceso y relaciones con cliente, servicio y coordinador.
- Política, Form Request, acción o servicio de creación y permisos iniciales.
- Ruta e interfaz para iniciar la creación desde un cliente existente.
- Seeders de roles/permisos y pruebas de autorización, validación y persistencia.

## Criterio de salida

El cambio estará listo para aplicar cuando un diseño y tareas verificables garanticen que Secretario o Administrador pueden crear un proceso pendiente para un cliente existente, sin duplicarlo ni crear un turno automático, y que los demás roles no pueden hacerlo.

## Riesgos

- Reutilizar la autorización de alta de clientes impediría auditar y evolucionar CU 38 de forma independiente.
- Un doble envío podría crear procesos duplicados para el mismo cliente; la interfaz y el servidor deben prevenirlo de forma segura.
- Crear un turno como efecto lateral rompería la separación entre alta inicial y procesos adicionales.
