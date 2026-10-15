# Gestión jurídica

## Purpose

Definir el comportamiento funcional vigente del sistema jurídico, con la matriz online Aequitas V2 como autoridad de casos de uso y permisos. Esta especificación formaliza los 41 casos de uso actuales y conserva las decisiones complementarias aprobadas que no amplían ni contradicen sus roles.

## Requirements

### Requirement: Limitar el acceso a empleados autenticables y sus roles

El sistema MUST permitir el acceso únicamente a empleados autenticados con los roles Secretario, Profesional, Coordinador, Directivo o Administrador. El Administrador MUST estar autorizado en los 41 casos de uso. El cliente MUST ser solo un dato administrativo y MUST NOT tener credenciales ni acceso al sistema.

#### Scenario: CU 31 - Iniciar y cerrar sesión

- **WHEN** un empleado con cualquiera de los cinco roles inicia o cierra sesión
- **THEN** el sistema autentica la cuenta interna y permite terminar la sesión; un cliente no puede autenticarse

#### Scenario: Visitante abre la raíz del portal

- **WHEN** una persona no autenticada solicita `/`
- **THEN** el sistema la redirige al inicio de sesión en `/login`

#### Scenario: Empleado autenticado abre la raíz del portal

- **WHEN** un empleado autenticado solicita `/`
- **THEN** el sistema lo redirige al dashboard operativo en `/dashboard`
- **AND** el dashboard muestra datos y destinos correspondientes a sus permisos efectivos

#### Scenario: El Administrador conserva el alcance de superadministrador

- **WHEN** el Administrador ejecuta cualquiera de los casos de uso de la matriz
- **THEN** el sistema lo autoriza conforme a su condición de superadministrador

### Requirement: Permitir que cada empleado actualice su propia contraseña y perfil

El sistema MUST permitir que Secretario, Profesional, Coordinador, Directivo y Administrador actualicen su propia contraseña o perfil. La operación MUST aplicarse a la cuenta autenticada.

#### Scenario: CU 32 - Cambiar contraseña o perfil

- **WHEN** un empleado autenticado actualiza su contraseña o perfil
- **THEN** el cambio se aplica a su propia cuenta y no concede acceso al perfil de otro usuario

### Requirement: Presentar navegación acorde a rol y permisos

Las navegaciones autenticadas MUST ofrecer los destinos operativos implementados que correspondan a los permisos del usuario según la matriz Aequitas V2. Cada enlace operativo MUST ocultarse cuando el usuario no tenga el permiso exigido por la ruta. Las variantes de navegación MUST conservar el mismo conjunto de destinos autorizados. Los accesos comunes de inicio, perfil, guía técnica y cierre de sesión MUST seguir disponibles para los usuarios internos autenticados. Ocultar un enlace MUST NOT reemplazar la autorización del servidor para solicitudes directas.

#### Scenario: Empleado navega funciones autorizadas

- **WHEN** Secretario, Profesional, Coordinador, Directivo o Administrador inicia sesión
- **THEN** la navegación presenta los destinos implementados que corresponden a sus permisos en la matriz
- **AND** todas las variantes de navegación muestran los mismos destinos operativos autorizados

#### Scenario: Empleado no autorizado solicita una función

- **WHEN** un empleado sigue una URL directa a una función que su rol no tiene autorizada
- **THEN** el servidor deniega la solicitud aunque el usuario haya construido la URL sin usar la navegación

### Requirement: Gestionar clientes y el alta inicial

El sistema MUST permitir al Secretario y al Administrador registrar, modificar y dar de baja lógicamente clientes. El registro MUST crear un proceso inicial de Consultoría en estado pendiente y un turno inicial con el Coordinador. La baja MUST conservar los datos históricos y MUST rechazarse si el cliente tiene procesos activos.

#### Scenario: CU 1 - Registrar Cliente

- **WHEN** Secretario o Administrador registra un cliente con datos válidos, existe la configuración inicial requerida y hay disponibilidad para el Coordinador
- **THEN** el sistema crea el cliente, un proceso de Consultoría pendiente y un turno inicial con un Coordinador en una operación consistente
- **AND** crea el registro interno mínimo dirigido al Coordinador, que no constituye la entrega de CU 35 ni del módulo final de Notificaciones

#### Scenario: CU 1 - Conflicto en el turno inicial

- **WHEN** Secretario o Administrador registra un cliente y el Coordinador ya tiene un turno activo en esa fecha y hora, o un compromiso externo activo en esa fecha
- **THEN** el sistema informa el conflicto en el campo de fecha y hora inicial y no persiste cliente, proceso, turno ni registro interno

