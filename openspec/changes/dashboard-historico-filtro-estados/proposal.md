# Propuesta: filtrar estados en la vista histórica de CU 40

## Problema

La vista histórica actual fuerza incluir todos los estados no terminales y ofrece solo dos casillas para finalizado y rechazado. No permite analizar un estado concreto ni combinar un subconjunto de estados.

## Alcance

- Permitir seleccionar uno o varios estados del catálogo y de los estados presentes en el historial.
- Mostrar todos los estados seleccionados por defecto, incluidos finalizado y rechazado.
- Validar que una selección enviada no esté vacía, no repita estados y contenga solo slugs permitidos.
- Conservar el scope `Proceso::visibleTo($user)`, el conteo de procesos distintos por estado y la aclaración de no aditividad.
- Conservar fechas, métricas CU 40, permisos y acceso Admin existentes.

## Criterios de salida

- PHPUnit cubre selección única y múltiple, selección vacía, slug inválido, permisos y scope.
- Pint, pruebas afectadas, build Vite y OpenSpec estricto pasan.
- La interfaz del filtro se revisa en IAB y a 375 px.
- Cambio confirmado en una rama dedicada e integrado localmente en `dev`, sin push.

## Riesgos

- La ausencia de checkboxes en el GET puede significar primera visita o selección vacía; el formulario debe enviar un marcador para distinguirlos.
- Los procesos pueden aparecer en más de un estado; la vista debe indicar que las barras no suman un total único.
