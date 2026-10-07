# Tareas: módulo independiente de notificaciones

## Decisión y preparación

- [x] Confirmar que Notificaciones se implementará como el último módulo, después de completar el núcleo funcional.
- [x] Agrupar HU-18, HU-19 y HU-20 en una lista final propia de Trello.
- [x] Señalar en OpenSpec y documentación que Brevo queda sujeto a una revisión futura de costos.
- [ ] Definir los criterios de aceptación que determinan que el núcleo funcional está terminado.
- [ ] Inventariar eventos, destinatarios y permisos que consumirá el módulo.

## Construcción del módulo final

- [ ] Revisar costos y condiciones vigentes de Brevo y decidir proveedor(es) para los canales internos, correo y WhatsApp contemplados actualmente.
- [ ] Definir contratos de integración y la matriz de eventos y destinatarios.
- [ ] Implementar la bandeja y consulta del registro correspondiente a CU-35 / HU-18.
- [ ] Implementar la generación de avisos de HU-19 dentro del módulo independiente.
- [ ] Implementar los canales aprobados para HU-20 sin acoplarlos a los módulos centrales.
- [ ] Revisar y migrar el registro interno mínimo que actualmente genera el alta de clientes.
- [ ] Validar permisos, errores, reintentos, duplicados, auditoría y funcionamiento sin proveedor externo.

## Integración y cierre

- [ ] Conectar los eventos de clientes, procesos, turnos y demás módulos después de validar Notificaciones de forma aislada.
- [ ] Ejecutar pruebas automatizadas y verificación de los flujos integrados.
- [ ] Actualizar la matriz Aequitas y la documentación final con el proveedor, canales y eventos aprobados.
