# Trabajo Práctico Integrador
### "Tecnicatura Superior de Desarrollo de Software"

**Integrantes:** Flores Lucas, Espinola Yamila, Gonzalez Lucia  
**Profesor/es:** Villalba Carlos  
**Instituto:** Superior Roque Gonzalez  
**Año:** 2026

---

## 1. Descripción del Escenario

El estudio jurídico (en adelante, la organización) se dedica a brindar servicios profesionales en las áreas Civil, Comercial y de Familia. Actualmente, la gestión interna se realiza de manera manual o mediante herramientas dispersas, como planillas, archivos físicos y comunicaciones informales. Este enfoque, si bien permitió operar en etapas iniciales, genera dificultades en la organización de la información, duplicación de datos, demoras en la atención y una limitada trazabilidad de los procesos.

Entre las principales problemáticas se destacan la falta de centralización de la información de clientes, la dificultad para coordinar turnos entre secretaría, coordinadores y profesionales, el manejo poco estructurado de la documentación y la ausencia de un seguimiento claro de los casos en curso.

Con el objetivo de mejorar la eficiencia operativa, optimizar la atención al cliente y garantizar una adecuada gestión de la información, se propone el desarrollo de un sistema de gestión y administración interna jurídica que permita centralizar todas las actividades del estudio.

En el nuevo sistema, el proceso comenzará cuando un cliente se presente por primera vez en el estudio. En esta instancia, la secretaría registrará sus datos básicos y clasificará el tipo de servicio requerido como "asesoramiento". Posteriormente, se agendará un turno con el coordinador, quien será notificado automáticamente por el sistema.

Durante la consulta, el coordinador abrirá el legajo del cliente, evaluará la viabilidad del caso, definirá el tipo de servicio a prestar, asignará el profesional correspondiente según su especialidad y establecerá los honorarios junto con la modalidad de pago. Una vez tomada la decisión de aceptar o rechazar el caso, se notificará a la secretaría para continuar, o no, con el proceso administrativo.

Luego, la secretaría solicitará al cliente la documentación necesaria según el tipo de servicio definido. Dicha documentación será digitalizada y cargada en el sistema, asegurando su disponibilidad para los profesionales. Además, se agendará un nuevo turno con el profesional designado, quien será notificado, así como también el cliente. En esta etapa, el sistema solo enviará notificaciones, sin permitir aún la confirmación o cancelación por parte del cliente.

El sistema permitirá a los profesionales acceder a la información completa del cliente, incluyendo sus datos, historial de casos y documentación asociada. Asimismo, podrán registrar el avance de cada caso mediante la actualización de estados (iniciado, en proceso, finalizado o en espera) y la generación de actas que documenten las acciones realizadas, facilitando el control por parte de los coordinadores.

En relación con los pagos, la secretaría deberá acceder al sistema externo de ARCA, descargar el comprobante correspondiente y cargarlo en el sistema interno.

El sistema incluirá también un módulo de gestión de turnos que permitirá registrar tanto citas dentro del estudio como compromisos externos, tales como audiencias judiciales. En estos casos, se bloqueará la disponibilidad del profesional para evitar la asignación de nuevos turnos en esos horarios. Además, se generarán recordatorios automáticos previos a cada evento.

Mediante la implementación de esta solución, el estudio logrará centralizar la gestión de clientes, casos, documentación, turnos y pagos, mejorando la organización interna, reduciendo errores operativos y aumentando la eficiencia en la prestación de servicios profesionales.

---

## 2. Requisitos Funcionales (Casos de Uso)

**Actores:** Secretario, Cliente, Profesional, Coordinadores, Directivo

---

### Secretario

#### CU 1 - Registrar Cliente

| Campo | Detalle |
|---|---|
| **Actor Primario** | Secretario |
| **Descripción** | Permite registrar en el sistema a un nuevo cliente, almacenando sus datos personales y de contacto, para poder iniciar la gestión de un servicio. |
| **Precondición** | El cliente no debe existir previamente en el sistema. |

**Flujo de Eventos:**

| # | Acciones del Actor | Respuesta del Sistema |
|---|---|---|
| 1 | Ingresa los datos personales del cliente (nombre, DNI, contacto, dirección) | |
| 2 | | Valida que los datos estén completos y correctos |
| 3 | Confirma el registro | |
| 4 | | Almacena la información del cliente |
| 5 | | Notifica registro exitoso. Se crea proceso con servicio "Consultoría" |

**PostCondición:** Cliente asociado al sistema. Creado Proceso con el servicio "Consultoría".  
**Flujo Alternativo:** Si el cliente ya existe → el sistema informa duplicidad y cancela el registro.

---