#### Scenario: CU 1 - Horario liberado por cancelación

- **WHEN** Secretario o Administrador registra un cliente en la fecha y hora de un turno cancelado del Coordinador, sin otro conflicto activo
- **THEN** el sistema completa el alta inicial y conserva el turno cancelado como historial

#### Scenario: CU 2 - Modificar Cliente

- **WHEN** Secretario o Administrador modifica los datos personales o de contacto de un cliente
- **THEN** el sistema conserva el mismo cliente y guarda los datos actualizados

#### Scenario: CU 3 - Eliminar Cliente

- **WHEN** Secretario o Administrador solicita la baja lógica de un cliente sin procesos activos
- **THEN** el sistema conserva el registro con baja lógica y lo excluye de las consultas operativas
- **AND** si quedan procesos activos, el sistema rechaza la baja

#### Scenario: CU 28 - Listar y Filtrar Clientes

- **WHEN** Secretario, Coordinador, Directivo o Administrador consulta clientes
- **THEN** el sistema permite buscar, filtrar por estado de admisión y paginar resultados

### Requirement: Gestionar procesos, admisión, asignaciones y consulta autorizada

El proceso MUST conservar su cliente, servicio, coordinador, profesional, fechas, tipo, descripción, honorarios y motivo de rechazo. El Profesional MUST consultar y operar únicamente procesos que tenga asignados. Las mutaciones MUST aplicar los permisos por caso de uso y conservar las transiciones de estado.

#### Scenario: CU 15 - Admitir un proceso

- **WHEN** Coordinador o Administrador resuelve la admisión de un proceso pendiente
- **THEN** el sistema registra la decisión y actualiza el proceso según el flujo de admisión

#### Scenario: CU 15.1 - Registrar motivo de rechazo

- **WHEN** Coordinador o Administrador rechaza un proceso pendiente
- **THEN** el sistema exige un motivo no vacío, conserva la causal y actualiza el estado a rechazado

#### Scenario: CU 16 - Asignar Profesional

- **WHEN** Secretario, Coordinador o Administrador asigna un profesional a un proceso
- **THEN** el sistema valida que el profesional esté activo y sea compatible con el servicio o especialidad del proceso, y fija los honorarios con el costo de referencia vigente del servicio

#### Scenario: CU 17 - Reasignar Profesional

- **WHEN** Secretario, Coordinador o Administrador reasigna el profesional de un proceso
- **THEN** el sistema valida la compatibilidad del nuevo profesional y actualiza los honorarios con el costo de referencia vigente del servicio

#### Scenario: CU 18 - Consultar Legajo

- **WHEN** un Profesional asignado, Coordinador, Directivo o Administrador consulta el legajo
- **THEN** el sistema muestra la información autorizada por rol y asignación
- **AND** el Profesional solo recibe los procesos que tiene asignados
- **AND** la consulta del legajo no concede al Directivo visualización CU 12 ni descarga CU 19 de documentos

#### Scenario: CU 20 - Actualizar Estado de Proceso

- **WHEN** el Profesional asignado, Coordinador o Administrador actualiza un estado permitido
- **THEN** el sistema guarda la transición y conserva su historial

#### Scenario: CU 29 - Listar y Filtrar Procesos

- **WHEN** Secretario, Profesional, Coordinador, Directivo o Administrador consulta la bandeja de procesos
- **THEN** el sistema permite buscar y filtrar por estado, cliente y profesional asignado, y paginar resultados
- **AND** el Profesional solo recibe procesos asignados
- **AND** se conservan los filtros adicionales ya aprobados para servicio, coordinador y rango de fecha de inicio

#### Scenario: CU 30 - Consultar Historial de Estados del Proceso

- **WHEN** Secretario, Profesional, Coordinador, Directivo o Administrador consulta el historial de estados
- **THEN** el sistema muestra el estado anterior, estado nuevo, fecha, usuario interviniente y motivo
- **AND** el Profesional solo consulta el historial de procesos que tiene asignados

#### Scenario: CU 38 - Crear Proceso Adicional

- **WHEN** Secretario o Administrador crea un proceso para un cliente ya registrado
- **THEN** el sistema crea un nuevo proceso asociado al cliente existente, sin duplicar el cliente y sin generar un turno inicial automático
- **AND** el proceso se crea sin profesional asignado; cualquier `profesional_id` recibido en la petición se rechaza
- **AND** el proceso comienza pendiente y continúa por el flujo de admisión vigente

#### Scenario: Asignar al proceso adicional mediante CU 16

