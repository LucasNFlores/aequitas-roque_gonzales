# Tercera entrega integradora

## Problema

La revisión del estado en origin/dev al 22 de octubre de 2026 encontró documentación que ya no describe el código: README marca pendiente la visualización de comprobantes aunque existe una ruta protegida que responde el PDF inline y un enlace desde el listado; los diagramas todavía representan Turno.proceso_id como opcional y CU-4 usa un controlador y un Form Request que no reflejan el flujo Livewire actual. La explicación del modelo debe dejar claro que Cliente y Proceso son modelos de dominio y que Legajo es una vista de consulta, no una clase Eloquent.

La auditoría de navegación detectó cuatro retornos que pierden el proceso de origen: después de agendar desde el detalle, después de editar el cliente desde el detalle, al regresar del historial de un reporte y al salir de la gestión documental. La especificación vigente no expresa ese comportamiento de retorno, por lo que se propone agregar un requisito acotado.

La auditoría de errores PATCH encontró además que la petición permite omitir `motivo`, pero `ProcesoController::updateState()` accede a esa clave como si siempre existiera y lanza una excepción. Se requiere cubrir y manejar el caso de omisión de acuerdo con la validación existente, sin cambiar el requisito funcional.

No se encontró en los documentos versionados ni en el historial revisado un informe previo de pruebas ni una plantilla de clase. El informe de esta entrega debe registrar evidencia obtenida al ejecutar las pruebas actuales, sin reutilizar cifras históricas.

## Alcance

- Preservar el retorno al mismo detalle de proceso en los cuatro recorridos de navegación confirmados.
- Añadir cobertura de regresión para esos recorridos y cubrir los errores de la petición PATCH de cambio de estado identificados en la auditoría, incluida la omisión permitida de `motivo` sin excepción.
- Añadir pruebas unitarias sin base de datos para las reglas de transición de Proceso que lo permitan.
- Actualizar README y los diagramas de clases y secuencia con base en la implementación vigente.
- Explicar el modelo centrado en Cliente y Proceso sin representar Legajo como clase persistente.
- Crear el informe de pruebas de la tercera entrega con los campos autorizados: caso, precondiciones, datos, resultado esperado, resultado obtenido y evidencia.
- Recorrer en navegador el flujo integrador en un entorno local aislado, incluyendo una entrada inválida y un acceso denegado.

## Fuera de alcance

- Cambiar roles, permisos, reglas de negocio de turnos o estados, o la matriz Aequitas.
- Crear un modelo o una tabla Legajo.
- Reabrir o duplicar el trabajo funcional de detalle-proceso-legajo o formalizar-turnos-agenda-categorias.
- Modificar código o pruebas antes de revisar y aprobar esta propuesta, su diseño y sus tareas.

## Enfoque

Usar detalle-proceso-legajo como base de la navegación desde el detalle y formalizar-turnos-agenda-categorias como referencia del dominio de turnos. La implementación debe propagar solamente el identificador de retorno conocido del cliente y del proceso, y construir destinos internos permitidos por el servidor. Mantendrá las autorizaciones vigentes y las rutas directas existentes.

Actualizar las fuentes Mermaid, Markdown y draw.io de manera coordinada. Comparar atributos y relaciones con los modelos y migraciones actuales, incluyendo la obligatoriedad de proceso_id en Turno. Corregir CU-4 y CU-4.1 para que reflejen Livewire y el requisito de proceso asociado.

Ejecutar las pruebas dirigidas después de implementar los cambios y documentar únicamente resultados observados en esa ejecución. No se cambiarán requisitos de dominio salvo el nuevo requisito de retorno al detalle cuando una acción comienza desde ese detalle.

## Criterios de salida

- Los cuatro recorridos de origen regresan al mismo detalle de proceso después de completar la acción, con pruebas de regresión.
- Los accesos anidados y las transiciones de estado que ya conservan contexto siguen haciéndolo.
- Un recorrido en navegador, ejecutado con datos de prueba en el entorno local aislado preparado para esta entrega, cubre cliente, legajo, admisión, rechazo, asignación de profesional, turno, documento, reporte e historial, y observa una entrada inválida y un acceso denegado.
- Las pruebas PHPUnit cubren los errores relevantes de PATCH de estado y las reglas puras de transición que no requieren persistencia.
- Una petición PATCH válida sin `motivo` no produce una excepción; los casos de validación, autorización y actualización permitida existentes siguen cubiertos.
- README describe la visualización privada existente de comprobantes y no la marca como pendiente.
- Los artefactos de clases coinciden entre sí y con los modelos y migraciones actuales; no presentan Legajo como modelo.
- CU-4 y CU-4.1 describen los componentes actuales y la asociación obligatoria con Proceso.
- El informe presenta caso, precondiciones, datos, resultados esperados y obtenidos y evidencia reproducible, sin cifras no verificadas.
- La diferencia entre la observación de navegador (el formulario no aparece para el Profesional asignado) y la prueba HTTP (renderizado y POST confirmados) queda investigada y reconciliada antes de declarar verificada manualmente la creación por ese perfil.
- El delta de especificación queda limitado al comportamiento de retorno desde acciones iniciadas en el detalle de proceso.

## Riesgos

- Un contexto de retorno mal validado podría redirigir a destinos externos o a otro proceso; construir el destino con rutas internas conocidas y comprobar la pertenencia y autorización vigentes.
- Los formatos Mermaid, Markdown y draw.io pueden divergir si se corrige solo una representación; validarlos en conjunto.
- Un informe preparado antes de ejecutar las pruebas podría presentar resultados supuestos; completar resultados y evidencias después de la ejecución.
- Algunos casos no tienen cobertura específica hoy; las tareas deben agregar aserciones sin debilitar las pruebas existentes.
- La prueba HTTP nueva sobre renderizado y POST del formulario de reporte no coincide con su visibilidad observada en navegador para el Profesional asignado; hasta resolver la diferencia, no afirmar que ese paso se verificó manualmente.
- La corrección del acceso a `motivo` debe respetar su opcionalidad declarada por la petición y no agregar una regla nueva de negocio.
- El recorrido en navegador depende de que esté preparado el entorno local aislado; no usar datos ni servicios existentes para sustituirlo.

## Referencias

- openspec/changes/detalle-proceso-legajo/
- openspec/changes/formalizar-turnos-agenda-categorias/
- openspec/specs/gestion-juridica/spec.md
- docs/diagrama-clases/
- docs/diagrama de secuencia/
