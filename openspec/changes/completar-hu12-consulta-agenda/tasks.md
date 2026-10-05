# Tareas HU-12

## Preparación

- [x] Revisar CU8, permisos, documentación de turnos y la implementación existente.
- [x] Corregir en Trello los criterios que confundían consulta con escritura o pedían disponibilidad no definida.

## Implementación

- [x] Añadir cobertura de regresión para filtros vacíos, validar el alcance del Profesional antes de renderizar resultados y probar rangos inclusivos.
- [x] Completar la cobertura PHPUnit de roles, estados históricos y agenda vacía.
- [x] Hacer responsive la vista y representar las reglas de disponibilidad documentadas sin acciones de escritura.

## Validación y cierre

- [x] Ejecutar las pruebas afectadas y Pint.
- [x] Probar la UI autenticada en navegador en escritorio y móvil.
- [x] Revisar `git diff --check`, el diff y criterios de salida; integrar en `dev` al finalizar.