#### CU 2 - Modificar Cliente

| Campo | Detalle |
|---|---|
| **Actor Primario** | Secretario |
| **Descripción** | Permite actualizar los datos personales de un cliente ya registrado en el sistema. |
| **Precondición** | El cliente debe existir en el sistema. |

**Flujo de Eventos:**

| # | Acciones del Actor | Respuesta del Sistema |
|---|---|---|
| 1 | Busca al cliente por DNI o nombre | |
| 2 | | Muestra los datos actuales del cliente |
| 3 | Modifica los campos necesarios | |
| 4 | | Valida los datos ingresados |
| 5 | Confirma los cambios | |
| 6 | | Guarda los datos actualizados y notifica éxito |

**PostCondición:** Datos del cliente actualizados en el sistema.  
**Flujo Alternativo:** Si los datos son inválidos → el sistema informa el error y solicita corrección.

---

#### CU 3 - Eliminar Cliente

| Campo | Detalle |
|---|---|
| **Actor Primario** | Secretario |
| **Descripción** | Permite eliminar el registro de un cliente del sistema. |
| **Precondición** | El cliente debe existir en el sistema y no tener procesos activos. |

**Flujo de Eventos:**

| # | Acciones del Actor | Respuesta del Sistema |
|---|---|---|
| 1 | Busca al cliente por DNI o nombre | |
| 2 | | Muestra los datos del cliente |
| 3 | Solicita eliminar el cliente | |
| 4 | | Verifica que no tenga procesos activos |
| 5 | Confirma la eliminación | |
| 6 | | Soft delete del cliente (Eliminación lógica) |

**PostCondición:** Cliente eliminado del sistema.  
**Flujo Alternativo:** Si el cliente tiene procesos activos → el sistema informa que no puede eliminarse.

---

#### CU 4 - Agendar Turno Interno

| Campo | Detalle |
|---|---|
| **Actor Primario** | Secretario |
| **Descripción** | Permite registrar un turno entre el cliente y un profesional (coordinador o profesional). |
| **Precondición** | Cliente registrado en el sistema. Profesional seleccionado. |

**Flujo de Eventos:**

| # | Acciones del Actor | Respuesta del Sistema |
|---|---|---|
| 1 | Ingresa el DNI del cliente o lo selecciona de una lista | |
| 2 | Selecciona el profesional (coordinador o profesional específico) | |
| 3 | Selecciona fecha y hora del turno | |
| 4 | | Verifica disponibilidad del profesional seleccionado |
| 5 | Confirma el turno | |
| 6 | | Registra el turno |
| 7 | | Notifica al profesional y al cliente |

**PostCondición:** Turno registrado. Cliente asociado al turno. Profesional asociado al turno. Involucrados notificados.  
**Flujo Alternativo:** Si no hay disponibilidad → el sistema solicita otra fecha.

---

#### CU 4.1 - Agendar Turno de Seguimiento

| Campo | Detalle |
|---|---|
| **Actor Primario** | Secretario |
| **Descripción** | Permite registrar un turno entre el cliente y un profesional (coordinador o profesional). |
| **Precondición** | Cliente registrado en el sistema. Profesional seleccionado. |

**Flujo de Eventos:**

| # | Acciones del Actor | Respuesta del Sistema |
|---|---|---|
| 1 | Busca y selecciona el legajo o caso activo del cliente | |
| 2 | | Muestra los datos del caso y recupera automáticamente al Profesional asignado al mismo |
| 3 | Selecciona fecha y hora del turno | |
| 4 | | Verifica disponibilidad del profesional seleccionado |
| 5 | Confirma el turno | |
| 6 | | Registra el turno vinculándolo al legajo/caso específico |
| 7 | | Notifica al profesional y al cliente |

**PostCondición:** Turno registrado y vinculado al caso/proceso. Profesional asociado al turno. Involucrados notificados.  
**Flujo Alternativo:** Si no hay disponibilidad → el sistema solicita otra fecha.

---

#### CU 5 - Agendar Turno Externo

| Campo | Detalle |
|---|---|
| **Actor Primario** | Secretario |
| **Descripción** | Registra compromisos externos del profesional (como audiencias judiciales), bloqueando su disponibilidad. |
| **Precondición** | Profesional registrado en el sistema. |

**Flujo de Eventos:**

| # | Acciones del Actor | Respuesta del Sistema |
|---|---|---|
| 1 | Ingresa fecha, hora y detalle del evento externo | |
| 2 | Selecciona el profesional involucrado | |
| 3 | | Verifica la disponibilidad del profesional para ese día completo |
| 4 | | Registra el evento |
| 5 | | Bloquea la disponibilidad del profesional en ese día |
| 6 | | Notifica al profesional |

