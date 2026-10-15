# Diseño del dashboard del Profesional

## Datos y permisos

- Usar `Proceso::visibleTo($user)` como base de indicadores, estados y cola; el scope limita al Profesional a su asignación actual.
- Procesos activos excluyen `finalizado` y `rechazado`. La cola propia de seguimiento comprende `iniciado`, `en_proceso` y `en_espera`, ordenada por `fecha_inicio`, sin atribuir vencimientos.
- Clientes se cuentan mediante `Cliente::visibleTo($user)`, que incluye clientes relacionados a procesos asignados; el destino es la bandeja de legajos CU 18.
- Turnos se consultan mediante `Turno::visibleTo($user)`, estado `programado` y rango desde ahora hasta siete días; el destino es la agenda CU 8.
- No se agrega la métrica de reportes para usuarios que requieren alcance de asignación. `Reporte::visibleTo()` también exige que el proceso siga asignado al mismo Profesional, en concordancia con `ReportePolicy::view`.
- Los demás roles conservan sus métricas y consultas autorizadas.

## Interfaz

- Reutilizar `x-dashboard.process-state-chart` para Profesional y Secretario.
- El componente usa Chart.js diferido solo cuando hay al menos dos estados con casos. La lista de estados permanece visible como representación accesible, fallback para cero o un estado y respaldo si Chart.js no carga.
- Explicar en el componente que los datos del Profesional corresponden a sus asignaciones actuales. No usar color como único medio para distinguir valores.
- Mantener estados vacíos explícitos y enlaces a bandejas existentes autorizadas.

## Verificación

- Probar asignaciones ajenas y reportes de procesos reasignados en consultas y agregados.
- Recorrer la vista real en navegador con Profesional y Secretario, comprobar tarjetas, links, gráfico/lista y reflujo móvil; limpiar cualquier registro de QA utilizado.
