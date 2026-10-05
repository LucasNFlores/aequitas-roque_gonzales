# Completar HU-10: consulta del legajo jurídico

## Problema

El sistema no cuenta con una entrada de búsqueda ni una vista integral del legajo. La consulta actual del cliente muestra procesos sin consolidar turnos, documentos, reportes, comprobantes e historial; además, HU-16 no tiene rutas ni operaciones implementadas y las sustituciones documentales no dejan versiones consultables. HU-17 permite cargar y listar recibos, pero no abrir el PDF mediante una ruta autorizada.

## Alcance

- Implementar búsqueda de clientes por DNI o nombre y una vista de legajo protegida por CU18.
- Mostrar únicamente los procesos accesibles para el usuario, con sus datos, turnos, documentos y versiones, reportes, comprobantes e historial de estados.
- Completar la gestión de reportes de HU-16: registrar, editar, consultar y dar de baja según rol, asignación y autoría.
- Conservar y consultar versiones anteriores al reemplazar un documento de HU-15.
- Habilitar la consulta segura del PDF de comprobantes de HU-17 para roles con CU14.
- Mantener separadas las capacidades CU18, CU12, CU19 y CU14. Directivo puede consultar metadatos del legajo, pero no obtener contenido o rutas de archivos documentales.
- Actualizar las pruebas y la documentación funcional afectada.

## Restricciones

- La matriz Aequitas de Drive sigue siendo la fuente de verdad y ya concede CU18 a Profesional, Coordinador, Directivo y Administrador.
- El Profesional solo consulta procesos asignados y sus propios reportes.
- Las policies y los alcances deben proteger solicitudes directas, bindings anidados y acciones Livewire.
- No se agregan permisos CU ni cambios a la matriz online.

## Áreas afectadas

- Rutas, controladores, policies, modelos, scopes y migraciones de Laravel.
- Componentes y vistas Blade/Livewire de clientes, documentos, reportes, comprobantes y navegación.
- Pruebas PHPUnit, `openspec/specs/gestion-juridica/spec.md` y documentación funcional existente.

## Riesgos

- **Exposición de archivos desde el legajo:** proyectar metadatos sin `archivo_path` y verificar cada endpoint de visualización/descarga con su CU.
- **Acceso profesional a procesos ajenos:** aplicar la policy y el scope al cargar la relación, no solo al ocultar acciones.
- **Consultas N+1:** eager-load de relaciones usadas y validar el conteo de consultas en la vista principal.
- **Historial previo inexistente:** la nueva migración conserva versiones futuras; las filas actuales no permiten reconstruir cambios anteriores.

## Criterios de salida

- Los cuatro roles autorizados acceden a CU18; Secretario y usuarios sin CU18 reciben denegación también por URL directa.
- Profesional solo ve procesos asignados; Coordinador, Directivo y Administrador ven los alcances autorizados.
- Se muestran estados vacíos sin errores y las relaciones se consultan sin N+1 evidente.
- Directivo no recibe rutas, contenido ni acciones de archivos documentales desde el legajo.
- CU12 y CU19 siguen controlando por separado la visualización y descarga documental; CU14 controla la vista del PDF del comprobante.
- Pruebas backend y una comprobación interactiva en el navegador de Codex cubren los flujos principales.
