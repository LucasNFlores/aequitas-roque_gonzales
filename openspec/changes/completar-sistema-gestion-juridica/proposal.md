# Alinear la gestión jurídica con la matriz funcional y de permisos

## Objetivo

Actualizar la especificación del sistema para que coincida con la matriz de casos de uso y permisos del documento principal **“Aequitas - Matriz de Módulos, Funciones y Permisos por Rol V2”**. La matriz pasa a ser la fuente definitiva de alcance funcional y autorización.

Este cambio es documental. No autoriza todavía la implementación de código.

## Situación actual

La documentación de OpenSpec describe cuatro roles y varios permisos de forma genérica, mientras que la matriz define cinco roles y 38 casos de uso actuales, con permisos por operación y restricciones más precisas. También existen diferencias sobre la administración de usuarios y servicios, la gestión de turnos, el acceso del profesional a procesos y reportes, y la eliminación de información.

## Alcance

- Incorporar `Administrador` como rol autenticable propio, con alcance de superadministrador.
- Permitir que `Directivo` administre usuarios y servicios además de consultar la operación autorizada.
- Alinear los permisos de los 38 casos de uso actuales con la matriz y agregar dos casos de uso aprobados.
- Formalizar CU 28 a CU 37: listados y filtros, historial de estados, autenticación, perfil, servicios, especialidades, registro de notificaciones, estados y categorías documentales.
- Mantener como requisitos el CRUD de estados de procesos y el CRUD de categorías de documentos.
- Registrar el flujo de alta de cliente, proceso inicial de Consultoría y turno inicial con Coordinador.
- Establecer la política mixta de baja lógica, cancelación y conservación de evidencias.
- Actualizar la documentación funcional relacionada dentro de `docs/`.
- Actualizar el documento online para que refleje la nueva fuente de verdad.

## Decisiones confirmadas

- El cliente es un dato administrativo sin login, permisos ni acceso al sistema.
- El Administrador puede realizar todas las operaciones del sistema.
- El Profesional solo opera sobre procesos que tiene asignados.
- Solo Secretario y Administrador pueden crear, modificar y cancelar turnos.
- El sistema almacena el PDF descargado desde ARCA; no integra automáticamente con ARCA.
- Las notificaciones se envían internamente, por correo y por WhatsApp mediante Brevo.

## Restricciones

- Los documentos deben ser PDF de hasta 20 MB.
- Los permisos deben validarse en interfaz, rutas, políticas y consultas.
- La matriz debe conservar el código, nombre, alcance, permisos y estado de cada caso de uso.
- La implementación de código, migraciones e integraciones queda fuera de este cambio documental.

## Áreas afectadas

- `openspec/specs/gestion-juridica/spec.md`
- `openspec/changes/completar-sistema-gestion-juridica/design.md`
- `openspec/changes/completar-sistema-gestion-juridica/tasks.md`
- `docs/explicacion_sistema.md`
- `docs/transcripcion_de_docs.md`
- Matriz funcional online de Google Sheets

## Riesgos y controles

- **Permisos contradictorios:** resolverlos usando la matriz como autoridad y dejando el detalle en `design.md`.
- **Pérdida de trazabilidad:** usar baja lógica o estados de cancelación y conservar auditoría.
- **Registros históricos inconsistentes:** excluir bajas lógicas de las consultas operativas, pero mantenerlas disponibles para auditoría.
- **Diferencias futuras entre documentos:** registrar la matriz como fuente definitiva y revisar OpenSpec ante cada cambio aprobado.
