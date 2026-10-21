## ADDED Requirements

### Requirement: Aplicar la base visual Aequitas a clientes, turnos y comprobantes

Las pantallas activas de clientes, turnos/agenda y comprobantes MUST usar Figtree y las superficies, tipografía, colores y estados definidos por la base visual Aequitas. Las acciones primarias y enlaces operativos MUST usar marino; el dorado MUST quedar reservado para foco e indicadores. Los textos y fondos MUST mantener contraste administrativo legible. Las pantallas MUST adaptarse al ancho del viewport; el listado de clientes MUST conservar todos sus datos y acciones en un diseño usable a 390 px sin desbordar el viewport. La agenda, los turnos y los comprobantes MUST mantener sus estados semánticos legibles en ambas disposiciones.

La adaptación MUST conservar rutas, permisos, filtros y búsquedas disponibles, validación, acciones y transiciones actuales. MUST NOT crear nuevas operaciones, cambiar autorizaciones ni ofrecer baja de comprobantes.

#### Scenario: Usar clientes en pantalla móvil

- **WHEN** un usuario autorizado lista clientes en un viewport de 390 px
- **THEN** cada resultado presenta su nombre, DNI, correo, teléfono y acciones permitidas en una tarjeta usable
- **AND** el listado no genera desplazamiento horizontal del viewport
- **AND** búsqueda, filtro, paginación y estados vacíos conservan su comportamiento

#### Scenario: Consultar agenda y gestionar turnos

- **WHEN** un usuario consulta filtros, listado, agenda, detalle o formulario de turnos desde escritorio o móvil
- **THEN** los datos y controles usan la base visual Aequitas y se adaptan al ancho disponible
- **AND** cancelados, programados y compromisos externos continúan identificándose con sus textos y acciones autorizadas

#### Scenario: Registrar y consultar comprobantes

- **WHEN** un usuario autorizado abre el formulario o listado de comprobantes desde escritorio o móvil
- **THEN** la búsqueda por DNI, campos, validaciones, estado de carga, confirmación de guardado y enlace de consulta siguen disponibles
- **AND** el listado presenta todos sus datos y enlaces autorizados sin desbordar el viewport
- **AND** la interfaz no ofrece una acción de baja u ocultación