- **WHEN** Secretario, Coordinador o Administrador asigna posteriormente un profesional al proceso adicional
- **THEN** el sistema valida que el profesional esté activo, tenga rol Profesional y esté habilitado para el servicio del proceso
- **AND** el sistema fija los honorarios con el costo de referencia vigente del servicio en la misma operación
- **AND** una asignación inválida no modifica el proceso

### Requirement: Administrar turnos y consultar la agenda

Solo Secretario y Administrador MUST poder crear, modificar o cancelar turnos. El sistema MUST impedir conflictos de disponibilidad, conservar los turnos cancelados como historial y liberar la disponibilidad anterior al reprogramar.

#### Scenario: CU 4 - Agendar Turno Interno

- **WHEN** Secretario o Administrador agenda un turno interno válido
- **THEN** el sistema conserva la cita entre cliente y profesional o Coordinador
- **AND** la integración de avisos queda para el módulo independiente de Notificaciones de última fase

#### Scenario: CU 4.1 - Agendar Turno de Seguimiento

- **WHEN** Secretario o Administrador agenda un turno de seguimiento
- **THEN** el sistema vincula el turno a un legajo o proceso activo

#### Scenario: CU 5 - Agendar Turno Externo

- **WHEN** Secretario o Administrador registra un compromiso externo con su detalle
- **THEN** el sistema guarda el detalle y bloquea la jornada completa del profesional para esa fecha

#### Scenario: CU 6 - Modificar Turno

- **WHEN** Secretario o Administrador reprograma un turno interno a una disponibilidad válida
- **THEN** el sistema guarda la nueva fecha y hora y libera la disponibilidad anterior

#### Scenario: CU 6.1 - Modificar Turno Externo

- **WHEN** Secretario o Administrador reprograma un turno externo
- **THEN** el sistema actualiza la fecha y libera el bloqueo anterior o asigna el bloqueo a la nueva jornada

#### Scenario: CU 7 - Eliminar Turno mediante cancelación

- **WHEN** Secretario o Administrador cancela un turno
- **THEN** el sistema conserva el registro con estado cancelado, mantiene su historial y libera la disponibilidad

#### Scenario: CU 8 - Ver Agenda de un Profesional

- **WHEN** Secretario, Profesional, Coordinador o Administrador consulta la agenda
- **THEN** el sistema muestra los turnos y bloqueos definidos para el profesional y período autorizados; Directivo no recibe CU 8
- **AND** el Profesional queda limitado a su propia agenda incluso si manipula filtros
- **AND** los rangos consultables son inclusivos y no exceden 90 días; no se infieren horas libres ni duraciones no definidas
- **AND** los turnos cancelados pueden mostrarse como historial, pero no ocupan disponibilidad

### Requirement: Conservar y autorizar los documentos del legajo

El sistema MUST aceptar únicamente archivos PDF de hasta 20 MB. La carga, reemplazo y eliminación lógica corresponden a Secretario y Administrador; la visualización corresponde a Secretario, Profesional, Coordinador y Administrador; la descarga corresponde al Profesional asignado, Coordinador y Administrador.

#### Scenario: CU 9 - Cargar Documentación

- **WHEN** Secretario o Administrador carga un archivo PDF de hasta 20 MB en el legajo
- **THEN** el sistema valida tipo y tamaño y conserva la referencia al archivo

#### Scenario: CU 10 - Reemplazar Documentación

- **WHEN** Secretario o Administrador reemplaza un documento por un PDF válido
- **THEN** el documento conserva su identidad, referencia e historial de versiones

#### Scenario: CU 11 - Eliminar Documentación

- **WHEN** Secretario o Administrador elimina u oculta un documento
- **THEN** el sistema conserva su referencia, historial y auditoría, y no elimina automáticamente el archivo físico

#### Scenario: CU 12 - Visualizar Documentación

- **WHEN** Secretario, Profesional, Coordinador o Administrador visualiza documentación autorizada
- **THEN** el sistema permite visualizar el contenido según el alcance del rol y del proceso
- **AND** Directivo no recibe CU 12 por el hecho de consultar un legajo mediante CU 18

#### Scenario: CU 19 - Descargar Documentación

- **WHEN** el Profesional asignado, Coordinador o Administrador descarga documentación autorizada
- **THEN** el sistema entrega el archivo solo después de verificar el permiso y el alcance del proceso

### Requirement: Registrar y consultar comprobantes sin ampliar CU 13 y CU 14

