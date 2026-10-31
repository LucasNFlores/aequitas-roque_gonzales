# Diseño: autorización de CU 39 para el Directivo

## Decisión

Separar el acceso al informe documental de la administración de categorías con el permiso `consultar_informe_documental`. Coordinador y Directivo lo reciben desde `RoleSeeder`; el Administrador conserva acceso como superusuario.

## Aplicación

- Proteger ambas rutas y las acciones del controlador con la nueva autorización.
- Mostrar el enlace de informe con el permiso nuevo en navegación dinámica y estática; mantener el enlace de categorías bajo `gestionar_categorias_documentos`.
- Conservar filtros, agregación y contenido del PDF sin cambios.
- Actualizar la fila CU 39 de la matriz de Drive, la copia Markdown, la transcripción funcional y la especificación base.

## Riesgos y validación

La autorización debe comportarse igual en la pantalla y en la URL directa del PDF. Las pruebas cubrirán los tres roles permitidos, los roles no habilitados y que la consulta no conceda permisos de gestión o acceso a archivos.
