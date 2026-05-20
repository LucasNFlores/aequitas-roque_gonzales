# Análisis de Falencias en Casos de Uso

Documento de análisis de las falencias encontradas en los casos de uso del documento de requerimientos, junto con las posibles soluciones propuestas.

---

## 1. Falencias Estructurales

### 1.1 CU19 Inexistente

Hay un salto numérico entre CU18 y CU20. El CU19 fue eliminado intencionalmente. No requiere acción.

---

### 1.2 CU4 y CU5 Redundantes

CU4 (Agendar Turno con Coordinador) y CU5 (Agendar Turno con Profesional) son prácticamente idénticos. La única diferencia es el tipo de profesional involucrado (coordinador vs profesional).

**Flujo idéntico:**

1. Actor selecciona fecha y hora
2. Sistema verifica disponibilidad
3. Actor confirma el turno
4. Sistema registra el turno
5. Sistema notifica a los involucrados

**Solución:** Unificar ambos casos de uso en uno solo:

#### CU4 - Agendar Turno

- **Actor Primario:** Secretario
- **Descripción:** Permite registrar un turno entre el cliente y un profesional (coordinador o profesional).
- **Precondición:** Cliente registrado en el sistema. Profesional seleccionado.
- **Flujo de Eventos:**
    1. El Actor ingresa DNI del cliente o lo selecciona de una lista.
    2. El Actor selecciona el profesional (coordinador o profesional específico).
    3. El Actor selecciona fecha y hora del turno.
    4. El Sistema verifica disponibilidad del profesional seleccionado.
    5. El Actor confirma el turno.
    6. El Sistema registra el turno.
    7. El Sistema notifica al profesional y al cliente.
- **Postcondición:** Turno registrado. Cliente asociado al turno. Profesional asociado al turno. Involucrados notificados.
- **Flujo Alternativo:** Si no hay disponibilidad -> el sistema solicita otra fecha.

**Nota:** Eliminar CU5. Si se necesita mantener la distinción, agregar un campo `tipo_profesional` (coordinador/profesional).

---

### 1.3 CU6 No Cubre Modificación y Eliminación de Turnos Externos

CU6 (Registrar Turno Externo) permite crear un turno externo, pero CU7 (Modificar Turno) y CU8 (Eliminar Turno) mencionan comportamiento especial para turnos externos sin un CU específico.

**CU7 dice:** "Si es turno externo el sistema desbloquea el horario anterior y bloquea el nuevo."
**CU8 dice:** "Si era turno externo el sistema desbloquea la disponibilidad del profesional."

Esto implica lógica diferente pero no hay un CU dedicado para turnos externos.

**Solución:** Extender los flujos existentes con un caso especial:

#### CU6 - Registrar Turno Externo (Extendido)

- **Actor Primario:** Secretario
- **Descripción:** Registra compromisos externos del profesional (como audiencias judiciales), bloqueando su disponibilidad.
- **Precondición:** Profesional registrado en el sistema.
- **Flujo de Eventos:**
    1. El Actor ingresa fecha, hora y detalle del evento externo.
    2. El Actor selecciona el profesional involved.
    3. El Sistema verifica disponibilidad del profesional en ese horario.
    4. El Sistema registra el evento.
    5. El Sistema bloquea la disponibilidad del profesional en ese horario.
    6. El Sistema notifica al profesional.
- **Postcondición:** Evento externo registrado. Disponibilidad del profesional bloqueada. Profesional notificado.
- **Flujo Alternativo:** Si no hay disponibilidad -> el sistema informa y solicita otro horario.

**Nota:** Agregar validación específica en CU7 y CU8 para turnos externos (ya mencionado pero sin CU dedicado).

---

## 2. Falencias Funcionales (Casos de Uso Faltantes)

### 2.1 Falta: Búsqueda y Filtrado General

No existe un caso de uso genérico de búsqueda. Los CUs mencionan "buscar por DNI o nombre" pero no hay un CU dedicado.

**Solución:** Crear nuevo caso de uso:

#### CU22 - Buscar Entidades

- **Actor Primario:** Cualquier actor autenticado
- **Descripción:** Permite buscar y filtrar clientes, procesos, turnos y reportes según diversos criterios.
- **Precondición:** El actor debe estar autenticado.
- **Flujo de Eventos:**
    1. El Actor accede al módulo de búsqueda.
    2. El Actor selecciona el tipo de entidad a buscar (cliente/proceso/turno/reporte).
    3. El Actor ingresa criterios de búsqueda (DNI, nombre, estado, fecha, etc.).
    4. El Sistema devuelve resultados filtrados.