**PostCondición:** Evento externo registrado. Disponibilidad del profesional bloqueada por el día completo. Profesional notificado.  
**Flujo Alternativo:** Si no hay disponibilidad → el sistema informa y solicita otro horario.

---

#### CU 6 - Modificar Turno

| Campo | Detalle |
|---|---|
| **Actor Primario** | Secretario |
| **Descripción** | Permite modificar la fecha y/o hora de un turno registrado. |
| **Precondición** | El turno debe existir en el sistema. |

**Flujo de Eventos:**

| # | Acciones del Actor | Respuesta del Sistema |
|---|---|---|
| 1 | Busca y selecciona el turno a modificar | |
| 2 | | Muestra los datos actuales del turno |
| 3 | Ingresa la nueva fecha y/o hora | |
| 4 | | Verifica disponibilidad en el nuevo horario |
| 5 | Confirma el cambio | |
| 6 | | Actualiza el turno y notifica a los involucrados |

**PostCondición:** Turno actualizado. Involucrados notificados.  
**Flujo Alternativo:** Si es turno externo → el sistema desbloquea el horario anterior y bloquea el nuevo.

---

#### CU 6.1 - Modificar Turno Externo

| Campo | Detalle |
|---|---|
| **Actor Primario** | Secretario |
| **Descripción** | Permite modificar la fecha y/o hora de un turno externo registrado. |
| **Precondición** | El turno debe existir en el sistema. |

**Flujo de Eventos:**

| # | Acciones del Actor | Respuesta del Sistema |
|---|---|---|
| 1 | Busca y selecciona el turno a modificar | |
| 2 | | Muestra los datos actuales del turno |
| 3 | Ingresa la nueva fecha y/o hora | |
| 4 | | Verifica disponibilidad en el nuevo horario |
| 5 | Confirma el cambio | |
| 6 | | Actualiza el turno, notifica a los involucrados, libera la fecha anterior |

**PostCondición:** Turno actualizado. Libera la fecha anteriormente bloqueada del profesional. Involucrados notificados.  
**Flujo Alternativo:** El sistema desbloquea el horario anterior y bloquea el nuevo.

---

#### CU 7 - Eliminar Turno

| Campo | Detalle |
|---|---|
| **Actor Primario** | Secretario |
| **Descripción** | Permite eliminar un turno registrado en el sistema. |
| **Precondición** | El turno debe existir en el sistema. |

**Flujo de Eventos:**

| # | Acciones del Actor | Respuesta del Sistema |
|---|---|---|
| 1 | Busca y selecciona el turno a eliminar | |
| 2 | Confirma la eliminación | |
| 3 | | Elimina el turno del sistema |
| 4 | | Si era turno externo, desbloquea la disponibilidad del profesional |
| 5 | | Notifica a los involucrados |

**PostCondición:** Turno eliminado. Disponibilidad actualizada si correspondía.

---

#### CU 8 - Ver Agenda de un Profesional

| Campo | Detalle |
|---|---|
| **Actor Primario** | Secretario, Profesional, Coordinador |
| **Descripción** | Permite ver los turnos agendados de un profesional/coordinador. |
| **Precondición** | El actor debe contar con permisos para ver la agenda. Profesional existente en el sistema. |

**Flujo de Eventos:**

| # | Acciones del Actor | Respuesta del Sistema |
|---|---|---|
| 1 | Busca al profesional por DNI o nombre | Verifica permisos del usuario para ver la información |
| 2 | | Se devuelven fechas y horas de los turnos del profesional |

**PostCondición:** Devolver información de fechas y horas de turnos.  
**Flujo Alternativo:** Sin permisos → se devuelve un mensaje de falta de permisos.

---

#### CU 9 - Cargar Documentación

| Campo | Detalle |
|---|---|
| **Actor Primario** | Secretario |
| **Descripción** | Permite cargar los documentos entregados por el cliente al sistema, asociándolos a su legajo. |
| **Precondición** | Cliente registrado y legajo creado. |

**Flujo de Eventos:**

| # | Acciones del Actor | Respuesta del Sistema |
|---|---|---|
| 1 | Abre el legajo del cliente | |
| 2 | Carga los documentos | |
| 3 | | Verifica el formato correcto de los documentos (Formato válido: PDF. Límite de tamaño: ~20MB) |
| 4 | | Guarda los archivos |
| 5 | | Notifica el guardado exitoso |

**PostCondición:** Documentación almacenada y asociada al cliente.  
**Flujo Alternativo:** Documento con formato no compatible → el sistema informa el error.

---

#### CU 10 - Reemplazar Documentación

