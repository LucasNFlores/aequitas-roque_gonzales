## ADDED Requirements

### Requirement: Consultar legajo jurídico por cliente

El sistema MUST permitir a usuarios autenticados con CU18 buscar clientes por DNI o nombre y consultar su legajo. La ficha MUST incluir datos de contacto, procesos visibles, servicio, tipo, estado, fechas, descripción, coordinador, profesional, honorarios, motivo de rechazo, turnos relacionados, reportes autorizados, comprobantes asociados e historial de estados con estado anterior/nuevo, fecha, usuario y motivo.

#### Scenario: Profesional consulta los procesos asignados

- GIVEN un Profesional con CU18 y un cliente con procesos asignados y no asignados a ese usuario
- WHEN busca al cliente y abre su legajo
- THEN solo recibe los procesos asignados a ese Profesional y las relaciones vinculadas a ellos

#### Scenario: Roles de supervisión consultan el legajo

- GIVEN un Coordinador, Directivo o Administrador con CU18
- WHEN abre el legajo de un cliente
- THEN recibe las relaciones permitidas por la matriz y los estados vacíos se muestran sin error

#### Scenario: Un usuario sin CU18 solicita el legajo

- GIVEN un Secretario u otro usuario autenticado sin CU18
- WHEN solicita la bandeja, la ficha o una relación por URL directa
- THEN recibe denegación y no obtiene datos del legajo

### Requirement: Mantener separados el legajo y los archivos documentales

La consulta CU18 MUST mantenerse separada de CU12 y CU19. El Directivo puede consultar metadatos documentales del legajo, pero MUST NOT recibir contenido de archivos, rutas, vistas previas ni enlaces de archivo. La visualización en línea MUST autorizar CU12 y la descarga MUST autorizar CU19, con alcance del Profesional asignado aplicado también a versiones históricas.

#### Scenario: Directivo consulta la ficha

- GIVEN un Directivo autorizado por CU18
- WHEN consulta un legajo que contiene documentos
- THEN ve metadatos de categoría, tipo e historial, pero no puede visualizar ni descargar los archivos

#### Scenario: Sustitución conserva la versión anterior

- GIVEN un documento existente y un usuario autorizado por CU10
- WHEN reemplaza el archivo
- THEN el documento conserva su identidad y la versión anterior queda registrada, accesible solo mediante las policies documentales correspondientes

### Requirement: Gestionar reportes y consultar comprobantes desde el legajo

El Profesional asignado y el Administrador MUST poder registrar reportes según CU21; solo el autor o el Administrador pueden editarlos o darlos de baja según CU22/CU23. CU24 limita al Profesional a sus propios reportes y permite a Coordinador, Directivo y Administrador consultar los reportes autorizados del proceso. La consulta del PDF de comprobante MUST autorizar CU14.

#### Scenario: Profesional administra reportes de su proceso

- GIVEN un Profesional asignado al proceso
- WHEN registra un reporte, edita uno propio o elimina uno propio
- THEN el sistema conserva autoría, validación, alcance y baja lógica

#### Scenario: Un rol autorizado consulta un comprobante

- GIVEN un usuario con CU14 y un comprobante asociado al cliente
- WHEN abre el comprobante desde la ficha o la lista
- THEN el PDF se sirve solo después de autorizar la política
