# Tareas

- [ ] 1. Preservar el proceso de origen al agendar un turno desde el detalle; después de guardar, volver al mismo detalle.
- [ ] 2. Preservar el proceso de origen al editar los datos de un cliente desde el detalle; después de guardar, volver al mismo detalle.
- [ ] 3. Corregir el retorno desde el historial de reporte para volver al detalle del proceso del reporte.
- [ ] 4. Corregir el retorno desde gestión documental para volver al detalle del proceso administrado.
- [ ] 5. Agregar pruebas de regresión para los cuatro recorridos y confirmar que el acceso anidado y la transición de estado conservan su contexto.
- [ ] 6. Cubrir errores de validación y autorización del PATCH de estado, el caso permitido sin `motivo` (opcional en la petición, sin excepción en la actualización) y los casos permitidos existentes.
- [ ] 7. Agregar pruebas unitarias de las reglas de Proceso::transitionTo que pueden probarse sin base de datos.
- [x] 8. Actualizar README.md para retirar el pendiente obsoleto y describir la visualización protegida del PDF.
- [x] 9. Sincronizar docs/diagrama-clases/diagrama-clases.md, docs/diagrama-clases/diagrama-clases.mermaid, docs/diagrama-clases/Diagrama de clases.drawio y docs/diagrama-clases/funcionamiento-diagrama-clases.md con modelos y migraciones, incluyendo proceso obligatorio en Turno y explicación de Legajo como consulta compuesta.
- [x] 10. Actualizar docs/diagrama de secuencia/CU-4.mermaid y docs/diagrama de secuencia/CU-4.1.mermaid para reflejar los flujos Livewire vigentes y su relación con Proceso.
- [x] 11. Ejecutar las pruebas PHPUnit dirigidas informadas, revisar resultados y registrar sus comandos y conteos en el informe.
- [x] 11.1. Incorporar el resultado final del nuevo caso HTTP de renderizado y POST del formulario de reporte sin mezclarlo con el conteo anterior de ReporteFlowTest.
- [x] 12. Crear docs/informe-pruebas-tercera-entrega-20261022.md con caso, precondiciones, datos, resultado esperado, resultado obtenido y evidencia real disponible; registrar la discrepancia de creación de reporte como pendiente.
- [x] 13. Validar OpenSpec, revisar el diff completo y confirmar que no se alteraron permisos ni reglas de negocio no incluidas en el delta.
- [ ] 14. En el entorno local aislado preparado por el coordinador y con datos de prueba, recorrer en navegador cliente → legajo → admitir/rechazar → asignar profesional → turno → documento → reporte → historial; observar una entrada inválida y un acceso denegado, y registrar evidencia sin usar datos existentes. La creación manual por el Profesional asignado no se considera comprobada mientras el formulario observado en navegador no se reconcilie con la prueba HTTP que confirma renderizado y POST.