| Campo | Detalle |
|---|---|
| **Actor Primario** | Secretario |
| **Descripción** | Permite reemplazar un documento existente por una nueva versión, manteniendo la referencia en el legajo. |
| **Precondición** | El documento debe existir en el sistema. |

**Flujo de Eventos:**

| # | Acciones del Actor | Respuesta del Sistema |
|---|---|---|
| 1 | Busca y selecciona el documento a reemplazar | |
| 2 | Carga el nuevo archivo | |
| 3 | | Verifica formato correcto |
| 4 | | Reemplaza el archivo manteniendo el nombre y referencia |
| 5 | | Notifica el reemplazo exitoso |

**PostCondición:** Documento actualizado en el sistema.  
**Flujo Alternativo:** Formato no compatible → el sistema informa el error.

---

#### CU 11 - Eliminar Documentación

| Campo | Detalle |
|---|---|
| **Actor Primario** | Secretario |
| **Descripción** | Permite eliminar un documento del legajo de un cliente. |
| **Precondición** | El documento debe existir en el sistema. |

**Flujo de Eventos:**

| # | Acciones del Actor | Respuesta del Sistema |
|---|---|---|
| 1 | Busca y selecciona el documento a eliminar | |
| 2 | Confirma la eliminación | |
| 3 | | Elimina el archivo del sistema |
| 4 | | Actualiza el legajo del cliente |

**PostCondición:** Documento eliminado del legajo.

---

#### CU 12 - Registrar Comprobante de Pago

| Campo | Detalle |
|---|---|
| **Actor Primario** | Secretario |
| **Descripción** | Permite registrar el comprobante de pago descargado de ARCA, asociándolo al cliente correspondiente. |
| **Precondición** | Cliente registrado en el sistema. |

**Flujo de Eventos:**

| # | Acciones del Actor | Respuesta del Sistema |
|---|---|---|
| 1 | Selecciona el proceso al que estará asociado el comprobante | |
| 2 | Sube el PDF del comprobante al sistema | |
| 3 | | Verifica el formato del archivo (PDF) |
| 4 | | Guarda el comprobante |
| 5 | | Notifica el registro exitoso |

**PostCondición:** Comprobante de pago registrado y asociado al cliente.

---

#### CU 13 - Ver Comprobante de Pago

| Campo | Detalle |
|---|---|
| **Actor Primario** | Secretario, Coordinador, Directivo |
| **Descripción** | Permite consultar y visualizar un comprobante de pago descargado de ARCA. |
| **Precondición** | Cliente registrado con al menos un comprobante cargado. |

**Flujo de Eventos:**

| # | Acciones del Actor | Respuesta del Sistema |
|---|---|---|
| 1 | Buscar cliente por DNI o nombre | |
| 2 | | Muestra lista de comprobantes del cliente |
| 3 | Seleccionar el comprobante | |
| 4 | | Sirve el archivo y sus datos |

**PostCondición:** Comprobante de pago devuelto para visualización.  
**Flujo Alternativo:** El cliente no tiene comprobantes registrados → el sistema informa.

---

### Coordinador

#### CU 14 - Admisión de Cliente

| Campo | Detalle |
|---|---|
| **Actor Primario** | Coordinador |
| **Descripción** | El coordinador analiza el caso del cliente para determinar si será atendido y bajo qué condiciones. |
| **Precondición** | Existe un turno agendado con el cliente. Existe un proceso de servicio "Consultoría" asociado al cliente. |

**Flujo de Eventos:**

| # | Acciones del Actor | Respuesta del Sistema |
|---|---|---|
| 1 | Abre el legajo electrónico generado y evalúa la viabilidad del caso | |
| 2 | Registra la decisión (admitido/rechazado) | |
| 3 | Se modifica el proceso al servicio correspondiente (en caso de admitirlo, sino CU 14.1) | Se modifica el estado del proceso a "admitido" |
| 4 | Asigna profesional (CU 15) | |
| 5 | | Notifica al secretario |
| 5b | | Notifica al cliente sobre la decisión (admitido/rechazado) y en caso de rechazo, incluye el motivo |

**PostCondición:** Cliente evaluado y admitido.  
**Flujo Alternativo:** Si el caso es rechazado → CU 14.1

---

#### CU 14.1 - Registrar Motivo de Rechazo

| Campo | Detalle |
|---|---|
| **Actor Primario** | Coordinador |
| **Descripción** | Permite registrar el motivo por el cual se rechaza un caso durante la admisión. |
| **Precondición** | Caso en evaluación. |

**Flujo de Eventos:**

