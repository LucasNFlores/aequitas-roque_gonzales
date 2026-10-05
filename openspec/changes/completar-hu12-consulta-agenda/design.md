# Diseño HU-12: consulta de agenda profesional

## Decisiones

- Reutilizar `App\\Livewire\\Turnos\\Agenda`, `TurnoPolicy` y `Turno::visibleTo()`; no crear una ruta ni un modelo paralelo.
- Autorizar CU8 en el ingreso y volver a aplicar `visibleTo()` antes de los filtros. El Profesional queda fijado a su propio `user_id`, incluso si modifica propiedades públicas.
- Tratar el filtro vacío como ausencia de selección para Secretario, Coordinador y Administrador; validar cualquier ID informado como usuario Profesional activo y conservar una prueba de regresión para el filtro vacío.
- Aplicar fechas inicial y final inclusivas, con máximo de 90 días y consulta acotada, relaciones precargadas y paginación.
- Consultar todos los estados para que cancelados permanezcan visibles como historial; disponibilidad ignora cancelados y considera los externos programados como bloqueo de fecha. Los conflictos de alta/reprogramación se validan en los flujos de escritura de HU-11/HU-13.
- Presentar turnos y bloqueos definidos por el dominio. No calcular franjas libres, duración ni solapamientos por intervalos sin datos aprobados.
- Mantener una vista responsive consistente con Tailwind y los componentes Livewire existentes. Cada turno muestra sus datos, tipo y ocupación; externos programados indican bloqueo de jornada, cancelados indican que no ocupan disponibilidad, y no hay acciones de escritura.

## Verificación

- PHPUnit: matriz de roles, alcance por URL/propiedad Livewire, filtro vacío, profesional válido/inválido, inclusividad de fechas, rango límite/excesivo, errores y agenda vacía con cancelados/externos.
- Navegador de Codex: ingresar a agenda, filtrar un profesional/período, verificar resultados y estado vacío, confirmar ausencia de controles de escritura y revisar la disposición en escritorio y móvil.
- Ejecutar formateador PHP y `git diff --check` si se modifica código.
