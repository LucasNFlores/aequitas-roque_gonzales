# Tareas HU-22

## Especificación y alcance

- [x] Confirmar matriz online y mapear CU 8, 18, 21–24, 25–30 y 40 a capacidades existentes.
- [x] Acordar que cada rol use solo datos consultables y acciones ya autorizadas.
- [x] Definir para CU 40 los mismos indicadores permitidos por el alcance del Directivo; mantener CU 40 compatible sin ampliar permisos.

## Implementación

- [x] Sustituir la vista de bienvenida del dashboard por indicadores y colas derivadas de permisos.
- [x] Aplicar scopes de proceso, cliente, turno y reporte en el servidor antes de agregar o limitar.
- [x] Mostrar enlaces solo a bandejas y pantallas autorizadas, con estados vacíos informativos.
- [x] Actualizar la documentación afectada sin alterar la transcripción de la matriz.

## Verificación y entrega

- [x] Agregar pruebas PHPUnit focalizadas para los cinco roles, datos fuera de alcance, colas, enlaces, estados vacíos y acceso invitado.
- [x] Ejecutar la prueba focalizada, Pint de PHP y build de Vite.
- [x] Recorrer dashboard real en navegador con Secretario, Profesional, Coordinador, Directivo y Administrador; verificar links, estados vacíos y denegaciones/acotamiento, y validar el reflujo móvil a 375 px.
- [x] Limpiar los registros QA identificados y confirmar que no quede ninguno.
- [x] Revisar `git diff --check`, diff y stage de rutas explícitas; commit Conventional Commit y merge local a `dev` cuando el árbol quede limpio.

## Ampliación aprobada: indicadores y gráfico exclusivos del Secretario

- [x] Reemplazar el total de clientes del Secretario por altas visibles de los últimos 30 días calendario.
- [x] Activar Chart.js solo cuando el dashboard del Secretario muestre al menos dos estados activos con casos; conservar lista accesible y fallback.
- [x] Cubrir límites de período, bajas lógicas, estados vacíos, colas existentes y ausencia de novedades para los otros roles.
- [x] Validar con PHPUnit y Pint, compilar Vite y recorrer en navegador Secretario y un rol sin el gráfico; revisar reflujo responsive y limpiar datos QA.
- [ ] Revisar diff, crear commits atómicos y fusionar en `dev` sin publicar la rama.
