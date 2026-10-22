# Tareas: evitar el bloqueo del dashboard por el selector de tema

## Preparación

- [x] Revisar la especificación funcional de dashboard y el layout autenticado.
- [x] Identificar el ciclo entre el observador global y la escritura de la etiqueta.
- [x] Definir alcance, criterio de salida y riesgo antes de editar la implementación.

## Implementación

- [x] Eliminar el observador de mutaciones que vuelve a disparar `syncThemeToggle`.
- [x] Añadir una prueba focalizada que confirme que el layout mantiene disponible el selector de tema.

## Validación y cierre

- [x] Ejecutar la prueba PHPUnit focalizada (1 test, 13 aserciones), el build frontend y comprobar en navegador la carga e interacción del tema.
- [x] Revisar el diff y `git diff --check`.
- [ ] Integrar la rama terminada en `dev` según las instrucciones del repositorio.
