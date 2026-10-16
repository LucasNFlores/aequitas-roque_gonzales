## ADDED Requirements

### Requirement: Consultar dashboard operativo con datos autorizados por rol

Secretario, Profesional, Coordinador, Directivo y Administrador MUST poder consultar `/dashboard` como acceso común interno. El dashboard MUST derivar cada indicador, lista y enlace de permisos vigentes y aplicar en el servidor el alcance de la consulta correspondiente antes de agregar, ordenar o limitar datos. El Profesional MUST recibir únicamente procesos que tenga asignados actualmente. El dashboard MUST NOT conceder permisos de consulta, mutación, agenda, legajo, documento o reporte que la matriz no otorgue.

#### Scenario: Resumen de procesos y clientes autorizados

- **WHEN** un usuario con CU 29 o CU 18 consulta el dashboard
- **THEN** los indicadores de procesos y clientes reflejan solo registros obtenidos mediante `visibleTo($user)`
- **AND** los procesos finalizados o rechazados no se cuentan como operativos
- **AND** CU 28 o CU 18 determina si se presenta el acceso y el conteo de clientes

#### Scenario: El Profesional consulta el dashboard

- **WHEN** un Profesional consulta el dashboard o sus agregados
- **THEN** cada consulta de procesos, clientes, reportes y agenda se limita a sus asignaciones actuales y CU aplicables antes de contar o mostrar filas
- **AND** IDs o filtros manipulados no permiten incluir registros de otro Profesional

#### Scenario: Pendientes de admisión y asignación

- **WHEN** Coordinador o Administrador consulta el dashboard
- **THEN** la cola de admisión incluye únicamente procesos visibles en estado `pendiente`
- **AND** Secretario, Coordinador o Administrador ven la cola de procesos `admitido` sin Profesional solo si pueden `asignar_profesionales`
- **AND** cada cola enlaza a la bandeja autorizada de procesos

#### Scenario: Agenda próxima

- **WHEN** Secretario, Profesional, Coordinador o Administrador consulta el dashboard
- **THEN** la métrica de agenda presenta solo turnos programados en los próximos siete días mediante `Turno::visibleTo($user)`
- **AND** el Profesional recibe solo sus propios turnos
- **AND** Directivo no recibe citas ni un enlace a agenda, ya que CU 8 no lo autoriza

#### Scenario: Métricas adicionales según capacidad

- **WHEN** Coordinador, Directivo o Administrador consulta CU 24, o Directivo o Administrador consulta CU 25–27
- **THEN** el dashboard puede contar reportes visibles de los últimos 30 días o usuarios activos respectivamente
- **AND** cada conteo usa la consulta y permisos existentes, sin presentar contenido de reportes ni datos individuales de usuarios
- **AND** el Profesional no recibe una tarjeta de reportes en su dashboard

#### Scenario: Profesional consulta su trabajo de seguimiento

- **WHEN** un Profesional consulta su panel
- **THEN** la lista de seguimiento muestra procesos operativos asignados en estados `iniciado`, `en_proceso` o `en_espera`
- **AND** la lista se presenta como carga de seguimiento, sin atribuir vencimientos que el dominio no registra

#### Scenario: Coordinador prioriza decisiones operativas

- **WHEN** Coordinador consulta el dashboard
- **THEN** el total y la distribución de procesos activos usan `Proceso::visibleTo($user)` antes de excluir los estados `finalizado` y `rechazado` y agregar por estado
- **AND** las colas muestran procesos visibles `pendiente`, `admitido` sin Profesional y en seguimiento (`iniciado`, `en_proceso`, `en_espera`) únicamente cuando el Coordinador posee el permiso de acción correspondiente
- **AND** la agenda muestra turnos visibles `programado` entre el momento actual y los próximos siete días mediante `Turno::visibleTo($user)`
- **AND** el gráfico horizontal aparece únicamente cuando hay dos o más estados con procesos y siempre conserva una lista numérica accesible como alternativa
- **AND** los totales de clientes y reportes recientes no ocupan el bloque principal del Coordinador, mientras que la navegación conserva sus accesos autorizados
- **AND** no se infieren vencimientos, productividad ni antigüedad de casos a partir de estos conteos

#### Scenario: No hay pendientes para el rol

- **WHEN** no existen elementos en una métrica o cola disponible para el rol
- **THEN** el dashboard muestra un estado vacío explicativo y solo presenta acciones con destino autorizado

#### Scenario: Director consulta CU 40

- **WHEN** Directivo consulta el dashboard
- **THEN** el sistema presenta sus indicadores de procesos, clientes, reportes y usuarios activos basados en CU 29, CU 28, CU 24 y CU 25–27
- **AND** no presenta agendas, documentos, archivos ni una cola jurídica que el rol no pueda resolver
- **AND** el gráfico horizontal muestra procesos activos por servicio con lista accesible equivalente

#### Scenario: Administrador consulta el panel operativo global

- **WHEN** Administrador consulta `/dashboard`
- **THEN** el conteo de procesos activos y su gráfico por estado aplican `Proceso::visibleTo($user)` antes de contar o agrupar y omiten estados `finalizado`, `rechazado` y bajas lógicas
- **AND** las colas incluyen procesos visibles `pendiente` y procesos visibles `admitido` sin profesional asignado, con sus conteos y vistas previas de hasta cinco registros
- **AND** no se presenta la cola de seguimiento ni el gráfico por servicio del Directivo
- **AND** los turnos próximos incluyen solo turnos visibles `programado` entre `now()` y `now()->addDays(7)`, inclusive, mediante `Turno::visibleTo($user)`
- **AND** las cuentas internas vigentes omiten usuarios dados de baja lógica y enlazan a la gestión de usuarios
- **AND** si no hay procesos activos o turnos próximos, cada bloque disponible comunica su estado vacío; la lista numérica acompaña el gráfico y sus valores no dependen solo del color
- **AND** el panel conserva enlaces a procesos, agenda y usuarios conforme a los permisos del superadministrador

#### Scenario: El panel Administrador no cambia el alcance de los otros roles

- **WHEN** Secretario, Profesional, Coordinador o Directivo consulta `/dashboard`
- **THEN** cada rol conserva sus scopes, indicadores, colas y enlaces definidos para HU-22 o CU 40
- **AND** el gráfico por servicio sigue limitado a la variante estratégica del Directivo

#### Scenario: Usuario no autenticado consulta el dashboard

- **WHEN** una persona sin sesión solicita `/dashboard`
- **THEN** el sistema la deriva al inicio de sesión y no revela indicadores ni pendientes

#### Scenario: Secretario consulta altas recientes y distribución de procesos

- **WHEN** Secretario consulta el dashboard
- **THEN** la tarjeta de altas recientes cuenta clientes visibles creados desde el comienzo del día de hace 29 días hasta el momento actual, inclusive en el límite inferior
- **AND** los clientes dados de baja lógicamente no se cuentan
- **AND** la cola de procesos admitidos sin Profesional asignado continúa siendo la única representación del pendiente accionable de asignación
- **AND** la distribución por estado usa barras horizontales solo cuando existen al menos dos estados activos con casos, junto con una lista accesible de valores
- **AND** con cero o un estado activo, el dashboard presenta solo la lista o su estado vacío y no muestra un canvas de gráfico
- **AND** roles distintos de Secretario no reciben esta métrica nueva ni el gráfico nuevo
