# Dashboard operativo por rol (HU-22)

## Problema

La ruta autenticada `/dashboard` solo muestra el mensaje de inicio de sesión. Las personas internas no tienen una vista de indicadores y trabajo priorizado para sus capacidades autorizadas. La matriz online no agrega un HU-22 general; CU 40 concede un dashboard al Directivo y Administrador, pero deja sus indicadores pendientes. El usuario confirma que HU-22 debe derivar datos y acciones exclusivamente de los permisos y alcances ya vigentes.

## Alcance

- Reemplazar la pantalla vacía por un dashboard para Secretario, Profesional, Coordinador, Directivo y Administrador.
- Presentar procesos activos, pendientes de admisión y pendientes de asignación solo cuando el rol pueda consultar o resolver esa etapa.
- Presentar próximas citas únicamente a roles con CU 8; limitar al Profesional a su agenda y a procesos asignados.
- Presentar métricas de clientes, reportes y usuarios únicamente cuando el usuario cuente con la capacidad de consulta correspondiente.
- Enlazar métricas y listas a bandejas existentes autorizadas; la página no agrega rutas de mutación ni concede CU nuevos.
- Mostrar estados vacíos explicativos cuando no existan registros o una cola no corresponda al rol.

## Fuera de alcance

- Modificar la matriz online, los permisos del `RoleSeeder`, reglas de negocio o asignaciones de procesos.
- Exponer agenda al Directivo, documentos o archivos desde métricas, o legajos/reportes de procesos no asignados al Profesional.
- Crear una cola para tareas que el sistema no registra o cuya acción el rol no puede ejecutar.
- Incorporar gráficos o dependencias nuevas.

## Criterios de salida

- Cada métrica, lista y enlace corresponde a una capacidad ya autorizada.
- Las consultas del dashboard aplican `visibleTo($user)` antes de agregar, ordenar o limitar resultados.
- Los cinco roles pueden abrir la ruta y reciben contenido adaptado; una URL directa no amplía su alcance.
- El Administrador conserva su acceso global de superadministrador.
- Las vistas son accesibles y responsive, reutilizan componentes y tokens existentes, y comunican estados vacíos.
- PHPUnit focalizado, Pint y build de Vite pasan; se recorre la UI real por los cinco roles.

## Riesgos

- Una agregación sin scope de modelo podría revelar datos ajenos aunque no muestre enlaces; cada consulta debe iniciar desde su scope autorizado.
- Los nombres de estados son configurables; las colas usan los slugs estables `pendiente`, `admitido`, `finalizado` y `rechazado` ya establecidos por el dominio.
- CU 40 deja sus indicadores pendientes en OpenSpec; esta propuesta los define acotados a datos consultables y no cambia la matriz.
