# Completar el sistema de gestion juridica

## Objetivo

Documentar el alcance funcional necesario para que la aplicacion cumpla con la descripcion del sistema interno de gestion del estudio juridico.

Este cambio es documental. No autoriza la implementacion de codigo.

## Situacion actual

La aplicacion cuenta con Laravel, autenticacion de empleados, perfiles de usuario, roles mediante Spatie, auditoria y CRUD de servicios. El cliente es un registro administrativo sin acceso al sistema. Tambien existen migraciones y modelos para clientes, procesos, turnos, documentos, reportes, comprobantes de pago y notificaciones, pero sus controladores principales estan sin implementar y no hay pantallas ni rutas completas para esos modulos.

## Resultado esperado

Contar con una guia verificable de requisitos, flujos, permisos, estados, pantallas y tareas pendientes para planificar la implementacion por etapas.

## Fuera de alcance

- Implementar controladores, modelos, rutas o vistas.
- Agregar dependencias frontend.
- Integrar ARCA, correo, WhatsApp o servicios externos.
- Ejecutar migraciones o modificar datos.
