# Gestionar estados e historial de procesos

## Objetivo

Implementar la tarjeta HU-08 mediante la gestión configurable de estados de procesos y la consulta auditable de sus transiciones.

## Autoridad funcional

- La fuente funcional consultada es el documento de Google Drive `Trabajo Práctico Integrador_analisis y diseño II`, especialmente CU 30 y CU 36.
- La matriz local y OpenSpec confirman que CU 30 permite consultar el historial a Secretario, Profesional, Coordinador, Directivo y Administrador.
- El Profesional sólo puede consultar el historial de procesos que tiene asignados.
- CU 36 permite a Coordinador y Administrador crear, editar, ordenar, activar y desactivar estados.
- Administrador conserva el alcance de superadministrador.

## Alcance

- Reemplazar la limitación de estados fija por un catálogo persistente de estados activos/inactivos.
- Registrar estado anterior, estado nuevo, fecha, usuario interviniente y motivo de cada transición.
- Exponer una interfaz Livewire para listar y filtrar procesos, cambiar estados autorizados y consultar su historial.
- Exponer una interfaz Livewire para administrar el catálogo de estados, con modales controlados por Alpine.js y estilos Tailwind CSS.
- Aplicar autorización en rutas, políticas, componentes Livewire y consultas.
- Agregar migraciones, seeders, pruebas y actualizar la documentación local afectada.

## Fuera de alcance

- Cambiar otros flujos de procesos, turnos, clientes o documentos.
- Modificar la matriz o el documento de Google Drive.
- Integrar APIs externas o crear una API separada.

## Riesgos y controles

- Un estado en uso no se podrá desactivar; esto evita dejar procesos sin un estado válido.
- Los cambios se registrarán desde el modelo para cubrir tanto la interfaz nueva como actualizaciones existentes.
- El catálogo conservará estados inactivos para que el historial siga siendo legible.