El sistema MUST permitir registrar comprobantes PDF descargados manualmente desde ARCA y consultarlos según la matriz. MUST NOT integrar automáticamente con ARCA ni ofrecer baja u ocultación de comprobantes porque CU 13 y CU 14 no autorizan esa operación.

#### Scenario: CU 13 - Registrar Comprobante de Pago

- **WHEN** Secretario o Administrador registra un comprobante PDF descargado desde ARCA
- **THEN** el sistema conserva el comprobante vinculado sin realizar una integración automática con ARCA

#### Scenario: CU 14 - Ver Comprobante de Pago

- **WHEN** Secretario, Coordinador, Directivo o Administrador consulta un comprobante vinculado
- **THEN** el sistema permite su consulta y visualización autorizadas
- **AND** el sistema no ofrece baja u ocultación de comprobantes ni asigna un permiso para ello

### Requirement: Gestionar reportes con autoría y alcance

El sistema MUST asociar cada reporte al proceso y al Profesional actualmente asignado como autor. Profesional asignado y Administrador MUST poder registrar reportes; Administrador MUST quedar identificado por separado cuando registra en nombre del Profesional. CU22/CU23 MUST permitir editar o dar de baja lógicamente únicamente al Profesional autor o Administrador. Las consultas operativas MUST excluir reportes dados de baja y conservar sus versiones. El Profesional MUST consultar solo sus propios reportes dentro de procesos que tiene asignados actualmente. Coordinador, Directivo y Administrador MUST consultar reportes e historial dentro del alcance de sus legajos. Secretario MUST NOT consultar reportes ni su historial.

#### Scenario: CU 21 - Registrar como Profesional asignado

- **WHEN** el Profesional asignado registra un reporte con contenido y fecha
- **THEN** el sistema asocia el reporte al proceso y al Profesional autor y registra al Profesional como actor

#### Scenario: CU 21 - Administrador registra para el Profesional asignado

- **WHEN** un Administrador registra un reporte para un proceso con Profesional asignado
- **THEN** el Profesional asignado queda como autor y el Administrador queda identificado como quien lo registró
- **AND** el Profesional asignado puede consultar, editar y dar de baja su reporte según CU22/CU23

#### Scenario: CU 21 - Rechazar registro sin profesional asignado

- **WHEN** un Administrador intenta registrar un reporte en un proceso sin Profesional asignado
- **THEN** el sistema rechaza el registro y no crea un reporte sin autor profesional

#### Scenario: CU 22 - Editar reporte

- **WHEN** el Profesional autor o Administrador edita un reporte
- **THEN** el sistema conserva la autoría y registra el historial del cambio

#### Scenario: CU 23 - Eliminar reporte

- **WHEN** el Profesional autor o Administrador elimina un reporte
- **THEN** el sistema aplica baja lógica, mantiene el historial y excluye el reporte de consultas operativas

#### Scenario: CU 24 - Consultar reportes de proceso

- **WHEN** Profesional, Coordinador, Directivo o Administrador consulta reportes del proceso
- **THEN** Profesional ve solo sus reportes dentro de procesos asignados actualmente y los demás roles acceden solo mediante legajos permitidos
- **AND** Secretario, usuarios sin alcance y solicitudes directas fuera de CU24 reciben acceso denegado

### Requirement: Administrar usuarios según CU 25 a CU 27

El sistema MUST permitir a Directivo y Administrador agregar, modificar y dar de baja lógicamente usuarios. La baja MUST rechazarse mientras el usuario tenga procesos activos y MUST conservar trazabilidad.

#### Scenario: CU 25 - Agregar Nuevo Usuario

- **WHEN** Directivo o Administrador agrega un usuario interno y asigna su rol
- **THEN** el sistema crea la cuenta autenticable con los datos y rol admitidos

#### Scenario: CU 26 - Modificar Usuario

- **WHEN** Directivo o Administrador modifica los datos o rol de un usuario
- **THEN** el sistema guarda los cambios autorizados

#### Scenario: CU 27 - Eliminar Usuario

- **WHEN** Directivo o Administrador solicita la baja lógica de un usuario sin procesos activos
- **THEN** el sistema conserva la cuenta como baja lógica y la excluye de consultas operativas
- **AND** si tiene procesos activos, el sistema rechaza la baja

### Requirement: Gestionar servicios y asignaciones de especialidad

Directivo y Administrador MUST poder gestionar servicios. Coordinador, Directivo y Administrador MUST poder vincular a profesionales con los servicios que pueden atender.

#### Scenario: CU 33 - Gestionar Servicios

- **WHEN** Directivo o Administrador crea, modifica o da de baja lógicamente un servicio
- **THEN** el sistema conserva sus datos y costo de referencia para las operaciones autorizadas

