# Diseño de HU-09: alta transaccional de cliente

> El flujo y su registro interno son una capacidad acotada de HU-09; no equivalen al módulo de notificaciones. CU-35 y HU-18 a HU-20 se completan en un módulo independiente de última fase, que revisará esta integración. Brevo y los canales externos quedan sujetos a revisión de costos.

## Flujo

1. El usuario autorizado envía los datos del cliente y `fecha_hora_inicial`.
2. La acción busca un Coordinador activo, el estado `pendiente` activo y el servicio `Consultoría`.
3. Dentro de una única transacción crea cliente, proceso inicial `pendiente`, turno `consulta_inicial` y notificación interna.
4. Si falta la configuración, se duplica DNI/correo o falla cualquier inserción, la transacción se revierte.

## Persistencia

El proceso toma la fecha del turno como `fecha_inicio`, el primer tipo jurídico disponible (`Civil`) y no asigna Profesional hasta que el flujo de admisión lo haga. El estado se resuelve por el slug estable `pendiente` y se rechaza si falta, está inactivo o fue dado de baja.

El turno conserva `profesional_id` con el identificador del Coordinador por la restricción histórica no nula. Se incorpora `coordinador_id` nullable y su relación explícita; el próximo cambio de agenda podrá separar la representación heredada sin bloquear HU-09.

La notificación usa el canal `interno`, destinatario `user_id` del Coordinador, cliente asociado, fecha de creación y estado `enviado`. Es un registro auditable, no un envío externo.

## Autorización e idempotencia

El Form Request y `ClientePolicy` mantienen `registrar_clientes`. La unicidad de DNI y correo detiene reintentos exitosos antes de crear derivados. Ambos campos quedan respaldados por índices únicos de base de datos para cubrir carreras que atraviesen la validación HTTP. La unicidad incluye registros con baja lógica, igual que las reglas `unique` actuales.

## Verificación

Las pruebas cubren Secretario y Administrador, las cuatro entidades creadas y vinculadas, rechazo por roles no autorizados, DNI y correo duplicados, falta de Coordinador, servicio o estado inicial activo, requerimiento de fecha/hora y rollback al fallar una escritura posterior a la creación del cliente, proceso y turno.
