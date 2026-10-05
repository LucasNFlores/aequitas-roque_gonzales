# Diseño: legajo del cliente y módulos relacionados

## Autorización y alcance

- Añadir una bandeja `/legajos` protegida por `consultar_legajos` y una ficha `/clientes/{cliente}/legajo` con binding implícito y `ClientePolicy`.
- La búsqueda usa el alcance de clientes derivado de los procesos visibles. Para Profesional, cada consulta de procesos se limita a `profesional_id = usuario autenticado`.
- La ficha carga los procesos con servicio, coordinador, profesional, turnos, documentos/categorías/versiones, reportes/autores, comprobantes e historial/usuario en consultas eager-loaded.
- Mantener `/clientes/{cliente}` para los datos administrativos. Su relación de procesos debe respetar el permiso y alcance antes de mostrarse.

## Archivos documentales

- Registrar una fila `DocumentoVersion` con la versión vigente anterior antes de sustituir el archivo, dentro de la misma transacción que actualiza el documento.
- Guardar path aleatorio, tipo, nombre, categoría, actor y fecha de reemplazo; conservar el archivo anterior.
- Exponer historial como metadatos. La visualización y descarga de versiones pasan por rutas autenticadas y la policy del documento. No se serializan paths al navegador.
- En la vista documental, ofrecer acciones distintas para CU12 (ver en línea) y CU19 (descargar).

## Reportes

- Implementar altas, cambios y bajas lógicas en rutas anidadas a proceso.
- Limitar creación al Profesional asignado y al Administrador; limitar edición/baja al autor o Administrador; consulta según CU24, y para Profesional solo sus propios reportes.
- Usar Form Requests existentes, bindings anidados acotados, policy y scope por proceso.

## Comprobantes

- Servir el PDF solo desde una ruta autenticada que autoriza CU14 y verifica que el archivo exista en el disco local.
- En el legajo, mostrar fecha, descripción y asociación al proceso/cliente; ningún `archivo_path` se devuelve en texto o atributos HTML.

## Interfaz

- Añadir enlace de navegación solo para usuarios con CU18.
- Mantener búsqueda y detalle responsive siguiendo Tailwind v3 y los patrones Blade existentes.
- Mostrar secciones con estados vacíos claros y datos personales/contacto, proceso, turnos, documentos/versiones, reportes, pagos e historial.
- Para Directivo, mostrar solo metadatos documentales sin vista previa, URL ni acción de archivo.

## Verificación

- PHPUnit para permisos, alcance profesional, relaciones vacías, reportes, reemplazo de versiones y archivos autorizados.
- Abrir la aplicación desde el navegador de Codex y recorrer búsqueda, ficha, interacciones autorizadas y denegación de acceso.
- Ejecutar Pint, las pruebas afectadas y `git diff --check`.
