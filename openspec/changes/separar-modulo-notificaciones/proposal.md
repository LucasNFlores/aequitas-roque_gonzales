# Separar Notificaciones como módulo de fase final

## Problema

Los requisitos de notificaciones atraviesan clientes, procesos y turnos, pero los costos y condiciones de Brevo cambiaron. Implementar ahora envíos acoplaría el núcleo funcional a una decisión de proveedor que debe revisarse. También existe un registro interno mínimo durante el alta de clientes que podría confundirse con la entrega completa del módulo.

## Objetivo

Conservar las notificaciones en el alcance final, organizarlas como un módulo independiente y posponer su construcción e integración hasta que el resto del núcleo funcional esté terminado y aceptado.

## Alcance

- Agrupar CU-35 y las historias HU-18, HU-19 y HU-20 bajo un único módulo final.
- Definir una frontera entre los eventos del núcleo y el registro, consulta y entrega de notificaciones.
- Construir y probar el módulo antes de conectarlo con clientes, procesos, turnos y otros productores de eventos.
- Revisar costos y condiciones vigentes de Brevo al iniciar la fase; confirmar entonces el proveedor o combinación de proveedores para los canales vigentes.
- Revisar e integrar el registro interno mínimo que actualmente se crea al registrar clientes.

## Restricciones

- No contratar, configurar ni integrar Brevo durante las fases actuales.
- Las funciones principales de clientes, procesos y turnos deben seguir operando sin depender de entregas externas.
- La decisión de diferir la implementación no elimina CU-35 ni el alcance final de las notificaciones internas, por correo y WhatsApp.
- Brevo es un candidato sujeto a la revisión de costos; no se considera proveedor confirmado.

## Enfoque

El módulo mantendrá su propio ciclo de desarrollo y validación. Una vez aprobado, se conectará al núcleo mediante contratos o eventos explícitos, sin llamadas directas desde los módulos de negocio a un proveedor externo.

## Criterios de salida

- HU-18, HU-19 y HU-20 están agrupadas en Trello como módulo de fase final.
- La documentación y OpenSpec explican el orden, la independencia y la revisión de costos.
- El núcleo funcional puede completarse sin configurar un proveedor externo.
- Antes de implementar canales externos existe una decisión documentada sobre precios y proveedor(es); se conservan los canales vigentes salvo una decisión funcional posterior.
- El módulo se valida de forma aislada antes de conectar los eventos existentes.
