# Diseño: alta CU 38 sin profesional y asignación posterior por CU 16

## Decisión

CU 38 creará el proceso adicional sin profesional asignado. La petición de alta rechazará `profesional_id` incluso si el valor corresponde a un usuario activo. La persona autorizada asignará luego un profesional desde CU 16.

Esta separación sigue la matriz online: CU 38 solo permite Secretario y Administrador, mientras que CU 16 permite Secretario, Coordinador y Administrador. Evita duplicar en el alta las reglas y efectos de la asignación.

## Flujo

1. Secretario o Administrador completa CU 38 desde la ficha de un cliente existente.
2. El Form Request rechaza campos `profesional_id`, `estado` y otros atributos controlados por el servidor.
3. La acción crea en una transacción el proceso pendiente con `profesional_id` y `honorarios` nulos.
4. Secretario, Coordinador o Administrador abre la asignación CU 16 desde la bandeja de procesos.
5. La acción de dominio comprueba usuario activo con rol Profesional, servicio compatible y proceso sin asignación previa; en la transacción fija el profesional y el `costo_servicio` vigente.
6. Ante una validación fallida, no cambia ni crea el proceso.

## Interfaz y validación

- Quitar el selector de profesional del formulario de alta adicional y comunicar que se asignará luego por CU 16.
- Mantener `StoreProcesoRequest` como límite del servidor: `profesional_id` debe estar ausente de la petición (`missing`).
- La acción de creación solo persiste atributos del proceso validados y no consume un profesional enviado por el cliente.
- Reutilizar la pantalla, permisos y acción existentes para CU 16; no introducir un segundo flujo de asignación.

## Compatibilidad y honorarios

La asignación CU 16 valida el rol Profesional y que el usuario no esté dado de baja, además de la relación con el servicio del proceso. La misma transacción guarda el profesional y los honorarios con el costo de referencia actual del servicio, sin aceptar importes del navegador.

## Verificación

- PHPUnit: alta CU 38 sin profesional; petición CU 38 manipulada con `profesional_id`; rechazos de CU 16 para usuario inactivo, rol incorrecto y profesional no habilitado; persistencia y honorarios del costo vigente al asignar.
- Navegador: confirmar que el alta no ofrece selección profesional, crear un proceso sin asignación y asignarlo desde CU 16 con el honorario actualizado.

## Riesgos

- Un cliente HTTP puede añadir campos ausentes del formulario; la regla `missing` los rechaza antes de la persistencia.
- El valor de referencia cambia con el tiempo; el honorario se captura al momento de la asignación, no al crear el proceso.