- **Postcondición:** Resultados de búsqueda mostrados.

---

### 2.2 Falta: Notificar Decisión al Cliente

CU13 (Admisión de Cliente) notifica al secretario cuando se decide aceptar o rechazar un caso, pero **no hay notificación al cliente**.

El flujo del sistema (sección 1) dice: "Una vez tomada la decisión de aceptar o rechazar el caso, se notificará a la secretaría." Pero el cliente debería ser informado también.

**Solución:** Extender CU13:

En el **Flujo de Eventos** de CU13, agregar paso 4b:
> 4b. El Sistema notifica al cliente sobre la decisión (admitido/rechazado) y en caso de rechazo, incluye el motivo.

En el **Flujo Alternativo** de CU13, agregar:
> Si el caso es rechazado -> el sistema solicita el motivo del rechazo y lo registra antes de notificar.

**Adicionalmente**, crear:

#### CU23 - Registrar Motivo de Rechazo

- **Actor Primario:** Coordinador
- **Descripción:** Permite registrar el motivo por el cual se rechaza un caso durante la admisión.
- **Precondición:** Caso en evaluación.
- **Flujo de Eventos:**
    1. El Actor selecciona "rechazar caso".
    2. El Sistema solicita el motivo del rechazo (selección de motivo predefinido o texto libre).
    3. El Actor ingresa el motivo.
    4. El Sistema guarda el motivo junto con la decisión de rechazo.
- **Postcondición:** Motivo de rechazo registrado en el sistema.

---

### 2.3 Falta: Cambiar Profesional Asignado

CU14 asigna un profesional, pero no hay caso de uso para reasignar si el profesional no está disponible o necesita ser cambiado.

**Solución:** Crear nuevo caso de uso:

#### CU24 - Reasignar Profesional

- **Actor Primario:** Coordinador
- **Descripción:** Permite cambiar el profesional asignado a un proceso, definiendo nuevos honorarios y modalidad de pago si corresponde.
- **Precondición:** Proceso con profesional asignado.
- **Flujo de Eventos:**
    1. El Actor busca el proceso por cliente o número de proceso.
    2. El Sistema muestra los datos del proceso incluyendo el profesional actualmente asignado.
    3. El Actor selecciona "reasignar profesional".
    4. El Sistema muestra lista de profesionales disponibles según especialidad.
    5. El Actor selecciona el nuevo profesional.
    6. El Sistema verifica disponibilidad.
    7. El Actor confirma la reasignación y define nuevos honorarios y modalidad de pago si cambian.
    8. El Sistema actualiza la relación proceso-profesional.
    9. El Sistema notifica al profesional anterior y al nuevo profesional.
- **Postcondición:** Nuevo profesional asignado al proceso. Profesionales notificados.

---

### 2.4 Falta: Consultar Reportes

CU18 crea reportes "visibles para coordinador y director general", pero no existe un caso de uso para que estos actores consulten los reportes.

**Solución:** Crear nuevo caso de uso:

#### CU25 - Consultar Reportes de Proceso

- **Actor Primario:** Coordinador / Director General
- **Descripción:** Permite consultar los reportes generados para un proceso específico. Solo consulta; no pueden editar ni eliminar reportes.
- **Precondición:** El actor debe tener permisos de consulta (coordinador o director general).
- **Flujo de Eventos:**
    1. El Actor accede al módulo de reportes.
    2. El Actor puede buscar por proceso, rango de fechas o profesional.
    3. El Sistema muestra lista de reportes matching criteria.
    4. El Actor selecciona un reporte.
    5. El Sistema muestra el contenido completo del reporte.
- **Postcondición:** Reporte consultado y legible.

---

### 2.5 Falta: Editar Reporte

Solo existen CU18 (registrar) y CU20 (eliminar) para reportes. No hay caso de uso para editar un reporte ya creado.

**Solución:** Crear nuevo caso de uso:

#### CU26 - Editar Reporte

