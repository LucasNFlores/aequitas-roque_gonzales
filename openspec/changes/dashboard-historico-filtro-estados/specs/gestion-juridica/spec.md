## MODIFIED Requirements

### Requirement: Consultar el dashboard estratégico del Directivo (CU 40)

El dashboard del Directivo y Administrador MUST permitir seleccionar uno o varios estados del catálogo de estados o presentes en el historial. En la primera consulta MUST incluir todos los estados, incluidos finalizado y rechazado. Una selección enviada MUST contener al menos un slug permitido y no repetido. El agregado MUST aplicar el filtro de estados y el scope `Proceso::visibleTo($user)` antes de contar procesos distintos por estado. La vista MUST conservar las fechas históricas, las métricas existentes, el gráfico vigente de procesos activos por servicio, la lista accesible y la aclaración de que un proceso puede aparecer en varias barras y las cantidades no se suman como total único.

#### Scenario: Selección inicial incluye todos los estados

- **WHEN** Directivo o Administrador abre `/dashboard` sin filtro de estados
- **THEN** se incluyen todos los estados disponibles, incluidos terminales
- **AND** todas las opciones aparecen seleccionadas

#### Scenario: Filtra por un estado no terminal o varios estados

- **WHEN** selecciona un estado no terminal, o más de un estado permitido
- **THEN** gráfico y lista muestran solo procesos distintos que entraron en los estados seleccionados durante el período
- **AND** los estados se derivan del catálogo y del historial, con etiquetas del catálogo cuando están disponibles

#### Scenario: Envía una selección vacía o slug inválido

- **WHEN** envía el grupo sin estados, un slug desconocido o valores repetidos
- **THEN** el servidor informa el error junto al filtro y conserva los valores enviados
- **AND** no ejecuta el agregado histórico con una selección inválida

#### Scenario: Directivo consulta la variante estratégica

- **WHEN** un Directivo consulta `/dashboard`
- **THEN** ve las cuatro métricas aprobadas y los procesos activos por servicio con una lista equivalente accesible
- **AND** no ve agenda, documentos, archivos ni colas operativas
- **AND** ve los cambios de estado históricos con el período, la fecha de cambio usada y el conteo de procesos distintos por estado claramente explicados
- **AND** puede seleccionar últimos 3 meses, últimos 6 meses o rango personalizado, y filtrar estados del catálogo o del historial
- **AND** todos los estados, incluidos finalizados y rechazados, están seleccionados por defecto
- **AND** los conteos históricos no se presentan como procesos activos ni como un total único sumable

#### Scenario: Agregados del Directivo excluyen datos no vigentes

- **WHEN** el sistema calcula las métricas o el gráfico de CU 40
- **THEN** excluye registros con baja lógica y procesos finalizados o rechazados
- **AND** cuenta solo reportes cuya fecha se encuentra entre hoy menos 29 días y hoy, inclusive
- **AND** el agregado histórico cuenta cada proceso una vez por estado y no mezcla eventos de procesos fuera del scope de CU 40

#### Scenario: Rol sin CU 40 consulta su dashboard

- **WHEN** Secretario, Profesional o Coordinador consulta `/dashboard`
- **THEN** conserva su panel autorizado de HU-22
- **AND** no recibe la distribución estratégica por servicio de CU 40
- **AND** no recibe la distribución histórica de CU 40
