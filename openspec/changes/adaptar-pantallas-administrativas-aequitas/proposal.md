# Adaptar pantallas administrativas a la base visual Aequitas

## Problema

Las pantallas administrativas activas conservan colores índigo, tablas y controles de Breeze mientras el área autenticada ya dispone de una base visual compartida para Aequitas. La inconsistencia aparece en usuarios y roles, servicios, asignaciones a profesionales, estados de proceso, categorías documentales e informe por categoría.

## Alcance

- Aplicar Figtree, azul marino `#172538`, dorado `#8c6936`, superficies claras, texto oscuro y tokens compartidos a los módulos administrativos enumerados.
- Adaptar listados, búsqueda/filtros, formularios, modales, acciones, feedback y estados vacíos o de carga presentes.
- Asegurar lectura, interacción con teclado/táctil y composición responsive en escritorio y móvil.
- Conservar comportamiento Livewire/Alpine, rutas, datos, permisos por rol y restricciones funcionales actuales.

## Restricciones

- Trabajar en worktree gestionado aislado, rama dedicada desde `dev` en `70b1dcd`.
- No modificar layouts, navegación, Tailwind config, CSS global, componentes compartidos ni vistas de módulos asignados a otros chats.
- No cambiar permisos, políticas, controladores, componentes Livewire, consultas ni esquema.
- No ejecutar pruebas de backend por instrucción directa.
- Compilar assets y validar interfaz e interacciones en navegador integrado en escritorio y móvil.

## Áreas afectadas

Vistas Blade de usuarios/roles, servicios activos, asignación de servicios a profesionales, catálogo de estados de proceso, categorías documentales e informe documental por categoría; propuesta/diseño/tareas OpenSpec del cambio.

## Enfoque

Migrar utilidades locales al vocabulario visual existente sin alterar la estructura de datos ni las directivas Blade/Livewire/Alpine. Mantener superficies operativas claras, tablas con encabezados y filas legibles, botones marinos, foco dorado y acciones destructivas semánticamente rojas. Conservar el contraste de estados positivos y errores.

## Criterios de salida

- Las vistas activas indicadas utilizan la identidad Aequitas y los tokens ya definidos.
- Listados, filtros/búsquedas, formularios, modales, feedback y estados vacíos siguen operando con las condiciones de rol existentes.
- La interfaz no introduce desbordamiento horizontal en móvil y mantiene acciones alcanzables por tacto y teclado.
- Assets de frontend compilados y pantallas verificadas visualmente en escritorio y móvil.
- Diff limitado a módulos asignados y artefactos OpenSpec; sin pruebas de backend.

## Riesgos

- Las pantallas usan combinaciones de Blade, Livewire y Alpine; una edición de estilos debe dejar intactos eventos y bindings.
- La superficie de informe documental es Blade tradicional y debe conservar parámetros GET, errores y paginación.
- Las capturas y estados observados dependen de datos/permisos disponibles en la base local de verificación.