- **Actor Primario:** Profesional
- **Descripción:** Permite modificar el contenido de un reporte ya registrado.
- **Precondición:** El reporte debe existir en el sistema. El actor debe ser el creador del reporte.
- **Flujo de Eventos:**
    1. El Actor busca y selecciona el reporte a editar.
    2. El Sistema muestra el contenido actual del reporte.
    3. El Actor modifica el contenido.
    4. El Sistema valida los datos ingresados.
    5. El Actor confirma los cambios.
    6. El Sistema guarda los cambios y actualiza la fecha de modificación.
- **Postcondición:** Reporte actualizado.

---

### 2.8 Falta: Configurar Notificaciones

No existe un caso de uso genérico de búsqueda. Los CUs mencionan "buscar por DNI o nombre" pero no hay un CU dedicado.

**Solución:** Crear nuevo caso de uso:

#### CU28 - Buscar Entidades

- **Actor Primario:** Cualquier actor autenticado
- **Descripción:** Permite buscar y filtrar clientes, procesos, turnos y reportes según diversos criterios.
- **Precondición:** El actor debe estar autenticado.
- **Flujo de Eventos:**
    1. El Actor accede al módulo de búsqueda.
    2. El Actor selecciona el tipo de entidad a buscar (cliente/proceso/turno/reporte).
    3. El Actor ingresa criterios de búsqueda (DNI, nombre, estado, fecha, etc.).
    4. El Sistema devuelve resultados filtrados.
- **Postcondición:** Resultados de búsqueda mostrados.

---

### 2.7 Falta: Consultar Auditoría

Tienen OwenIt Auditing instalado pero no hay caso de uso para consultar el historial de cambios.

**Solución:** Crear nuevo caso de uso:

#### CU29 - Consultar Historial de Auditoría

- **Actor Primario:** Director General / Coordinador
- **Descripción:** Permite consultar el historial de cambios realizados en cualquier entidad del sistema.
- **Precondición:** El actor debe tener permisos de auditoría (director general o coordinador).
- **Flujo de Eventos:**
    1. El Actor accede al módulo de auditoría.
    2. El Actor selecciona el tipo de entidad y el ID específico.
    3. El Sistema muestra el historial de cambios (fecha, usuario, old_values, new_values).
    4. El Actor puede filtrar por rango de fechas o tipo de usuario.
- **Postcondición:** Historial de auditoría consultado.

---

### 2.8 Falta: Configurar Notificaciones

El documento menciona Brevo (email y WhatsApp) pero no hay caso de uso para gestionar plantillas, canales o configuración de notificaciones.

**Solución:** Crear nuevo caso de uso:

#### CU30 - Configurar Notificaciones

- **Actor Primario:** Director General / Coordinador
- **Descripción:** Permite gestionar plantillas de notificación, canales habilitados (email/WhatsApp/SMS) y configuración general de notificaciones.
- **Precondición:** El actor debe tener permisos de administrador.
- **Flujo de Eventos:**
    1. El Actor accede al módulo de configuración de notificaciones.
    2. El Actor puede crear/editar plantillas de notificación por tipo (recordatorio turno, decisión admisión, cambio de estado, etc.).
    3. El Actor puede habilitar/deshabilitar canales por tipo de notificación.
    4. El Sistema guarda la configuración.
- **Postcondición:** Plantillas y canales configurados.

---

## 3. Falencias de Claridad

### 3.1 CU3 - Eliminar Cliente

**Problema:** Dice "no tener procesos activos" pero no define qué pasa con los procesos finalizados o en espera.

**Resuelto:** Eliminación lógica (soft delete). Nunca se realiza hard delete. Al eliminar un cliente, solo se marca como eliminado (`deleted_at`), manteniendo la integridad referencial.

---

### 3.2 CU9 - Cargar Documentación

**Problema:** Dice "verifica formato correcto" pero no especifica formatos ni límite de tamaño.

**Resuelto:** Formato válido: PDF. Límite de tamaño: ~20MB (equivalente a ~20 páginas/carillas de PDF).

---

### 3.3 CU12 - Registrar Comprobante de Pago

**Problema:** Dice "descargado de ARCA" pero no hay especificación de la integración.

**Nota:** La integración con ARCA es **manual**. El secretary descarga el PDF de ARCA (fuera del sistema) y luego lo sube mediante CU12. No hay conexión automática con la API de ARCA.

**CU12 Simplificado:**

