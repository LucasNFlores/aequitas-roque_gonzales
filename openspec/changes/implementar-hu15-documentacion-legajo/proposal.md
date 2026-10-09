# Implementar HU-15: gestionar documentación del legajo

## Objetivo

Completar CU 9, 10, 11, 12 y 19 para cargar y reemplazar PDFs en procesos, conservar el historial y permitir visualización y descarga separadas según la matriz vigente.

## Alcance

- Aceptar únicamente PDFs de hasta 20 MB asociados a un proceso existente y una categoría activa.
- Conservar la identidad del documento al reemplazarlo y guardar cada archivo/metadato anterior como versión histórica.
- Dar de baja lógicamente documentos, registrar quién lo hizo y cuándo mediante la auditoría instalada, y conservar archivos físicos.
- Proteger rutas directas, acciones Livewire y controles de interfaz con permisos CU9/10/11/12/19 y alcance de proceso.
- Responder 404 ante un archivo físico ausente, un documento/version fuera del proceso o una versión ajena al documento.
- Añadir pruebas focalizadas para archivos ausentes, aislamiento entre procesos, permisos denegados, baja auditada y versiones históricas.

## Fuera de alcance

- HU-10 de gestión general del legajo, otras tarjetas, cambios a la matriz, avisos/notificaciones, eliminación física y restauración de documentos.
- Cambios de permisos para cualquier otro módulo.

## Criterios de salida

- CU9/10/11 solo están disponibles para Secretario y Administrador.
- CU12 solo está disponible para Secretario, Profesional asignado, Coordinador y Administrador; Directivo no puede ver el PDF ni el historial por URL directa.
- CU19 solo está disponible para Profesional asignado, Coordinador y Administrador; Secretario y Directivo reciben 403.
- El reemplazo conserva referencia e identidad y mantiene accesibles, bajo las mismas reglas, todas las versiones anteriores.
- La baja persiste actor y fecha sin quitar el archivo ni el historial.
- Las pruebas backend y una revisión real de la interfaz cubren carga, reemplazo, visualización, descarga y roles.
- Cambios formateados, diff revisado y validaciones completas antes del merge local a `dev`; no hacer push.

## Riesgos

- Un archivo puede guardarse antes de fallar la persistencia: borrar únicamente el nuevo archivo de reemplazo/carga al revertir el guardado, sin tocar versiones previas.
- La autorización del proceso y la del documento deben verificarse conjuntamente para evitar IDOR en rutas anidadas.
- Las versiones históricas deben tener comprobación de existencia y pasar por la misma policy del documento vigente.
- La baja debe registrar un evento de auditoría del modelo sin crear una segunda fuente de auditoría ni borrar archivos.
