# Documento de Requerimientos y Especificación del Sistema

## 1. Descripción del Escenario

El estudio jurídico (en adelante, la organización) se dedica a brindar servicios profesionales en las áreas Civil, Comercial y de Familia. Actualmente, la gestión interna se realiza de manera manual o mediante herramientas dispersas, como planillas, archivos físicos y comunicaciones informales. Este enfoque, si bien permitió operar en etapas iniciales, genera dificultades en la organización de la información, duplicación de datos, demoras en la atención y una limitada trazabilidad de los procesos.

Entre las principales problemáticas se destacan:

* Falta de centralización de la información de clientes.
* Dificultad para coordinar turnos entre secretaría, coordinadores y profesionales.
* Manejo poco estructurado de la documentación.
* Ausencia de un seguimiento claro de los casos en curso.

Con el objetivo de mejorar la eficiencia operativa, optimizar la atención al cliente y garantizar una adecuada gestión de la información, se propone el desarrollo de un sistema de gestión y administración interna jurídica que permita centralizar todas las actividades del estudio.

### Flujo del Sistema

1. **Admisión Inicial:** El proceso comienza cuando un cliente se presenta por primera vez. La secretaría registrará sus datos básicos y clasificará el tipo de servicio requerido como "asesoramiento". Posteriormente, se agendará un turno con el coordinador, quien será notificado automáticamente.
2. **Evaluación del Coordinador:** Durante la consulta, el coordinador abrirá el legajo del cliente, evaluará la viabilidad del caso, definirá el tipo de servicio a prestar, asignará el profesional correspondiente según su especialidad y establecerá los honorarios junto con la modalidad de pago. Una vez tomada la decisión de aceptar o rechazar el caso, se notificará a la secretaría.
3. **Procesamiento Administrativo:** Si el caso es aceptado, la secretaría solicitará al cliente la documentación necesaria según el tipo de servicio. Dicha documentación será digitalizada y cargada en el sistema. Además, se agendará un nuevo turno con el profesional designado, notificando tanto al profesional como al cliente (el sistema solo enviará notificaciones, sin permitir aún la confirmación o cancelación por parte del cliente).
4. **Gestión del Caso:** Los profesionales podrán acceder a la información completa del cliente, historial de casos y documentación. Registrarán el avance de cada caso mediante la actualización de estados (*iniciado, en proceso, finalizado o en espera*) y la generación de actas (reportes).
5. **Gestión de Pagos:** La secretaría accederá al sistema externo de ARCA, descargará el comprobante correspondiente y lo cargará en el sistema interno.
6. **Módulo de Turnos:** Permitirá registrar citas internas y compromisos externos (como audiencias judiciales). En compromisos externos, se bloqueará la disponibilidad del profesional. Se incluirán recordatorios automáticos previos a cada evento.

---

## 2. Requisitos Funcionales (Casos de Uso)

**Actores:** Secretario, Cliente, Abogados, Contadores, Coordinadores, Director General.

### CU1 - Registrar Cliente

* **Actor Primario:** Secretario
* **Descripción:** Permite registrar en el sistema a un nuevo cliente, almacenando sus datos personales y de contacto.
* **Precondición:** El cliente no debe existir previamente en el sistema.
* **Flujo de Eventos:**
    1. El Actor ingresa los datos personales del cliente (nombre, DNI, contacto, dirección).
    2. El Sistema valida que los datos estén completos y correctos.
    3. El Actor confirma el registro.
    4. El Sistema almacena la información del cliente.
    5. El Sistema notifica registro exitoso.
* **Postcondición:** Cliente asociado al sistema.
* **Flujo Alternativo:** Si el cliente ya existe -> el sistema informa duplicidad y cancela el registro.

### CU2 - Modificar Cliente

* **Actor Primario:** Secretario
* **Descripción:** Permite actualizar los datos personales de un cliente ya registrado.
* **Precondición:** El cliente debe existir en el sistema.
* **Flujo de Eventos:**
    1. El Actor busca al cliente por DNI o nombre.
    2. El Sistema muestra los datos actuales del cliente.
    3. El Actor modifica los campos necesarios.
    4. El Sistema valida los datos ingresados.
    5. El Actor confirma los cambios.
    6. El Sistema guarda los datos actualizados y notifica éxito.
