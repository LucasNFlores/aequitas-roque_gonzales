# Diseño: navegación por secciones y acciones de cuenta

## Estructura

- **Inicio:** Dashboard.
- **Gestión jurídica:** Clientes, Legajos, Procesos y Turnos.
- **Administración:** Usuarios y Roles, Servicios por profesional, Servicios, Estados de proceso y Categorías.
- **Informes:** Informe documental y Comprobantes.

Las secciones operativas conservan sus directivas `@can`. Se usa una condición por grupo para evitar encabezados vacíos para roles con permisos limitados.

## Cuenta activa

La tarjeta inferior del usuario autenticado pasa a ser un botón desplegable con inicial, nombre, rol y chevron. Al activarse muestra Modo oscuro/claro, Perfil y Cerrar sesión. Alpine controla la expansión, y el botón expone `aria-expanded` y `aria-controls`; el panel puede cerrarse con Escape o al pulsar fuera.

## Compatibilidad

La navegación estática conserva sus enlaces y autorizaciones. No se modifican permisos, rutas, ni el mecanismo de preferencia del tema.
