# Adoptar la base visual en procesos, legajos y documentos

## Problema

La base visual Aequitas ya está integrada en `dev`, pero las pantallas activas de procesos, legajos, documentos y reportes de proceso todavía usan colores índigo y estilos heredados. Esto hace que la identidad, los focos y la jerarquía de superficies varíen entre áreas.

## Alcance

- Alinear listados, filtros, detalle del proceso, alta de procesos adicionales, legajos, documentación, reportes de proceso e historiales con los tokens Aequitas existentes.
- Mantener Figtree para la interfaz operativa, superficies claras, texto oscuro y dorado solo en focos e indicadores.
- Conservar acciones, estados, filtros, permisos, visibilidad por rol y alcance de datos sin cambios.
- Verificar el frontend en escritorio y móvil con un servidor y puerto propios.

## Fuera de alcance

- Cambios a clientes, turnos, comprobantes, usuarios o administración.
- Cambios a layouts, navegación, configuración Tailwind, CSS global o componentes compartidos.
- Cambios de rutas, backend, base de datos, permisos, procesos de negocio o documentación general.
- Pruebas de backend.

## Enfoque

Aplicar tokens semánticos ya definidos en `tailwind.config.js` y `resources/css/app.css` directamente en las vistas locales afectadas. Mantener estados de foco, error, carga y acciones destructivas; mejorar la lectura responsiva sin ocultar información ni controles autorizados.

## Riesgos

- Las vistas Livewire reúnen muchas acciones condicionadas por policies; el cambio de presentación debe preservar intactas las directivas de autorización y sus datos.
- Las tablas son densas en móvil; se conservará su scroll horizontal dentro del contenedor y se mantendrán áreas de interacción cómodas.
- Los datos de varios roles difieren por autorización de servidor; la revisión visual deberá cubrir los roles disponibles sin alterar sus consultas.