* **Postcondición:** Datos del cliente actualizados en el sistema.
* **Flujo Alternativo:** Si los datos son inválidos -> el sistema informa el error y solicita corrección.

### CU3 - Eliminar Cliente

* **Actor Primario:** Secretario
* **Descripción:** Permite eliminar el registro de un cliente del sistema.
* **Precondición:** El cliente debe existir en el sistema y no tener procesos activos.
* **Flujo de Eventos:**
    1. El Actor busca al cliente por DNI o nombre.
    2. El Sistema muestra los datos del cliente.
    3. El Actor solicita eliminar el cliente.
    4. El Sistema verifica que no tenga procesos activos.
    5. El Actor confirma la eliminación.
    6. El Sistema elimina el registro del cliente.
* **Postcondición:** Cliente eliminado del sistema.
* **Flujo Alternativo:** Si el cliente tiene procesos activos -> el sistema informa que no puede eliminarse.

### CU4 - Agendar Turno con Coordinador

* **Actor Primario:** Secretario
* **Descripción:** Permite registrar un turno inicial entre el cliente y el coordinador.
* **Precondición:** Cliente registrado en el sistema.
* **Flujo de Eventos:**
    1. El Actor selecciona fecha y hora del turno.
    2. El Sistema verifica disponibilidad del coordinador.
    3. El Actor confirma el turno.
    4. El Sistema registra el turno en la agenda.
    5. El Sistema notifica al coordinador y al cliente.
* **Postcondición:** Turno asociado al cliente. Coordinador y cliente notificados.
* **Flujo Alternativo:** Si no hay disponibilidad -> el sistema solicita otra fecha.

### CU5 - Agendar Turno con Profesional

* **Actor Primario:** Secretario
* **Descripción:** Permite registrar un turno entre el cliente y el profesional asignado.
* **Precondición:** Profesional asignado al caso. Cliente admitido.
* **Flujo de Eventos:**
    1. El Actor selecciona fecha y hora.
    2. El Sistema verifica disponibilidad del profesional.
    3. El Actor confirma el turno.
    4. El Sistema registra el turno.
    5. El Sistema notifica al profesional y al cliente.
* **Postcondición:** Turno registrado. Cliente asociado al turno. Turno asociado al profesional.
* **Flujo Alternativo:** Si no hay disponibilidad el sistema solicita otra fecha.

### CU6 - Registrar Turno Externo

* **Actor Primario:** Secretario
* **Descripción:** Registra compromisos externos del profesional (como audiencias judiciales), bloqueando su disponibilidad.
* **Precondición:** Profesional registrado en el sistema.
* **Flujo de Eventos:**
    1. El Actor ingresa fecha, hora y detalle del evento externo.
    2. El Sistema registra el evento.
    3. El Sistema bloquea la disponibilidad del profesional en ese horario.
    4. El Sistema notifica al profesional.
* **Postcondición:** Evento externo registrado. Disponibilidad del profesional bloqueada.

### CU7 - Modificar Turno

* **Actor Primario:** Secretario
* **Descripción:** Permite modificar la fecha y/o hora de un turno registrado.
* **Precondición:** El turno debe existir en el sistema.
* **Flujo de Eventos:**
    1. El Actor busca y selecciona el turno a modificar.
    2. El Sistema muestra los datos actuales del turno.
    3. El Actor ingresa la nueva fecha y/o hora.
    4. El Sistema verifica disponibilidad en el nuevo horario.
    5. El Actor confirma el cambio.
    6. El Sistema actualiza el turno y notifica a los involucrados.
* **Postcondición:** Turno actualizado. Involucrados notificados.
* **Flujo Alternativo:** Si es turno externo el sistema desbloquea el horario anterior y bloquea el nuevo.

### CU8 - Eliminar Turno

