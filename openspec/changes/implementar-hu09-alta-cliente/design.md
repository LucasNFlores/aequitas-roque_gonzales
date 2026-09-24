# Diseño de HU-09: alta transaccional de cliente

## Flujo

1. El usuario autorizado envía los datos del cliente y `fecha_hora_inicial`.
2. La acción busca un Coordinador activo y el servicio `Consulta legal inicial`.
3. Dentro de una única transacción crea cliente, proceso inicial `pendiente`, turno `consulta_inicial` y notificación interna.
4. Si falta la configuración, se duplica DNI/correo o falla cualquier inserción, la transacción se revierte.

## Persistencia

El proceso toma la fecha del turno como `fecha_inicio`, el primer tipo jurídico disponible (`Civil`) y no asigna Profesional hasta que el flujo de admisión lo haga.

El turno conserva `profesional_id` con el identificador del Coordinador por la restricción histórica no nula. Se incorpora `coordinador_id` nullable y su relación explícita; el próximo cambio de agenda podrá separar la representación heredada sin bloquear HU-09.

La notificación usa el canal `interno`, destinatario `user_id` del Coordinador, cliente asociado, fecha de creación y estado `enviado`. Es un registro auditable, no un envío externo.

## Autorización e idempotencia

El Form Request y `ClientePolicy` mantienen `registrar_clientes`. La unicidad de DNI y correo detiene reintentos exitosos antes de crear derivados; la base de datos cubre carreras que atraviesen la validación HTTP.

## Verificación

Las pruebas cubren Secretario y Administrador, las cuatro entidades creadas y vinculadas, rechazo por roles no autorizados, duplicados, falta de Coordinador, falta de servicio y requerimiento de fecha/hora.