| # | Acciones del Actor | Respuesta del Sistema |
|---|---|---|
| 1 | Selecciona "rechazar caso" | |
| 2 | | Solicita el motivo del rechazo (selección de motivo predefinido o texto libre) |
| 3 | Ingresa el motivo | |
| 4 | | Guarda el motivo junto con la decisión de rechazo. Se modifica el estado del caso a "rechazado" |

**PostCondición:** Motivo de rechazo registrado en el sistema. El Sistema notifica al cliente sobre la decisión e incluye el motivo.

---

#### CU 15 - Asignar Profesional

| Campo | Detalle |
|---|---|
| **Actor Primario** | Coordinador, Secretario |
| **Descripción** | Permite asignar un profesional adecuado según el tipo de servicio requerido, definiendo honorario. |
| **Precondición** | Cliente admitido. |

**Flujo de Eventos:**

| # | Acciones del Actor | Respuesta del Sistema |
|---|---|---|
| 1 | Busca los profesionales vinculados al servicio necesario | |
| 2 | | Devuelve lista de profesionales filtrada según tipo de servicio |
| 3 | Selecciona profesional disponible | |
| 4 | | Verifica disponibilidad |
| 5 | Confirma selección y define honorario/servicio | |
| 6 | | Notifica al profesional y al secretario |

**PostCondición:** Profesional asociado al caso. Profesional y secretario notificados.  
**Flujo Alternativo:** Si el profesional no tiene disponibilidad → se busca otro con disponibilidad.

---

#### CU 16 - Reasignar Profesional

| Campo | Detalle |
|---|---|
| **Actor Primario** | Coordinador |
| **Descripción** | Permite cambiar el profesional asignado a un proceso, definiendo nuevos honorarios y modalidad de pago si corresponde. |
| **Precondición** | Cliente admitido. Profesional previamente asignado al Proceso. |

**Flujo de Eventos:**

| # | Acciones del Actor | Respuesta del Sistema |
|---|---|---|
| 1 | Busca el proceso por cliente o número de proceso | |
| 2 | | Muestra los datos del proceso incluyendo el profesional actualmente asignado |
| 3 | Selecciona "reasignar profesional" | |
| 4 | | Muestra lista de profesionales disponibles según especialidad |
| 5 | Selecciona el nuevo profesional | |
| 6 | | Verifica disponibilidad |
| 7 | Confirma la reasignación y define nuevos honorarios y modalidad de pago si cambian | Actualiza la relación proceso-profesional. Notifica al profesional anterior y al nuevo profesional |

**PostCondición:** Nuevo profesional asignado al proceso. Profesionales notificados.

---

### Profesional / Coordinador

#### CU 17 - Consultar Legajo

| Campo | Detalle |
|---|---|
| **Actor Primario** | Profesional / Coordinador / Directivo |
| **Descripción** | Permite acceder a los datos completos e historial del cliente. |
| **Precondición** | El actor debe tener permisos de consulta y estar asignado al caso (si es profesional) o tener rol de coordinador/Directivo. |

**Flujo de Eventos:**

| # | Acciones del Actor | Respuesta del Sistema |
|---|---|---|
| 1 | Ingresa DNI del cliente | |
| 2 | | Recupera información relacionada al cliente |
| 3 | | Devuelve los datos del cliente |

**PostCondición:** Datos del cliente accesibles para visualización (sin modificaciones).  
**Flujo Alternativo:** No hay coincidencias con el DNI buscado → el sistema informa.

---

#### CU 18 - Descargar Documentación

| Campo | Detalle |
|---|---|
| **Actor Primario** | Profesional |
| **Descripción** | Permite descargar documentos del legajo del cliente. |
| **Precondición** | El profesional debe tener acceso al legajo del cliente. |

**Flujo de Eventos:**

| # | Acciones del Actor | Respuesta del Sistema |
|---|---|---|
| 1 | Selecciona el documento a descargar | |
| 2 | | Verifica que el documento existe |
| 3 | | Descarga el documento |

**PostCondición:** Documento descargado exitosamente.  
**Flujo Alternativo:** El documento no existe → el sistema informa.

---

#### CU 19 - Actualizar Estado de Proceso

| Campo | Detalle |
|---|---|
| **Actor Primario** | Profesional / Coordinador |
| **Descripción** | Permite modificar el estado del proceso de un caso. |
| **Precondición** | El caso debe estar activo en el sistema. |

**Flujo de Eventos:**

| # | Acciones del Actor | Respuesta del Sistema |
|---|---|---|
| 1 | Selecciona el estado (iniciado / en proceso / finalizado / en espera) | |
| 2 | | Guarda el nuevo estado |
| 3 | | Actualiza el historial del caso |

**PostCondición:** Estado del proceso actualizado en el historial.

