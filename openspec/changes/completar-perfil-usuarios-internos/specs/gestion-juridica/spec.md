## MODIFIED Requirements

### Requirement: Administrar usuarios según CU 25 a CU 27

El sistema MUST permitir a Directivo y Administrador agregar, modificar y dar de baja lógicamente usuarios. La baja MUST rechazarse mientras el usuario tenga procesos activos y MUST conservar trazabilidad. Los formularios de alta y actualización MUST admitir nombres y apellido como campos separados. La fecha de ingreso MUST iniciar con la fecha actual al crear una cuenta, también cuando una petición autorizada omita ese campo. Los datos previos MUST conservarse sin que el sistema infiera automáticamente un apellido a partir del nombre almacenado.

Cada usuario MUST tener como máximo un rol en todo el sistema. La gestión MUST presentar un selector de un solo valor y rechazar asignaciones múltiples. Al migrar cuentas existentes con varios roles, el sistema MUST conservar únicamente el rol de mayor jerarquía en este orden: Administrador, Directivo, Coordinador, Profesional y Secretario. Roles no listados MUST quedar por debajo de los roles oficiales y el desempate entre ellos MUST ser determinista.

#### Scenario: CU 25 - Agregar Nuevo Usuario

- **WHEN** Directivo o Administrador agrega un usuario interno con nombres, apellido y un rol
- **THEN** el sistema crea la cuenta autenticable con los datos y rol admitidos
- **AND** asigna la fecha actual como fecha de ingreso si la petición no incluye una fecha
- **AND** guarda como máximo un rol

#### Scenario: CU 26 - Modificar Usuario

- **WHEN** Directivo o Administrador modifica los datos o rol de un usuario
- **THEN** el sistema guarda los cambios autorizados y mantiene nombre y apellido en campos separados
- **AND** reemplaza el rol anterior por el único rol seleccionado, si se modifica

#### Scenario: Normalizar cuentas con roles múltiples

- **GIVEN** una cuenta existente con dos o más roles
- **WHEN** se aplica la migración de la regla de rol único
- **THEN** conserva únicamente el rol de mayor jerarquía
- **AND** la base de datos impide que esa cuenta vuelva a recibir otro rol simultáneo


#### Scenario: CU 27 - Eliminar Usuario

- **WHEN** Directivo o Administrador solicita la baja lógica de un usuario sin procesos activos
- **THEN** el sistema conserva la cuenta como baja lógica y la excluye de consultas operativas
- **AND** si tiene procesos activos, el sistema rechaza la baja