- **Actor Primario:** Secretario
- **Descripción:** Permite registrar el comprobante de pago descargado de ARCA, asociándolo al cliente correspondiente.
- **Precondición:** Cliente registrado en el sistema.
- **Flujo de Eventos:**
    1. El Actor descarga manualmente el comprobante de ARCA (fuera del sistema).
    2. El Actor sube el PDF del comprobante al sistema.
    3. El Actor asocia el comprobante al cliente.
    4. El Sistema verifica el formato del archivo (PDF).
    5. El Sistema guarda el comprobante.
    6. El Sistema notifica el registro exitoso.
- **Postcondición:** Comprobante de pago registrado y asociado al cliente.

---

### 3.4 CU13 - Admisión de Cliente

**Problema:** No hay campo para registrar el motivo del rechazo.

**Solución:** Agregar campo `motivo_rechazo` en la entidad CLIENTE (o en PROCESO) y crear CU23 (Registrar Motivo de Rechazo) como se propuso en 2.2.

---

### 3.5 CU15 - Consultar Legajo

**Problema:** Solo el profesional puede consultar, pero el coordinador y director general también necesitan acceso para supervisión.

**Solución:** Modificar CU15:

- **Actores Primarios:** Profesional / Coordinador / Director General
- Cambiar precondición: "El profesional debe estar asignado al caso" → "El actor debe tener permisos de consulta y estar asignado al caso (si es profesional) o tener rol de coordinador/director general."

---

### 3.6 CU21 - Gestionar Usuarios

**Problema:** No menciona gestión de permisos, solo roles. Con Spatie Permission, ¿quién asigna permisos específicos?

**Resuelto:** Roles fijos. Los roles son estáticos (definidos en código via seeder). Solo se asignan usuarios a roles existentes. CU21 manipula `model_has_roles` pero no hace CRUD de roles.

---

## 4. Resumen de Casos de Uso Propuestos

### Casos de Uso Nuevos

| Nuevo CU | Descripción |
| --- | --- |
| CU22 | Buscar Entidades |
| CU23 | Registrar Motivo de Rechazo |
| CU24 | Reasignar Profesional |
| CU25 | Consultar Reportes de Proceso |
| CU26 | Editar Reporte |
| CU27 | Gestionar Disponibilidad |
| CU28 | Consultar Historial de Auditoría |
| CU29 | Configurar Notificaciones |

### Casos de Uso Modificados

| CU Modificado | Cambio |
| --- | --- |
| CU3 | Clarificar precondiciones sobre procesos finalizados y agregar soft delete |
| CU4 y CU5 | Unificar en un solo CU4 con parámetro de tipo de profesional |
| CU9 | Especificar formatos válidos, límites de tamaño y manejo de archivos corruptos |
| CU12 | Simplificar — solo subir PDF y descripción opcional (proceso manual con ARCA) |
| CU13 | Extender para notificar al cliente y registrar motivo de rechazo |
| CU15 | Permitir acceso a coordinador y director general |
| CU21 | Roles fijos — solo manipulación de model_has_roles, sin CRUD de roles |

### Re-numeración Sugerida

| Original | Propuesto |
| --- | --- |
| CU4 | CU4 (unificado) |
| CU5 | Eliminar (fusionado con CU4) |
| CU19 | Eliminado intencionalmente |

### Total de CUs

- **Originales documentados:** 20 (con gap en CU19)
- **Propuestos nuevos:** 9
- **Modificados:** 6
- **Eliminados por fusión:** 1
- **Total proyectado:** ~28 CUs

---

## 5. Decisiones Confirmadas

Las siguientes decisiones fueron tomadas y quedan resueltas:

| # | Pregunta | Respuesta |
| --- | --- | --- |
| 1 | ¿Un cliente puede tener múltiples procesos simultáneos? | **Sí** — Relación 1:N (un cliente puede tener múltiples procesos) |
| 2 | ¿Un proceso puede involucrar múltiples profesionales? | **Sí** — Relación N:M (tabla intermedia `proceso_profesional`) |
| 3 | ¿La eliminación es física o lógica? | **Lógica (soft delete)** — Nunca se realiza hard delete |
| 4 | ¿Existe CU19? | **Eliminado intencionalmente** |
| 5 | ¿Los roles de Spatie son fijos o editables? | **Fijos** — Definidos en código, solo se asignan usuarios a roles existentes |
| 6 | ¿Límite de tamaño para documentos? | **~20MB** (~20 páginas/carillas de PDF) |
| 7 | ¿Formatos válidos para documentos? | **Solo PDF** |
| 8 | ¿Coordinador y director general pueden editar reportes? | **No** — Solo consultarlos |