* **Actor Primario:** Secretario
* **Descripción:** Permite eliminar un turno registrado en el sistema.
* **Precondición:** El turno debe existir en el sistema.
* **Flujo de Eventos:**
    1. El Actor busca y selecciona el turno a eliminar.
    2. El Actor confirma la eliminación.
    3. El Sistema elimina el turno del sistema.
    4. El Sistema (si era turno externo) desbloquea la disponibilidad del profesional.
    5. El Sistema notifica a los involucrados.
* **Postcondición:** Turno eliminado. Disponibilidad actualizada si correspondía.

### CU9 - Cargar Documentación

* **Actor Primario:** Secretario
* **Descripción:** Permite cargar los documentos entregados por el cliente al sistema, asociándolos a su legajo.
* **Precondición:** Cliente registrado y legajo creado.
* **Flujo de Eventos:**
    1. El Actor abre el legajo del cliente.
    2. El Actor carga los documentos.
    3. El Sistema verifica formato correcto de los documentos.
    4. El Sistema guarda los archivos.
    5. El Sistema notifica el guardado exitoso.
* **Postcondición:** Documentación almacenada y asociada al cliente.
* **Flujo Alternativo:** Documento con formato no compatible -> el sistema informa el error.

### CU10 - Reemplazar Documentación

* **Actor Primario:** Secretario
* **Descripción:** Permite reemplazar un documento existente por una nueva versión, manteniendo la referencia en el legajo.
* **Precondición:** El documento debe existir en el sistema.
* **Flujo de Eventos:**
    1. El Actor busca y selecciona el documento a reemplazar.
    2. El Actor carga el nuevo archivo.
    3. El Sistema verifica formato correcto.
    4. El Sistema reemplaza el archivo manteniendo el nombre y referencia.
    5. El Sistema notifica el reemplazo exitoso.
* **Postcondición:** Documento actualizado en el sistema.
* **Flujo Alternativo:** Formato no compatible -> el sistema informa el error.

### CU11 - Eliminar Documentación

* **Actor Primario:** Secretario
* **Descripción:** Permite eliminar un documento del legajo de un cliente.
* **Precondición:** El documento debe existir en el sistema.
* **Flujo de Eventos:**
    1. El Actor busca y selecciona el documento a eliminar.
    2. El Actor confirma la eliminación.
    3. El Sistema elimina el archivo del sistema.
    4. El Sistema updates el legajo del cliente.
* **Postcondición:** Documento eliminado del legajo.

### CU12 - Registrar Comprobante de Pago

* **Actor Primario:** Secretario
* **Descripción:** Permite registrar el comprobante de pago descargado de ARCA, asociándolo al cliente correspondiente.
* **Precondición:** Cliente registrado. Servicio designado con honorario definido.
* **Flujo de Eventos:**
    1. El Actor accede al sistema externo de ARCA y descarga el comprobante.
    2. El Actor sube el comprobante al sistema y lo asocia al cliente.
    3. El Sistema verifica el formato del archivo.
    4. El Sistema guarda el comprobante vinculado al cliente.
    5. El Sistema notifica el registro exitoso.
* **Postcondición:** Comprobante de pago registrado y asociado al cliente.
* **Flujo Alternativo:** Formato no compatible -> el sistema informa el error.

### CU13 - Admisión de Cliente

* **Actor Primario:** Coordinador
* **Descripción:** El coordinador analiza el caso del cliente para determinar si será atendido y bajo qué condiciones.
* **Precondición:** Existe un turno agendado con el cliente.
* **Flujo de Eventos:**
    1. El Actor abre el legajo electrónico generado.
    2. El Actor evalúa la viabilidad del caso.
    3. El Actor registra la decisión (admitido/rechazado).
    4. El Sistema notifica al secretario.
* **Postcondición:** Cliente evaluado y admitido.
* **Flujo Alternativo:** Si el caso es rechazado -> se finaliza el proceso.

### CU14 - Asignar Profesional

