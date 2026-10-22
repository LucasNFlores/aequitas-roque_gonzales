# Diseño de la tercera entrega integradora

## Contexto de dominio y documentación

La implementación vigente declara Cliente y Proceso como modelos. Cliente agrupa sus procesos; cada Proceso pertenece a un Cliente y enlaza turnos, documentos, comprobantes, reportes e historial de estados. Legajo es la consulta compuesta que muestra datos del cliente y sus procesos mediante controlador y vistas. No se agregará una entidad de dominio Legajo.

La migración vigente exige que cada Turno tenga proceso_id. Las relaciones de Turno y Proceso conservan turnos históricos incluso cuando el proceso recibe baja lógica. Los diagramas deberán reflejar esta asociación obligatoria y conservar la opcionalidad de coordinador_id que sí existe en la tabla.

La visualización de comprobantes usa una ruta autenticada y autorizada que transmite el PDF con disposición inline. README debe describir esa capacidad implementada sin ampliar la matriz de permisos ni presentar la ruta como descarga distinta de lo que hace el código.

## Retorno de navegación

Cuando el usuario parte del detalle anidado de un proceso, las cuatro acciones deben conservar el cliente y proceso de origen:

1. El formulario de turno devuelve al detalle del proceso después de guardar.
2. La edición de datos del cliente devuelve al detalle del proceso desde el que se inició.
3. La acción de retorno del historial de reporte vuelve al detalle del proceso del reporte.
4. La acción de retorno de gestión documental vuelve al detalle del proceso administrado.

El contexto no concede autorización. Cada destino continúa resolviendo el cliente y proceso por sus relaciones y aplicando las autorizaciones existentes. Las entradas directas a formularios conservan su comportamiento de retorno habitual.

## Pruebas

- Añadir cobertura de feature para los cuatro retornos y una regresión para acceso anidado y actualización de estado.
- En las pruebas de PATCH de estado, verificar los casos de error de validación y autorización observados en la auditoría, además del camino permitido existente.
- Añadir pruebas unitarias de Proceso::transitionTo para las reglas que puedan verificarse con modelos en memoria: estado inexistente o inactivo y transición al mismo estado. No simular una persistencia exitosa como prueba unitaria.
- Mantener las pruebas de integración existentes para escrituras que requieren base de datos.
- Recorrer en navegador, en el entorno local aislado preparado por el coordinador y usando solo datos de prueba, el guion cliente → legajo → admitir/rechazar → asignar profesional → turno → documento → reporte → historial. Observar también una entrada inválida y un acceso denegado, y registrar evidencia reproducible. No usar servidores ni navegadores conectados a datos existentes.

## Actualización de documentos y evidencias

- README: reemplazar el aviso obsoleto de visualización pendiente con la descripción respaldada por ruta, policy, controlador y vista.
- Diagramas de clases: sincronizar diagrama-clases.md, diagrama-clases.mermaid, Diagrama de clases.drawio y funcionamiento-diagrama-clases.md con app/Models y las migraciones. Explicar Cliente, Proceso y la consulta de Legajo sin inventar un modelo.
- Diagramas de secuencia: actualizar CU-4.mermaid y CU-4.1.mermaid para describir componentes Livewire, TurnoScheduler, validación y proceso obligatorio según cada flujo vigente.
- Informe: crear docs/informe-pruebas-tercera-entrega-20261022.md. Cada caso contendrá caso, precondiciones, datos, resultado esperado, resultado obtenido y evidencia. Incluir solo comandos, salidas o referencias observados en la ejecución final.

## Compatibilidad y restricciones

- No cambia la matriz Aequitas, los permisos, las políticas de acceso ni las reglas de negocio.
- El contexto se usa solo como destino interno de retorno y no habilita acceso cruzado.
- No se agrega un modelo, migración o tabla Legajo.
- El cambio referencia detalle-proceso-legajo y formalizar-turnos-agenda-categorias como antecedentes; no reabre sus alcances.

## Verificación

Validar el cambio OpenSpec. Después de implementar, ejecutar las pruebas PHPUnit dirigidas para turnos, clientes, reportes, legajos y documentos, las nuevas pruebas de transición, y comprobar que los tres formatos de diagramas de clases permanecen alineados. Cuando el coordinador provea el entorno local aislado, completar el recorrido en navegador con datos de prueba. Completar el informe con evidencia real de los resultados automatizados y del recorrido.
