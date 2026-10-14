# Diseño: navegación interna acorde a rol y permisos

## Decisiones

- Usar los permisos técnicos ya asignados por `RoleSeeder` y exigidos por las rutas como condición de visibilidad (`@can`); no comprobar nombres de rol en Blade.
- Mantener iguales los destinos operativos de `layouts.navigation` y `layouts.static-navigation`, con textos y rutas nombradas coherentes.
- Mantener inicio, perfil, guía técnica y cierre visibles para todos los usuarios autenticados. La guía no corresponde a un CU de la matriz y ya está disponible a todos.
- No publicar enlaces CU35 o CU40 hasta que existan destinos funcionales y su autorización de servidor.
- No modificar rutas ni permisos: la autorización directa ya se evalúa en middleware/policies y se probará para denegaciones relevantes.

## Verificación

- Pruebas feature de ambas navegaciones con Secretario, Profesional, Coordinador, Directivo y Administrador, contrastando enlaces con permisos sembrados.
- Comprobar accesos directos negados para roles no autorizados y permitidos para roles autorizados en destinos representativos.
- Compilar assets y recorrer el menú en navegador a escritorio y móvil, verificando enlaces visibles, ocultos y navegación móvil.
