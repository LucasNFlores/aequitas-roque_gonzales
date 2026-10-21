# Diseño

## Decisiones

- Aplicar los tokens visuales existentes por medio de utilidades Tailwind: marino para acciones primarias y enlaces operativos, dorado para foco, neutros claros para paneles y superficies.
- Mantener Figtree como tipografía de interfaz. No agregar Playfair Display a datos, controles ni títulos operativos.
- Reutilizar componentes de formulario y botones ya existentes en clientes; las vistas Livewire de turnos/comprobantes conservan sus bindings y directivas.
- Usar tabla en escritorio y tarjetas con la misma información en móvil para clientes. Los listados extensos de turnos y comprobantes se presentarán en tarjetas móviles y tablas en pantallas mayores para evitar desbordes.
- Aplicar radios y sombras de tarjeta/panel ya definidos. Mantener rojo para cancelar y feedback rojo/verde/ámbar según su semántica.
- No tocar controladores, componentes Livewire PHP, permisos, rutas, navegación, layout, CSS ni configuración.

## Verificación

- Compilar assets frontend para detectar clases no generadas.
- Iniciar Laravel en el puerto 8002 con assets compilados.
- Recorrer clientes (filtros, listado, detalle, alta/edición), turnos (filtros, agenda, detalle, alta/reprogramación y compromiso externo) y comprobantes (búsqueda de cliente, carga, estados y consulta autorizada) en escritorio y móvil.
- Revisar que el viewport de 390 px no tenga scroll horizontal y que las acciones visibles sigan respetando permisos.
- No ejecutar pruebas de backend.

## Criterios de salida

- Diseño consistente con `DESIGN.md` en las vistas listadas.
- Ningún flujo/acción/estado existente se pierde en escritorio o móvil.
- `npm run build` termina correctamente y la revisión visual de navegador concluye.

## Riesgos y mitigaciones

- Una tabla de escritorio puede resultar extensa en móvil; las tarjetas conservan cada campo y acción sin depender de scroll horizontal de página.
- El tipo externo, cancelado y bloqueado tiene semántica propia; sus textos e indicadores de estado se mantienen diferenciados.
- El formulario de comprobantes es largo en móvil; se conserva el orden lógico, se adapta a una columna y se mantienen mensajes de error junto a cada campo.