* **Actor Primario:** Coordinador
* **Descripción:** Permite asignar un profesional adecuado según el tipo de servicio requerido, definiendo honorario y modalidad de pago.
* **Precondición:** Cliente admitido.
* **Flujo de Eventos:**
    1. El Actor busca los profesionales vinculados al servicio necesario.
    2. El Sistema devuelve lista de profesionales filtrada según tipo de servicio.
    3. El Actor selecciona profesional disponible.
    4. El Sistema verifica disponibilidad.
    5. El Actor confirma selección y define honorario y modalidad de pago.
    6. El Sistema notifica al profesional y al secretario.
* **Postcondición:** Profesional asociado al caso. Profesional y secretario notificados.
* **Flujo Alternativo:** Si el profesional no tiene disponibilidad se busca otro con disponibilidad.

### CU15 - Consultar Legajo

* **Actor Primario:** Profesional
* **Descripción:** Permite acceder a los datos completos e historial del cliente.
* **Precondición:** El profesional debe estar asignado al caso.
* **Flujo de Eventos:**
    1. El Actor ingresa DNI del cliente.
    2. El Sistema recupera información relacionada al cliente.
    3. El Sistema devuelve los datos del cliente.
* **Postcondición:** Datos del cliente accesibles para visualización (sin modificaciones).
* **Flujo Alternativo:** No hay conicidencias con el DNI buscado -> el sistema informa.

### CU16 - Descargar Documentación

* **Actor Primario:** Profesional
* **Descripción:** Permite descargar documentos del legajo del cliente.
* **Precondición:** El profesional debe tener acceso al legajo del cliente.
* **Flujo de Eventos:**
    1. El Actor selecciona el documento a descargar.
    2. El Sistema verifica que el documento existe.
    3. El Sistema descarga el documento.
* **Postcondición:** Documento descargado exitosamente.
* **Flujo Alternativo:** El documento no existe -> el sistema informa.

### CU17 - Actualizar Estado de Proceso

* **Actor Primario:** Profesional / Coordinador
* **Descripción:** Permite modificar el estado del proceso de un caso.
* **Precondición:** El caso debe estar activo en el sistema.
* **Flujo de Eventos:**
    1. El Actor selecciona el estado (iniciado / en proceso / finalizado / en espera).
    2. El Sistema guarda el nuevo estado.
    3. El Sistema actualiza el historial del caso.
* **Postcondición:** Estado del proceso actualizado en el historial.

### CU18 - Registrar Reporte

* **Actor Primario:** Profesional
* **Descripción:** Permite registrar actas de reuniones o acciones realizadas, visibles para coordinador y director general.
* **Precondición:** El profesional debe estar asignado al caso.
* **Flujo de Eventos:**
    1. El Actor ingresa el contenido del reporte.
    2. El Sistema guarda el reporte.
    3. El Sistema asocia el reporte al proceso.
* **Postcondición:** Reporte asociado al proceso.

### CU20 - Eliminar Reporte

* **Actor Primario:** Profesional
* **Descripción:** Permite eliminar un reporte registrado del sistema.
* **Precondición:** El reporte debe existir en el sistema.
* **Flujo de Eventos:**
    1. El Actor busca y selecciona el reporte a eliminar.
    2. El Actor confirma la eliminación.
    3. El Sistema elimina el reporte del sistema.
* **Postcondición:** Reporte eliminado.

### CU21 - Gestionar Usuarios

* **Actor Primario:** Director General / Coordinador
* **Descripción:** Permite crear, modificar y eliminar usuarios del sistema (secretarios, coordinadores, profesionales, director general).
* **Precondición:** El actor debe tener permisos de administración.
* **Flujo de Eventos:**
    1. El Actor selecciona la acción (crear / modificar / eliminar).
    2. El Actor ingresa o modifica los datos del usuario y su rol.
    3. El Sistema valida los datos ingresados.
    4. El Actor confirma la acción.
    5. El Sistema ejecuta la operación y notifica el resultado.
* **Postcondición:** Usuario creado, modificado o eliminado del sistema.
* **Flujo Alternativo:** Si se intenta eliminar un usuario con procesos activos -> el sistema informa e impide la eliminación.

---

## 3. Requisitos No Funcionales

