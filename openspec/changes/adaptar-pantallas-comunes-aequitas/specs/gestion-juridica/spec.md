# Requisitos visuales de pantallas comunes

## ADDED Requirements

### Requirement: Mantener una identidad visual coherente en las pantallas comunes

Las pantallas activas de recuperación, restablecimiento, confirmación y verificación de acceso, perfil y dashboard MUST usar la base visual Aequitas integrada. MUST usar Figtree para lectura y controles, conservar Playfair Display para acentos editoriales puntuales, emplear azul marino en acciones principales, dorado en foco e indicadores y mantener superficies claras con texto oscuro, radios y sombras compartidos. MUST conservar las rutas, permisos, destinos, validaciones y estados funcionales existentes. El dashboard MUST mantener la variante y el alcance autorizado de cada rol.

#### Scenario: Visitante completa un flujo de acceso común

- **WHEN** un visitante abre recuperación o restablecimiento de contraseña, confirmación de contraseña o verificación de correo
- **THEN** la pantalla presenta la identidad visual Aequitas y conserva las acciones, campos, errores y mensajes del flujo
- **AND** los controles mantienen foco visible y etiquetas asociadas

#### Scenario: Empleado consulta o actualiza su perfil

- **WHEN** un empleado autenticado abre su perfil y sus formularios
- **THEN** la superficie y los controles presentan la base visual Aequitas
- **AND** los mensajes de éxito, errores, acciones destructivas y confirmación de eliminación conservan su significado y comportamiento

#### Scenario: Empleado consulta el dashboard de su rol

- **WHEN** un empleado abre el dashboard autorizado para su rol en escritorio o móvil
- **THEN** tarjetas, listas, filtros, gráficos, estados vacíos y mensajes presentan la base visual Aequitas
- **AND** conserva los datos, acciones, destinos, condiciones de visibilidad y alcances autorizados existentes
- **AND** los valores de gráficos y los filtros siguen disponibles mediante controles accesibles