#### Scenario: CU 34 - Asignar Especialidad o Servicio a Profesional

- **WHEN** Coordinador, Directivo o Administrador asigna servicios o especialidades a un profesional
- **THEN** el sistema conserva la relación que determina qué servicios puede atender el profesional

### Requirement: Consultar el registro del módulo final de Notificaciones

El módulo final MUST conservar registros auditables con canal, mensaje, destinatario, fecha, estado y resultado. MUST contemplar notificaciones internas, correo y WhatsApp. El módulo MUST entregarse como componente independiente en la última fase y MUST NOT volver dependientes de un proveedor externo los flujos de clientes, procesos o turnos. Brevo es candidato y sus costos y condiciones vigentes MUST revisarse antes de confirmar proveedor.

#### Scenario: CU 35 - Consultar Registro de Notificaciones

- **WHEN** Secretario, Coordinador, Directivo o Administrador consulta el registro de notificaciones en la fase final
- **THEN** el sistema presenta los registros auditables autorizados y sus datos de canal, mensaje, destinatario, fecha, estado y resultado
- **AND** la integración de eventos del núcleo ocurre después de validar el módulo por separado

### Requirement: Administrar estados de proceso y categorías documentales

Coordinador y Administrador MUST poder administrar estados de proceso y categorías documentales. El sistema MUST impedir desactivar un estado o categoría si la operación deja datos activos sin una transición válida.

#### Scenario: CU 36 - Gestionar Estados de Proceso

- **WHEN** Coordinador o Administrador crea, edita, ordena, activa o desactiva un estado
- **THEN** el sistema conserva la configuración y no permite una desactivación que deje procesos activos sin transición válida

#### Scenario: CU 37 - Gestionar Categorías de Documentos

- **WHEN** Coordinador o Administrador crea, edita, activa, desactiva o da de baja lógicamente una categoría
- **THEN** el sistema conserva su asociación histórica y los documentos y archivos relacionados, sin dejar datos activos sin transición válida
- **AND** la restauración complementaria conserva el valor activo o inactivo previo y usa la autorización existente de CU 37

### Requirement: Conservar las bajas y excluirlas de la operación vigente

Clientes, usuarios, procesos, servicios, estados, categorías y reportes MUST admitir baja lógica y MUST conservar su trazabilidad. Los turnos MUST conservarse con estado cancelado y los documentos MUST conservar su referencia, historial y auditoría sin eliminar automáticamente el archivo físico. Las consultas operativas MUST excluir los registros dados de baja, salvo vistas explícitas de auditoría o restauración. CU 13 y CU 14 MUST NOT habilitar la baja de comprobantes.

#### Scenario: Registro con baja lógica

- **WHEN** una entidad con baja lógica se consulta desde una vista operativa
- **THEN** el sistema la excluye de los resultados operativos y la conserva para auditoría o restauración autorizada

#### Scenario: Baja de proceso, servicio, estado, categoría o reporte

- **WHEN** un usuario autorizado da de baja lógicamente un proceso, servicio, estado, categoría o reporte
- **THEN** el sistema conserva sus relaciones y datos históricos y aplica las restricciones de seguridad específicas de esa entidad

#### Scenario: Consulta de un turno cancelado o documento eliminado

- **WHEN** un usuario autorizado consulta el historial de turnos o documentos
- **THEN** el sistema conserva la evidencia del turno cancelado o del documento eliminado, incluido el archivo físico documental cuando corresponda

### Requirement: Mantener las decisiones aprobadas de interfaz y consistencia

La aplicación MUST ser accesible desde un navegador y las vistas MUST seguir el estándar aprobado de Tailwind CSS, Alpine.js y Livewire dentro de la arquitectura MVC existente. Este estándar MUST NOT requerir una API separada. Las operaciones relacionadas MUST conservar persistencia consistente, las autorizaciones MUST comprobarse en interfaz y servidor, y los cambios sensibles y transiciones MUST quedar auditados.

#### Scenario: Interfaz de gestión de servicios

- **WHEN** un usuario autorizado administra servicios
- **THEN** la interfaz principal está disponible en /servicios con Blade, Livewire, Alpine.js y Tailwind CSS, y conserva /servicios-viejo como referencia

#### Scenario: Operación protegida y consistente

- **WHEN** un usuario solicita una operación sensible desde la interfaz o una ruta directa
- **THEN** el servidor vuelve a comprobar autorización, valida los datos y conserva auditoría y consistencia de las escrituras relacionadas
