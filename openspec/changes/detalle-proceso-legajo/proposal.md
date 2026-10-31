# Detalle de proceso desde el legajo

## Problema

La ficha del legajo presenta el contenido completo de cada proceso en una página larga. No ofrece un resumen seleccionable ni una URL propia para consultar un proceso, y sus acciones quedan separadas de la información contextual del caso.

## Alcance

- Resumir cada proceso visible en la ficha del legajo y enlazarlo a su detalle.
- Incorporar una vista de detalle con datos principales, turnos, documentos, reportes, comprobantes e historial.
- Mantener la carga y autorización dentro del cliente y del alcance de procesos permitido al usuario.
- Mostrar acciones de edición solo cuando las policies o permisos existentes autoricen esa operación; proteger también las solicitudes directas.
- Alinear la composición visual con la referencia de Figma y los tokens actuales de Aequitas.
- Cubrir permisos y navegación con pruebas PHPUnit y una comprobación real del flujo en navegador.

## Restricciones

- No se agregan permisos ni se altera la matriz de roles.
- El Profesional solo puede abrir procesos que tenga asignados.
- CU18 mantiene la consulta del legajo separada de los permisos de editar datos, cambiar estados, documentos, reportes, turnos y comprobantes.
- No se exponen rutas ni contenido documental fuera de las policies actuales.

## Criterios de salida

- Los procesos visibles se presentan como resúmenes con enlace a su detalle.
- La URL de detalle verifica pertenencia al cliente, CU18 y alcance del proceso antes de cargar relaciones.
- El detalle conserva los datos y acciones permitidos que ya se consultaban en el legajo.
- Los controles de edición coinciden con la policy del recurso y el servidor rechaza los accesos directos no autorizados.
- La ficha funciona en escritorio y móvil y conserva el tema Aequitas.
- Pruebas dirigidas y recorrido interactivo local confirman roles autorizados y denegados.

## Riesgos

- **Acceso cruzado por URL:** resolver el proceso a través de la relación del cliente y aplicar `visibleTo` antes de cargar sus datos.
- **Controles que filtran datos o funciones:** reutilizar policies existentes en las vistas y volver a autorizar cada escritura en el servidor.
- **Pérdida de información al resumir la ficha:** mover la consulta integral al detalle y comprobar secciones vacías y pobladas.
