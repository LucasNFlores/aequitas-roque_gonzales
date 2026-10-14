# Propuesta: navegación interna acorde a rol y permisos

## Contexto

La HU-21 requiere que Secretario, Profesional, Coordinador, Directivo y Administrador encuentren rápidamente sus funciones. La matriz Aequitas V2 online es la autoridad: contiene 40 casos de uso y cinco roles. La navegación principal ya oculta varios enlaces mediante `@can`, pero la navegación del layout heredado de servicios no tiene el mismo catálogo. Los destinos funcionales existentes deben enlazarse con los permisos técnicos que protegen sus rutas; los accesos de inicio, perfil, guía técnica y cierre se mantienen comunes a usuarios autenticados.

## Alcance

- Alinear las navegaciones autenticadas principal y heredada para mostrar los mismos destinos operativos disponibles.
- Condicionar cada destino operativo al permiso que protege su ruta y representa el CU autorizado en la matriz.
- Mantener los accesos comunes a todos los usuarios internos.
- Conservar las denegaciones de rutas existentes y cubrir acceso directo para roles sin autorización.
- Especificar la navegación por permisos en `gestion-juridica`.

## Fuera de alcance

- Crear pantallas aún no implementadas, incluyendo CU35 y el dashboard específico CU40.
- Cambiar permisos, rutas, políticas, capacidades de datos o la matriz online.
- Reordenar capacidades por rol fuera de la navegación.

## Criterios de salida

- Ambas navegaciones presentan enlaces operativos equivalentes y cada uno usa el permiso correspondiente a su destino.
- Para cada destino cubierto, la UI muestra el enlace a los roles autorizados de la matriz y lo oculta al resto.
- Las rutas protegidas continúan denegando el acceso directo a los roles no autorizados.
- La navegación responde en escritorio y móvil.
- Pruebas PHPUnit, Pint, build de Vite y recorrido de navegador para los cinco roles completan correctamente; se revisa `git diff --check`.

## Riesgos

- El layout heredado podría divergir otra vez si conserva una lista duplicada; se verificará paridad de enlaces con pruebas de vista.
- La matriz online lista CU35 y CU40, pero sus pantallas no están implementadas en este alcance; no deben aparecer enlaces muertos.
