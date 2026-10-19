# Formalizar la especificación vigente de gestión jurídica

## Why

La especificación base ya recoge el alcance aprobado, pero su formato en prosa no puede ser validado estrictamente por OpenSpec: faltan Purpose, Requirements y escenarios. Además, esta propuesta conservaba el conteo obsoleto de 38 casos y su diseño repetía una tabla de permisos incompleta que omitía CU 38.

## What Changes

- Reorganizar la especificación base en requisitos verificables y escenarios, con un escenario por cada uno de los 41 casos de uso actuales.
- Mantener en la matriz online la autoridad funcional y de permisos, y conservar las decisiones complementarias aprobadas que no la contradicen.
- Corregir la descripción del cambio y quitar la tabla de permisos duplicada en favor de referencias a la matriz y su transcripción local.
- Declarar el cambio como documental en .openspec.yaml mediante skip_specs: true, ya que no modifica el comportamiento del producto ni añade una delta funcional.

## Capabilities

### New Capabilities

Ninguna.

### Modified Capabilities

Ninguna. La especificación base se formaliza sin alterar su alcance funcional vigente; por eso este cambio no declara deltas.

## Alcance conservado

La matriz vigente contiene CU 1–38, incluidos los códigos fraccionarios, para Secretario, Profesional, Coordinador, Directivo y Administrador. Administrador tiene todos los CU. CU 13 y CU 14 autorizan registrar y consultar comprobantes, sin baja u ocultación.

## Criterios de salida

- La especificación base contiene Purpose, Requirements y escenarios OpenSpec válidos para los 41 CU.
- Cada rol y restricción concuerda con la hoja online y con docs/aequitas-matriz-modulos-funciones-permisos-v2.md.
- Las decisiones vigentes de conservación histórica, procesos adicionales, estados, categorías y servicios se preservan sin duplicar la matriz de permisos.
- Las dos validaciones estrictas del cambio y de la especificación base terminan correctamente; se ejecuta también la validación global estricta.
- git diff --check no informa errores.

## Fuera de alcance

No se modifica código de producto, la hoja online ni los permisos implementados. La formalización documental no declara que los 41 casos estén implementados.

## Riesgos

- Una diferencia entre la hoja y la documentación local podría volver a introducir permisos incorrectos; se contrasta cada caso contra la hoja actual antes de cerrar.
- Un requisito agregado para satisfacer al validador podría ampliar el alcance; cada escenario se vincula a un CU o a una decisión complementaria ya aprobada.
