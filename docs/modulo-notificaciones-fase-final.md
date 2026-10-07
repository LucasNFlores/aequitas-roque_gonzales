# Módulo de Notificaciones — fase final

## Decisión

Las notificaciones forman parte del alcance final del sistema, pero se entregarán como un módulo independiente en la última fase. Primero deben completarse y aceptarse los demás módulos funcionales; luego se construirá y probará Notificaciones por separado y, al final, se conectará con clientes, procesos, turnos y demás eventos del sistema.

Esta secuencia separa la entrega. No elimina CU-35 ni las historias HU-18, HU-19 y HU-20 del alcance final.

## Costos y proveedor

Los costos y condiciones de Brevo cambiaron. No se contratará ni configurará Brevo durante las fases actuales. El alcance vigente contempla avisos internos, correo y WhatsApp. Al comenzar el módulo se revisarán las condiciones vigentes de Brevo antes de decidir el proveedor o combinación de proveedores. Brevo queda como candidato, no como una decisión cerrada.

## Límites entre módulos

- El núcleo funcional debe operar sin depender de una entrega de correo, WhatsApp o de un proveedor externo.
- El módulo de notificaciones tendrá su propio registro, consulta, reglas de entrega y manejo de resultados.
- La comunicación entre el núcleo y Notificaciones se definirá mediante contratos o eventos claros. La conexión de los productores de eventos se hará después de validar el módulo de forma aislada.
- El registro interno mínimo que hoy se crea al dar de alta un cliente es una capacidad transitoria y acotada; no significa que CU-35 ni HU-18 a HU-20 estén terminados. Se revisará al integrarlo.

## Alcance diferido

- **CU-35 / HU-18:** consulta de la bandeja o registro de notificaciones.
- **HU-19:** generación de notificaciones ante eventos relevantes, como admisión, rechazo, asignación y cambios de turnos.
- **HU-20:** envío por correo y WhatsApp, con el proveedor elegido tras revisar costos.
- **Integración:** conectar el módulo validado con los eventos de los módulos existentes y cubrir fallos, reintentos, permisos y auditoría.

## Condición para iniciar

La fase puede comenzar cuando el resto de los módulos funcionales esté terminado y aceptado. Antes de habilitar un proveedor externo deben documentarse la revisión de costos, la decisión de proveedor y la configuración para los canales contemplados en el alcance vigente.

## Referencias

- [Tarjeta paraguas del módulo final en Trello](https://trello.com/c/voW5TBHi/75-m%C3%B3dulo-de-notificaciones-integraci%C3%B3n-final)
- [Especificación del sistema en OpenSpec](../openspec/specs/gestion-juridica/spec.md)
- [Cambio OpenSpec del módulo independiente](../openspec/changes/separar-modulo-notificaciones/proposal.md)
