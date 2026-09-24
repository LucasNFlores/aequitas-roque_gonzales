# Diseño: creación de procesos adicionales

## Contexto

CU 1 registra un cliente nuevo y crea su proceso inicial de Consultoría con un turno inicial. CU 38 es un flujo distinto: parte de un cliente ya existente y sólo crea otro proceso para ese cliente.

## Flujo propuesto

1. Secretario o Administrador abre un cliente existente desde el listado o su detalle.
2. Inicia la acción «Crear proceso adicional».
3. Completa los atributos obligatorios del proceso y selecciona un servicio y un Coordinador activos.
4. El servidor autoriza CU 38, valida las referencias y persiste el proceso ligado al cliente seleccionado con estado `pendiente`.
5. La aplicación muestra el nuevo proceso y deja la agenda sin modificaciones. Un turno posterior se crea sólo por los casos de uso de agenda.

## Autorización

- Incorporar un permiso técnico específico, por ejemplo `crear_procesos_adicionales`, otorgado a Secretario y Administrador.
- Proteger tanto la entrada de interfaz como la ruta y la acción de servidor mediante policy o autorización equivalente.
- Profesional, Coordinador y Directivo deben recibir denegación sin que se creen datos.
- La autorización no debe depender de un identificador de cliente enviado por el navegador: el cliente debe obtenerse desde la ruta y verificarse en el servidor.

## Persistencia y validación

- Consultar sólo clientes, servicios, Coordinadores y estados habilitados para uso operativo; conservar las reglas existentes de baja lógica.
- Validar los campos requeridos por el modelo de proceso: nombre, descripción, fecha de inicio, tipo, servicio y coordinador, además del cliente de la ruta.
- Resolver el estado inicial por la clave estable `pendiente`; si no existe o está inactivo, rechazar la operación sin persistir datos parciales.
- Persistir únicamente un `Proceso` asociado al cliente. No modificar ni duplicar el registro de `Cliente` y no crear un `Turno`.
- Ejecutar la creación dentro de una transacción si la resolución de estado y el guardado no pueden completarse de forma atómica.

## Interfaz y navegación

- Exponer la acción en el contexto de un cliente existente, no en el formulario de alta de cliente.
- Después de crear, redirigir al detalle o legajo del proceso con una confirmación clara de que no se programó ningún turno.
- Mantener el proceso visible en el listado y sujeto a los filtros y alcances actuales.

## Pruebas de aceptación

- Secretario y Administrador pueden crear un proceso adicional pendiente para un cliente existente.
- La cantidad y los datos de clientes no cambian; el proceso conserva el `cliente_id` seleccionado.
- No se crea un turno como consecuencia de la operación.
- Profesional, Coordinador y Directivo reciben respuesta prohibida.
- Cliente, servicio, Coordinador o estado inicial inválidos o inactivos no crean un proceso.
- Un reenvío de formulario debe resolverse sin crear un duplicado involuntario.

## Decisiones de implementación diferidas

- La política concreta para un posible duplicado funcional se definirá al diseñar la pantalla, porque la matriz no prohíbe más de un proceso del mismo tipo para el mismo cliente.
- Si el catálogo de servicios usa un nombre distinto de «Consultoría», CU 38 debe usar el servicio seleccionado y no asumir el servicio inicial de CU 1.
