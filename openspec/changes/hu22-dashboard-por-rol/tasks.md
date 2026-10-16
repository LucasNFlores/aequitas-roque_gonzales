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
- [x] Revisar diff, crear commits atómicos y fusionar en `dev` sin publicar la rama.

## Ampliación aprobada: dashboard del Coordinador

- [x] Priorizar el total de procesos activos, las colas autorizadas de admisión, asignación y seguimiento, y turnos de los próximos siete días.
- [x] Excluir clientes totales y reportes recientes de sus tarjetas principales sin cambiar navegación ni permisos.
- [x] Reutilizar el gráfico horizontal diferido con dos o más estados y mantener la lista accesible/fallback.
- [x] Cubrir alcance, colas, tarjetas, gráfico, estados vacíos y comparación con otros roles mediante pruebas PHPUnit.
- [x] Actualizar OpenSpec y la explicación funcional del dashboard.
- [x] Recorrer la UI real en IAB con Coordinador, rol comparativo y viewport móvil de 375 px; limpiar datos QA.
- [x] Revisar el diff, validar, crear commits atómicos y fusionar la rama local en `dev` sin publicar.

## Ampliación aprobada: panel operativo del Administrador

- [x] Acordar procesos activos globales por estado, colas de admisión/asignación, turnos próximos de siete días y cuentas internas vigentes; mantener separado el gráfico por servicio del Directivo.
- [x] Implementar el panel Admin manteniendo los scopes y las vistas de los otros cuatro roles.
- [x] Añadir PHPUnit para cifras globales, estados terminales, bajas lógicas, colas, límites de agenda, cuentas vigentes y estados vacíos.
- [x] Ejecutar PHPUnit focalizado, Pint, build de Vite y validación OpenSpec de HU-22.
- [x] Verificar Admin y rol comparativo en IAB, incluida vista a 375 px, enlaces, gráfico y estados vacíos; eliminar y comprobar solo los datos QA propios.
- [x] Revisar diff y árbol limpio, crear commits atómicos y fusionar la rama local terminada en `dev` sin publicar.