---

#### CU 20 - Registrar Reporte

| Campo | Detalle |
|---|---|
| **Actor Primario** | Profesional |
| **Descripción** | Permite registrar actas de reuniones o acciones realizadas, visibles para coordinador y Directivo. |
| **Precondición** | El profesional debe estar asignado al caso. |

**Flujo de Eventos:**

| # | Acciones del Actor | Respuesta del Sistema |
|---|---|---|
| 1 | Ingresa el contenido del reporte | |
| 2 | | Guarda el reporte |
| 3 | | Asocia el reporte al proceso |

**PostCondición:** Reporte asociado al proceso.

---

#### CU 19 - Editar Reporte

| Campo | Detalle |
|---|---|
| **Actor Primario** | Profesional |
| **Descripción** | Permite modificar el contenido de un reporte ya registrado. |
| **Precondición** | El reporte debe existir en el sistema. El actor debe ser el creador del reporte. |

**Flujo de Eventos:**

| # | Acciones del Actor | Respuesta del Sistema |
|---|---|---|
| 1 | Busca y selecciona el reporte a editar | |
| 2 | | Muestra el contenido actual del reporte |
| 3 | Modifica el contenido | |
| 4 | | Valida los datos ingresados |
| 5 | Confirma los cambios | |
| 6 | | Guarda los cambios y actualiza la fecha de modificación |

**PostCondición:** Reporte actualizado.

---

#### CU 21 - Eliminar Reporte

| Campo | Detalle |
|---|---|
| **Actor Primario** | Profesional |
| **Descripción** | Permite eliminar un reporte registrado del sistema. |
| **Precondición** | El reporte debe existir en el sistema. |

**Flujo de Eventos:**

| # | Acciones del Actor | Respuesta del Sistema |
|---|---|---|
| 1 | Busca y selecciona el reporte a eliminar | |
| 2 | Confirma la eliminación | |
| 3 | | Elimina el reporte del sistema |

**PostCondición:** Reporte eliminado.

---

#### CU 22 - Consultar Reportes de Proceso

| Campo | Detalle |
|---|---|
| **Actor Primario** | Coordinador / Directivo |
| **Descripción** | El actor debe tener permisos de consulta (coordinador o Directivo). |
| **Precondición** | Cliente admitido. |

**Flujo de Eventos:**

| # | Acciones del Actor | Respuesta del Sistema |
|---|---|---|
| 1 | Accede al módulo de reportes | |
| 2 | El actor puede buscar por proceso, rango de fechas o profesional | |
| 3 | | Muestra lista de reportes que coinciden con los criterios |
| 4 | Selecciona un reporte | |
| 5 | | Muestra el contenido completo del reporte |

**PostCondición:** Reporte consultado y legible.

---

### Directivo / Coordinador

#### CU 23 - Agregar Nuevo Usuario

| Campo | Detalle |
|---|---|
| **Actor Primario** | Directivo |
| **Descripción** | Permite agregar un nuevo usuario al sistema. |
| **Precondición** | El actor primario debe contar con los permisos requeridos. Los datos únicos no deben existir en otros usuarios. |

**Flujo de Eventos:**

| # | Acciones del Actor | Respuesta del Sistema |
|---|---|---|
| 1 | Ingresar datos del nuevo usuario | |
| 2 | Ingresar el rol del nuevo usuario | |
| 3 | | Crea un nuevo usuario |
| 4 | | Se le asigna una contraseña aleatoria |
| 5 | | Devuelve un mensaje de confirmación y la contraseña generada |

**PostCondición:** Usuario creado. Se devuelve la contraseña autogenerada.

---

#### CU 24 - Modificar Usuario

| Campo | Detalle |
|---|---|
| **Actor Primario** | Directivo |
| **Descripción** | Permite actualizar los datos y/o roles de un usuario ya registrado en el sistema. |
| **Precondición** | El usuario debe existir en el sistema. El actor principal debe estar verificado. |

**Flujo de Eventos:**

| # | Acciones del Actor | Respuesta del Sistema |
|---|---|---|
| 1 | Busca al usuario por DNI o nombre | |
| 2 | | Muestra los datos actuales del usuario |
| 3 | Modifica los campos necesarios | |
| 4 | | Valida los datos ingresados |
| 5 | Confirma los cambios | |
| 6 | | Guarda los datos actualizados y notifica éxito |

**PostCondición:** Modificados los datos del usuario.  
**Flujo Alternativo:** El usuario no existe.

---

#### CU 25 - Eliminar Usuario

| Campo | Detalle |
|---|---|
| **Actor Primario** | Directivo |
| **Descripción** | Permite realizar un soft delete de un usuario del sistema. |
| **Precondición** | El usuario debe existir en el sistema. El actor principal debe estar verificado. |

