# Adaptar las pantallas operativas a la base visual Aequitas

## Problema

Las pantallas activas de clientes, turnos/agenda y comprobantes todavía mezclan colores heredados, superficies planas y tablas difíciles de usar en móvil. En clientes, la tabla desborda el viewport a 390 px.

## Alcance

- Aplicar Figtree, marino `#172538`, dorado `#8c6936` para foco/indicadores, superficies claras y texto oscuro en las vistas activas de los tres módulos.
- Adaptar listados y formularios al ancho disponible; presentar clientes como tarjetas en móvil y tabla en escritorio.
- Reordenar la presentación de detalle de cliente y turno para lectura y operación móvil.
- Mantener permisos, rutas, filtros/búsquedas, acciones, validaciones y estados existentes.
- Conservar la carga PDF y la apertura autorizada del comprobante; no crear filtros ni fichas nuevas que el módulo no ofrece actualmente.

## Restricciones

- No editar layouts, navegación, configuración de Tailwind, CSS global ni componentes compartidos.
- No cambiar lógica de backend ni ejecutar pruebas de backend.
- No introducir dependencias ni nuevos patrones de interacción.
- Usar tokens ya disponibles en `DESIGN.md` y `tailwind.config.js`.

## Criterios de salida

- Las vistas activas usan la identidad Aequitas y conservan acciones/permisos/estados.
- La vista de clientes no causa desbordamiento horizontal del viewport a 390 px y sus acciones se pueden usar con toque.
- Formularios, filtros, detalles, agenda y listados se pueden recorrer en escritorio y móvil.
- Los assets frontend compilan y los flujos UI se verifican en navegador.
- La validación no ejecuta pruebas de backend.

## Riesgos

- Las tarjetas móviles pueden ocultar columnas si no se mantienen todos los datos y acciones de cada fila.
- Reemplazar acentos visuales sin cuidar semántica podría hacer que cancelaciones, bloqueos externos o feedback pierdan significado; se conservarán rótulos y colores de estado semánticos.
