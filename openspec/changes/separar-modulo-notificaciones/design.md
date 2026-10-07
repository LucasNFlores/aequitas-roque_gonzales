# Diseño: módulo independiente de notificaciones

## Orden de entrega

1. Completar y aceptar los módulos del núcleo funcional.
2. Revisar costos y condiciones vigentes de Brevo y decidir el proveedor o combinación de proveedores para los canales vigentes.
3. Implementar el módulo de Notificaciones de forma independiente.
4. Validar bandeja, generación de avisos, auditoría y resultados de entrega sin acoplarlos a los flujos de negocio.
5. Integrar los productores de eventos de clientes, procesos, turnos y otros módulos mediante contratos explícitos.

## Frontera del módulo

Las operaciones de negocio no llamarán directamente a Brevo ni a otro proveedor. El módulo será responsable de consultar notificaciones, registrar destinatarios y resultados, y gestionar la entrega por los canales aprobados. La interfaz de eventos permitirá que los flujos centrales continúen aunque el proveedor externo no esté configurado o una entrega falle.

## Proveedor y costos

Brevo es el proveedor contemplado actualmente, pero la variación de costos impide tratarlo como una decisión vigente. Su precio, condiciones y adecuación se revisarán al comenzar la fase final. La selección se documentará antes de configurar credenciales o habilitar envíos externos. El alcance vigente conserva los canales interno, correo y WhatsApp.

## Compatibilidad con el estado actual

El alta de clientes genera hoy un registro interno dirigido al Coordinador. Ese registro es una capacidad acotada existente, no una bandeja ni un sistema completo de notificaciones. La integración final debe revisar ese punto de entrada y trasladarlo al contrato del módulo sin introducir dependencias de proveedor en el flujo de alta.

## Historias

- CU-35 / HU-18: consulta de bandeja y registro.
- HU-19: generación ante eventos relevantes.
- HU-20: canales de correo y WhatsApp con proveedor confirmado en la fase final.

## Validación futura

La implementación deberá cubrir permisos, persistencia, fallos de proveedor, reintentos, duplicados, trazabilidad y operación sin configuración externa. La matriz de eventos y destinatarios se cerrará antes de la integración con el núcleo.