**Flujo de Eventos:**

| # | Acciones del Actor | Respuesta del Sistema |
|---|---|---|
| 1 | Busca al usuario por DNI o nombre | |
| 2 | | Muestra los datos del usuario |
| 3 | Solicita eliminar el usuario | |
| 4 | | Verifica que no tenga procesos activos |
| 5 | Confirma la eliminación | |
| 6 | | Soft delete del usuario (Eliminación lógica) |

**PostCondición:** Usuario "eliminado" del sistema.  
**Flujo Alternativo:** Si el usuario tiene procesos activos → el sistema informa que no puede eliminarse.

---

## 3. Diagrama de Casos de Uso

[Ver diagrama en draw.io](https://app.diagrams.net/#G1L2CoM1vEQATUypv-bHjGyQQNZ5I2QXXh)

---

## 4. Requisitos No Funcionales

- El sistema debe estar disponible en todo momento (alta disponibilidad).
- Debe garantizar seguridad mediante autenticación de usuarios.
- Debe ser accesible desde el navegador web.
- Debe responder en tiempos menores a 3 segundos.
- El sistema enviará notificaciones automáticas vía email y WhatsApp utilizando un servicio externo (ej: Brevo).

---

## 5. Modelo de Dominio

### Lista de Conceptos

- Cliente
- Proceso
- Servicio
- Turno
- Profesional
- Usuario
- Documento
- Reporte
- Comprobante_Pago
- Notificación

---

### Relación entre Conceptos

| Concepto | Relación | Tipo de Relación |
|---|---|---|
| Cliente | Cliente — Proceso (el cliente tiene un proceso judicial) | Parte lógica |
| Proceso | Proceso — Reporte (el proceso genera reportes) | Descripción |
| Servicio | Servicio — Proceso (el servicio define el tipo de proceso) | Descripción |
| Turno | Turno — Cliente (el turno pertenece a un cliente) | Parte lógica |
| Profesional | Profesional — Proceso (el profesional atiende un proceso) | Parte lógica |
| Usuario | Usuario — Rol (define su función en el sistema) | Descripción |
| Documento | Documento — Proceso (se asocia al proceso de un cliente) | Parte lógica |
| Reporte | Reporte — Proceso (describe el avance del proceso) | Descripción |
| ComprobantePago | ComprobantePago — Cliente (pertenece a un cliente) | Contenido lógico |
| Notificación | Notificación — Cliente (informa sobre un turno) | Descripción |

---

### Descripción de Atributos

#### Concepto 1: Cliente

| Atributo | Descripción |
|---|---|
| nombre | Nombre de pila del cliente |
| apellido | Apellido del cliente |
| dni | Documento Nacional de Identidad único para identificar al cliente |
| telefono | Número de contacto telefónico |
| correo | Dirección de email del cliente |
| domicilio | Dirección física donde reside |
| fecha_nacimiento | Fecha de nacimiento del cliente |
| estado_admision | Estado de admisión del cliente (Aceptado/Rechazado) |

#### Concepto 2: Notificación

| Atributo | Descripción |
|---|---|
| mensaje | Contenido del aviso o comunicación enviada |
| fecha_envio | Fecha y hora en que se envía la notificación |
| canal | Medio utilizado para la notificación (email, SMS, aplicación, etc.) |
| estado | Estado de la notificación (pendiente, enviada, leída) |

#### Concepto 3: Documento

| Atributo | Descripción |
|---|---|
| archivo_path | Ruta o ubicación del archivo digital almacenado |
| tipo_documento | Clasificación del documento (contrato, informe, formulario, etc.) |
| nombre | Nombre identificador del documento |

#### Concepto 4: ComprobantePago

| Atributo | Descripción |
|---|---|
| archivo_path | Archivo digital que respalda el pago realizado |
| fecha_subida | Fecha en que se cargó el comprobante al sistema |
| descripcion | Detalle adicional relacionado con el pago realizado |

#### Concepto 5: Proceso

| Atributo | Descripción |
|---|---|
| nombre | Nombre identificador del proceso |
| descripcion | Explicación del proceso y su finalidad |
| fecha_inicio | Fecha en que comienza el proceso |
| tipo | Categoría del proceso (administrativo, familia, civil, etc.) |
| estado | Situación actual del proceso |
| motivo_rechazo | Motivo registrado en caso de que el proceso sea rechazado |

#### Concepto 6: Reporte

| Atributo | Descripción |
|---|---|
| contenido | Información detallada sobre el avance o situación del proceso |
| fecha | Fecha de emisión del reporte |

#### Concepto 7: Servicio

| Atributo | Descripción |
|---|---|
| nombre | Nombre del servicio ofrecido |
| costo_servicio | Precio asociado al servicio |

#### Concepto 8: Profesional

| Atributo | Descripción |
|---|---|
| nombre | Nombre del profesional |
| apellido | Apellido del profesional |
| dni | Documento identificatorio único |
| telefono | Número telefónico de contacto |
| correo | Email profesional |
| domicilio | Dirección del profesional |
| fecha_nacimiento | Fecha de nacimiento |
| fecha_ingreso | Fecha en que comenzó a trabajar en la organización |

#### Concepto 9: Usuario

| Atributo | Descripción |
|---|---|
| nombre | Nombre del usuario del sistema |
| email | Correo electrónico utilizado para acceder al sistema |
| password | Contraseña de acceso al sistema |
| dni | Documento identificatorio del usuario |
| telefono | Número de contacto del usuario |
| domicilio | Dirección registrada |
| fecha_nacimiento | Fecha de nacimiento |
| fecha_ingreso | Fecha de incorporación al sistema |

#### Concepto 10: Turno

| Atributo | Descripción |
|---|---|
| fecha_hora | Fecha y hora asignada al turno |
| es_externo | Indica si el turno se realiza fuera de la organización |
| detalle_externo | Información adicional cuando el turno es externo |
| tipo | Tipo de turno asignado |

---

## 6. Diagrama de Secuencia (DSS) — Casos más relevantes

### CU4 — Agendar Turno Interno

**Flujo principal:**
1. Secretario → Sistema: `seleccionarCliente(dni)`
2. Sistema → Secretario: `mostrarDatosCliente()`
3. Secretario → Sistema: `seleccionarProfesional()`
4. Secretario → Sistema: `ingresarFechaHora()`
5. Sistema (interno): `verificarDisponibilidad()`

**[alt] Profesional disponible:**
6. Secretario → Sistema: `confirmarTurno()`
7. Sistema (interno): `registrarTurno()`
8. Sistema (interno): `asociarCliente()`
9. Sistema (interno): `asociarProfesional()`
10. Sistema → Secretario: `turnoRegistrado()`
11. Sistema (interno): `notificarCliente()`
12. Sistema (interno): `notificarProfesional()`

**[alt] Profesional no disponible:**
- Sistema → Secretario: `solicitarNuevaFecha()`

---

### CU14 — Admisión de Cliente

**Flujo principal:**
1. Coordinador → Sistema: `abrirLegajo(cliente)`
2. Sistema → Coordinador: `mostrarInformacionCaso()`
3. Coordinador → Sistema: `registrarDecision(admitido/rechazado)`

**[alt] Caso admitido:**
4. Sistema (interno): `actualizarEstadoProceso("Admitido")`
5. Sistema → Coordinador: `solicitarAsignacionProfesional()`
6. Sistema (interno): `notificarSecretario()`
7. Sistema (interno): `notificarCliente()`

**[alt] Caso rechazado:**
8. Coordinador → Sistema: `ingresarMotivoRechazo()`
9. Sistema (interno): `guardarMotivoRechazo()`
10. Sistema (interno): `actualizarEstadoProceso("Rechazado")`
11. Sistema (interno): `notificarSecretario()`
12. Sistema (interno): `notificarCliente()`

---

### CU15 — Asignar Profesional

**Flujo principal:**
1. Coordinador → Sistema: `buscarProfesionales(servicio)`
2. Sistema → Coordinador: `mostrarProfesionalesDisponibles()`
3. Coordinador → Sistema: `seleccionarProfesional()`
4. Sistema (interno): `verificarDisponibilidad()`

**[alt] Profesional disponible:**
5. Coordinador → Sistema: `confirmarAsignacion(honorarios)`
6. Sistema (interno): `asociarProfesionalAlProceso()`
7. Sistema (interno): `registrarHonorarios()`
8. Sistema (interno): `notificarProfesional()`
9. Sistema (interno): `notificarSecretario()`
10. Sistema → Coordinador: `asignacionExitosa()`

**[alt] Profesional no disponible:**
- Sistema → Coordinador: `informarFaltaDisponibilidad()`

---

### CU19 — Actualizar Estado de Proceso

**Flujo principal:**
1. Profesional → Sistema: `seleccionarProceso()`
2. Sistema → Profesional: `mostrarDatosProceso()`
3. Profesional → Sistema: `actualizarEstado(nuevoEstado)`
4. Sistema (interno): `guardarEstado()`
5. Sistema (interno): `actualizarHistorial()`
6. Sistema → Profesional: `estadoActualizado()`
