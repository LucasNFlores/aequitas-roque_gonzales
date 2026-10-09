# CU34: acceso independiente para asignar servicios

## Necesidad

El permiso `asignar_especialidad_servicio` ya autoriza al Coordinador, pero la interfaz está dentro de `/usuarios`, que exige `listar_usuarios` y expone tareas de CU25–27. La matriz online Aequitas V2 define CU34 para Coordinador, Directivo y Administrador; Secretario y Profesional no están autorizados.

## Alcance

- Incorporar una página CU34 propia, visible en la navegación para los roles autorizados sin ampliar `listar_usuarios`.
- Mostrar solamente profesionales activos y sus servicios activos.
- Permitir asignar y retirar servicios mediante una acción protegida también en servidor.
- Conservar el endpoint actual con autorización y validar que los servicios existan y sigan activos.
- Cubrir los cinco roles en navegación/pantalla, peticiones directas y persistencia.

## Fuera de alcance

- Cambiar CU25–27, el catálogo de servicios (CU33), los permisos de la matriz o la hoja de Drive.
- Editar o mover la tarjeta de Trello.

## Criterios de salida

- Coordinador accede a la página CU34 y puede asignar/retirar servicios sin `listar_usuarios`.
- Directivo y Administrador conservan el acceso.
- Secretario y Profesional no ven el acceso en UI y reciben 403 en la página y en peticiones directas.
- Solo profesionales y servicios activos válidos quedan vinculados, con persistencia sin duplicados.
- Pruebas PHPUnit, validación en navegador para los cinco roles, Pint, build disponible y `git diff --check` completan correctamente.

## Riesgos

- La lista debe limitarse a profesionales activos para no revelar datos ajenos a CU34.
- La regla genérica `exists` puede aceptar servicios con baja lógica; filtrar explícitamente `deleted_at` en ambos flujos.
