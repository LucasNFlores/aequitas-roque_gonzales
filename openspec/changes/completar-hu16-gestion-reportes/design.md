# Diseño: completar HU-16

## Autoría al registrar

`reportes.profesional_id` seguirá identificando al Profesional asignado que es autor del reporte. Se agregará `registrado_por_id` nullable con referencia al usuario que ejecutó CU21. Un Profesional registra para sí mismo; un Administrador registra para el profesional asignado al proceso. CU21 no permite crear un reporte sin profesional asignado. Las filas históricas conservarán `registrado_por_id = null`.

## Autorización y consultas

Se mantiene Spatie como fuente de autorización. La policy de creación se aplica además de middleware y Form Request. Para CU22/CU23 se mantiene autoría profesional o Administrador; las rutas directas siguen comprobando policy. La consulta CU24 del Profesional exige simultáneamente autoría propia y asignación actual al proceso; Coordinador, Directivo y Administrador acceden por legajos autorizados. SoftDeletes excluye bajas de consultas operativas y el model binding excluye el historial directo de reportes dados de baja.

## Interfaz

El legajo conserva formulario y acciones actuales. La tarjeta del reporte mostrará autor profesional y, cuando sea distinto, quién lo registró. Se conservará el mensaje de estado vacío y se pedirá confirmación explícita antes de la baja lógica.

## Verificación

Extender `ReporteFlowTest` con registro administrativo, atribución y visibilidad, baja lógica y rutas directas de roles autorizados/denegados. Ejecutar PHPUnit y Pint en Docker, compilar Vite, y recorrer alta, edición, baja e historial en navegador autenticando cada rol de la matriz.