* **Disponibilidad:** El sistema debe estar disponible en todo momento (alta disponibilidad).
* **Seguridad:** Debe garantizar seguridad mediante autenticación de usuarios.
* **Acceso:** Debe ser accesible desde el navegador web.
* **Rendimiento:** Debe responder en tiempos menores a 3 segundos.
* **Notificaciones:** El sistema enviará notificaciones automáticas vía email y WhatsApp utilizando un servicio externo (ej: Brevo).

---

## 4. Modelo de Dominio

### Lista de Conceptos

* Cliente
* Proceso
* Servicio
* Turno
* Profesional
* Usuario
* Documento
* Reporte
* Comprobante_Pago
* Notificación

### Relación entre Conceptos

| Concepto | Relación | Tipo de Relación |
| :--- | :--- | :--- |
| **Cliente** | Proceso (el cliente tiene un proceso judicial) | Parte lógico |
| **Proceso** | Reporte (el proceso genera reportes) | Descripción |
| **Servicio** | Proceso (el servicio define el tipo de proceso) | Descripción |
| **Turno** | Cliente (el turno pertenece a un cliente) | Parte Lógico |
| **Profesional** | Proceso (el abogado atiende un proceso) | Parte Lógico |
| **Usuario** | Usuario Rol (define su función en el sistema) | Descripción |
| **Documento** | Proceso (se asocia al proceso de ese cliente) | Parte Lógica |
| **Reporte** | Proceso (describe el avance del proceso) | Descripción |
| **Comprobante Pago** | Cliente (pertenece a un cliente) | Contenido Lógico |
| **Notificación** | Turno (informa sobre el turno) | Descripción |

### Descripción de Atributos

#### **1. Cliente**

* `nombre`: Nombre de pila del cliente.
* `apellido`: Apellido del cliente.
* `DNI`: Documento Nacional de Identidad único para identificar al cliente.
* `teléfono`: Número de contacto telefónico.
* `correo`: Dirección de email del cliente.
* `domicilio`: Dirección física donde reside.
* `fecha nacimiento`: Fecha de nacimiento del cliente.
* `estado admisión`: Aceptado/Rechazado.

#### **2. Notificación**

* `mensaje`: Contenido del aviso o comunicación enviada.
* `fecha envio`: Fecha y hora en que se envía la notificación.
* `canal`: Medio utilizado (ej: email, SMS, app).
* `destinatario_tipo`: Tipo de receptor (cliente, profesional).

#### **3. Documento**

* `archivo`: Archivo digital que contiene el documento.
* `tipo_documento`: Clasificación del documento (ej: contrato, informe).

#### **4. Comprobante Pago**

* `archivo`: Archivo que respalda el pago.
* `fecha subida`: Fecha en que se cargó el comprobante al sistema.
* `descripción`: Detalle adicional del pago realizado.

#### **5. Proceso**

* `nombre`: Nombre identificador del proceso.
* `descripción`: Explicación del proceso y su finalidad.
* `fecha inicio`: Fecha en que comienza el proceso.
* `tipo`: Categoría del proceso (ej: administrativo, Familia, Civil).
* `estado`: Situación actual.

#### **6. Reporte**

* `fecha`: Fecha de emisión del reporte.
* `contenido`: Detalle textual del reporte del caso.

#### **7. Servicio**

* `nombre`: Nombre del servicio ofrecido.
* `costo servicio`: Precio asociado al servicio.

#### **8. Profesional**

* `nombre`: Nombre del profesional.
* `apellido`: Apellido del profesional.
* `dni`: Documento identificatorio único.
* `teléfono`: Número de teléfono de contacto.
* `correo`: Email profesional.
* `domicilio`: Dirección del profesional.
* `fecha nacimiento`: Fecha de nacimiento.
* `fecha_ingreso`: Fecha en que comenzó a trabajar en la organización.

#### **9. Usuario**

* `correo`: Email utilizado para acceder al sistema.
* `contraseña`: Clave de acceso del usuario.
* `rol`: Tipo de usuario dentro del sistema (admin, cliente, profesional).

#### **10. Turno**

* `fecha hora`: Fecha y hora asignada al turno.
* `es externo`: Indica si el turno es fuera de la organización (booleano).
* `detalle externo`: Información adicional si el turno es externo.
