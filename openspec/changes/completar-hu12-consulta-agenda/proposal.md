# Completar consulta de agenda profesional (HU-12)

## Problema

La agenda CU8 ya existe, pero los criterios antiguos de HU-12 mezclan consulta con asignación de turnos y piden horarios libres sin definir jornadas ni duración. La interfaz tampoco explica de forma clara el bloqueo diario de un compromiso externo ni que un cancelado ya no ocupa disponibilidad. Faltan pruebas explícitas de filtros vacíos y límites de rango.

## Alcance

- Mantener HU-12 como una consulta de solo lectura.
- Validar los filtros de profesional y período, incluida la opción de todos los profesionales para los roles autorizados.
- Conservar el alcance del Profesional sobre su propia agenda y el rechazo de roles sin CU8.
- Mostrar turnos programados y cancelados como historial, aplicando las reglas existentes de disponibilidad.
- Verificar permisos, límites, rango inclusivo, errores, agenda vacía y presentación responsive con pruebas automatizadas y navegador.

## Fuera de alcance

- Crear, reprogramar o cancelar turnos; esas operaciones corresponden a HU-11 y HU-13.
- Definir jornadas laborales, duración de turnos o intervalos libres que no constan en la matriz.
- Cambiar el comportamiento funcional de HU-11, HU-13 o HU-17.

## Criterios de salida

1. Secretario, Coordinador y Administrador consultan el profesional y período seleccionados; un valor vacío conserva el alcance permitido para el rol.
2. Profesional siempre consulta su agenda, incluso al manipular parámetros o estado Livewire; Directivo y no autorizados reciben 403 sin datos.
3. Las fechas se validan y el rango no excede 90 días; los límites del período incluyen el día completo.
4. Los turnos cancelados se muestran como historial y no cuentan para disponibilidad; un turno externo programado bloquea su fecha completa.
5. La vista no expone controles de escritura ni inventa horas libres o solapamientos por duración.
6. Pasan las pruebas PHPUnit afectadas y la revisión interactiva de escritorio y móvil en el navegador.

## Riesgos

- Un filtro o parámetro alterado puede ampliar el alcance si no se vuelve a validar en el servidor.
- Mostrar intervalos libres sin horario/duración aprobados produciría información incorrecta; solo se representan ocupaciones y bloqueos definidos.
- La verificación de una pantalla autenticada puede depender de que el entorno web y los contenedores sigan disponibles.
