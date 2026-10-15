# Ajustar el dashboard del Profesional

## Problema

El dashboard por rol todavía muestra al Profesional la tarjeta «Reportes recientes», cuyo conteo no respeta completamente la autorización individual de CU 24 si el proceso fue reasignado. El desglose de estados con Chart.js se habilitó para Secretario, pero la misma interpretación resulta útil para que cada Profesional revise únicamente su cartera.

## Alcance

- Mostrar al Profesional procesos activos actualmente asignados, su cola de seguimiento, turnos propios de los próximos siete días y clientes de legajos asignados.
- Mostrar el desglose de procesos activos propios en barras horizontales cuando haya al menos dos estados con casos y conservar la lista accesible como alternativa y fallback.
- Quitar «Reportes recientes» del dashboard del Profesional.
- Alinear `Reporte::visibleTo()` con la asignación actual al proceso para que consultas y agregados excluyan reportes fuera de su alcance vigente.
- Documentar el comportamiento, sin cambiar permisos ni el dashboard de otros roles.

## Fuera de alcance

- Cambiar la matriz Aequitas, las capacidades de roles, CU 40 o paneles de otros roles.
- Crear vencimientos, métricas de productividad, comparaciones entre profesionales o filtros nuevos.
- Instalar dependencias; Chart.js ya está disponible y se reutiliza su carga diferida.

## Criterios de salida

- Cada consulta, agregado y lista del Profesional queda limitada a procesos con `profesional_id` igual al usuario autenticado.
- El Profesional no recibe tarjeta de reportes y tampoco puede contar reportes de procesos reasignados.
- La tarjeta de clientes apunta a los clientes que aparecen en sus legajos asignados.
- Agenda, indicadores y enlaces conservan los permisos CU 8, CU 18, CU 20 y CU 29.
- El gráfico se presenta solo con al menos dos estados; la lista accesible cubre todos los casos y el error de carga del gráfico.
- PHPUnit focalizado, Pint, build de Vite y verificación visual con Profesional y rol comparativo pasan; la base de QA queda limpia.

## Riesgos

- Un agregado sin la condición de proceso actualmente asignado puede revelar indirectamente actividad de un caso que el Profesional ya no puede abrir.
- Reutilizar el gráfico sin conservar los valores en el árbol accesible dificultaría su lectura por tecnologías de asistencia; el componente mantiene ambas representaciones.
