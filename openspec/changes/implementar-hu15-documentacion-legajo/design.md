# Diseño de HU-15: gestión documental del legajo

## Autorización

- Mantener los permisos Spatie ya existentes, alineados con la matriz: Secretario/Administrador para `cargar_documentacion`, `reemplazar_documentacion` y `eliminar_documentacion`; Secretario/Profesional/Coordinador/Administrador para `visualizar_documentacion`; Profesional/Coordinador/Administrador para `descargar_documentacion`.
- Hacer cumplir la policy en cada acción del controlador y Livewire además del middleware de rutas. Los enlaces y controles de interfaz se representan solo si la policy los permite.
- El Profesional queda acotado al proceso que tiene asignado. Directivo no recibe acceso documental aunque pueda consultar un legajo por otro caso de uso.
- Para versión histórica, verificar que proceso, documento y versión estén encadenados y autorizar con `view` o `download` sobre el documento.

## Archivos y versiones

- Validar PDF y máximo de 20 MB en Form Requests y en Livewire. La carga exige proceso válido/no dado de baja y categoría activa/no dada de baja.
- Guardar archivos con nombres no predecibles dentro del disco privado `local`; no eliminar el original cuando se reemplaza.
- En una transacción, crear `DocumentoVersion` con ruta y metadatos anteriores y después actualizar el registro estable del documento.
- Si falla la persistencia, eliminar solo el nuevo archivo; nunca borrar el archivo vigente ni versiones anteriores.
- La vista directa sirve PDF inline y la descarga usa disposition attachment; ambos endpoints y los de versiones responden 404 si el archivo no existe.

## Baja y auditoría

- Conservar `SoftDeletes` y activar el contrato/trait de OwenIt Auditing que el proyecto ya usa en `User`. El evento `deleted` conserva actor, URL/IP/contexto y fecha del registro de auditoría junto a los valores del documento.
- El archivo vigente y las versiones permanecen en almacenamiento y base de datos. No se duplica la auditoría en una columna propia.
- Excluir los documentos dados de baja de la vista ordinaria mediante el scope normal de SoftDeletes. No exponer borrado físico ni restauración en HU-15.

## Interfaz

- Mantener una única pantalla Livewire por proceso con carga, reemplazo, baja, enlaces separados de ver/descargar y sección de versiones históricas.
- Aplicar `@can` a cada control y volver a autorizar en cada acción Livewire. Mantener links a endpoints protegidos por servidor.
- No agregar notificaciones ni avisos nuevos.

## Verificación

- Probar permisos y alcance por rol en endpoints directos, incluyendo Directivo, Secretario y Profesionales no asignados.
- Probar carga inválida, categoría/proceso no válidos, archivo faltante, documento de otro proceso y versión que no corresponde al documento.
- Probar reemplazo/baja sin pérdida de archivos, metadatos de versiones, actor de baja, y separaciones inline/attachment.
- Ejecutar pruebas focalizadas y Pint; realizar verificación manual de la UI en el navegador con datos de prueba limitados.
