## ADDED Requirements

### Requirement: Agregar cambios históricos por estado en CU 40

El dashboard CU 40 MUST mostrar conteos históricos de procesos por estado, derivados de `HistorialEstadoProceso.fecha_cambio` en los últimos 3 o 6 meses o en un rango personalizado. MUST aplicar `Proceso::visibleTo($user)` antes de agregar y excluir procesos dados de baja. MUST contar procesos distintos por estado e incluir finalizados y rechazados solo cuando se seleccionen.

#### Scenario: Un proceso reingresa al mismo estado

- **WHEN** un proceso visible entra más de una vez en el mismo estado durante el período
- **THEN** cuenta una vez en ese estado
- **AND** puede contar en otro estado al que también ingresó durante el período

#### Scenario: El Directivo incluye estados terminales

- **WHEN** Directivo marca incluir finalizados o rechazados
- **THEN** los casos visibles que entraron en cada estado seleccionado aparecen en la distribución
- **AND** las barras se describen como conteos por estado que no se suman como total único

### Requirement: Filtrar cambios históricos por período en CU 40

Los presets MUST abarcar desde la misma fecha calendario de 3 o 6 meses antes hasta hoy, inclusive. El rango personalizado MUST requerir fechas válidas, no futuras y con `desde <= hasta`. La consulta histórica MUST NOT ejecutarse cuando el rango no supera la validación.

#### Scenario: Directivo consulta los últimos tres meses

- **WHEN** Directivo abre el dashboard sin parámetros o selecciona últimos 3 meses
- **THEN** el sistema agrupa por cada `estado_nuevo` registrado desde el límite inclusivo de tres meses hasta hoy, usando `fecha_cambio`
- **AND** conserva las cuatro métricas y el gráfico actual de procesos activos por servicio

#### Scenario: El filtro personalizado tiene fechas inválidas

- **WHEN** falta una fecha, `desde` es posterior a `hasta`, una fecha no es válida o `hasta` es futura
- **THEN** el sistema informa el error junto al formulario y no ejecuta la consulta histórica

### Requirement: Presentar claramente la distribución histórica de CU 40

La sección MUST explicar qué cuenta, la fecha de cambio usada y el período elegido. MUST NOT llamar “procesos activos” a los datos históricos ni presentarlos como un total único sumable. En resultados vacíos MUST explicar los filtros seleccionados y no dibujar un gráfico vacío. MUST conservar la visualización y las métricas actuales.

#### Scenario: El Directivo no tiene cambios en el período

- **WHEN** los filtros válidos no coinciden con cambios de estado en procesos visibles
- **THEN** el sistema explica que no hay cambios para el período y los filtros seleccionados
- **AND** no muestra un gráfico sin datos

#### Scenario: Un rol sin CU 40 intenta consultar la vista

- **WHEN** Secretario, Profesional o Coordinador solicita el dashboard con parámetros históricos
- **THEN** no recibe la distribución histórica
- **AND** conserva únicamente su panel autorizado

#### Scenario: Administrador consulta como superadministrador

- **WHEN** Administrador consulta el dashboard con CU 40
- **THEN** puede consultar la distribución histórica bajo los scopes de datos existentes
