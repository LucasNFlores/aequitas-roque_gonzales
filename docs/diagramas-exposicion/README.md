# Diagramas de exposición

Esta carpeta contiene la versión de los diagramas de secuencia preparada para la exposición. Se construyeron leyendo el código implementado y reemplazan, solo para la presentación, a los diagramas previos de `docs/diagrama de secuencia/`.

Los archivos de `docs/diagrama de secuencia/` y `docs/diagrama-clases/` no fueron modificados.

## Índice

| Markdown | Mermaid | Casos de uso | Flujo |
| --- | --- | --- | --- |
| `CU-09-cargar-documento.md` | `CU-09-cargar-documento.mermaid` | CU 9 | Carga de un PDF en el legajo desde la pantalla Livewire |
| `CU-15-15.1-admision-rechazo.md` | `CU-15-15.1-admision-rechazo.mermaid` | CU 15 y CU 15.1 | Admisión o rechazo de un proceso pendiente |
| `CU-20-actualizar-estado.md` | `CU-20-actualizar-estado.mermaid` | CU 20 | Cambio de estado de un proceso autorizado |

## Cómo renderizar

- **GitHub o VS Code:** abrir el archivo `.md` y usar la vista previa. GitHub renderiza Mermaid de forma nativa.
- **Mermaid Live:** abrir o pegar el archivo `.mermaid` correspondiente.
- **Presentación local:** usar la vista previa de VS Code o exportar el diagrama a PNG/PDF desde Mermaid Live.

Cada diagrama existe dos veces: el `.md` con la explicación y el bloque embebido, y el `.mermaid` con solo el código listo para importar.

No hace falta ejecutar `npm run build` ni levantar la aplicación para ver los diagramas.

## Criterio de coincidencia con el código

Cada mensaje del diagrama se corresponde con una llamada, validación o efecto real del sistema:

- Se usan los nombres reales de los componentes Livewire, policies, actions y modelos.
- Se representan las ramas de error y validación que existen en el código.
- No se agregaron notificaciones porque estos tres flujos no las generan.
- La carga inicial de documentos no crea `DocumentoVersion`; las versiones aparecen al reemplazar un documento.
- CU 15.1 se integra en el diagrama de CU 15 porque comparte la misma pantalla y el mismo punto de entrada.

## Trazabilidad general

| Caso de uso | Elemento del diagrama | Código que lo respalda |
| --- | --- | --- |
| CU 9 | Botón “Nuevo documento” y formulario | `resources/views/livewire/documentos/index.blade.php:14`, `:111` |
| CU 9 | Apertura del modal y autorización | `app/Livewire/Documentos/Index.php:54` |
| CU 9 | Validación, guardado y transacción | `app/Livewire/Documentos/Index.php:86`, `:117`, `:134`, `:198`, `:201` |
| CU 9 | Permiso de creación | `app/Policies/DocumentoPolicy.php:25` |
| CU 9 | Modelo persistido | `app/Models/Documento.php` |
| CU 15 | Acción de admisión | `app/Livewire/Procesos/Index.php:93` |
| CU 15 | Autorización | `app/Policies/ProcesoPolicy.php:60` |
| CU 15 | Lógica de admisión | `app/Actions/Procesos/AdmitirProceso.php:12` |
| CU 15.1 | Apertura y confirmación del rechazo | `app/Livewire/Procesos/Index.php:104`, `:120` |
| CU 15.1 | Autorización | `app/Policies/ProcesoPolicy.php:65` |
| CU 15.1 | Lógica de rechazo | `app/Actions/Procesos/RechazarProceso.php:12` |
| CU 15 y 15.1 | Cambio de estado e historial | `app/Models/Proceso.php:62`, `:79`, `:158` |
| CU 20 | Apertura y guardado del cambio | `app/Livewire/Procesos/Index.php:173`, `:184` |
| CU 20 | Autorización y alcance del Profesional | `app/Policies/ProcesoPolicy.php:80` |
| CU 20 | Catálogo de estados activos | `app/Models/EstadoProceso.php:34` |
| CU 15, 15.1 y 20 | Historial persistido | `database/migrations/2026_09_08_000003_create_historial_estados_proceso_table.php` |

## Guion de exposición

### CU 9 — Cargar documento

1. La pantalla de documentación es un componente Livewire, no un formulario del controlador.
2. El botón “Nuevo documento” ejecuta `createDocumento()`, que autoriza ver el proceso y crear documentos.
3. Al guardar, `saveDocumento()` valida nombre, categoría activa y PDF de hasta 20 MB.
4. El archivo se guarda en el disco local y el registro se crea dentro de una transacción.
5. Si falla la transacción, se elimina el archivo subido para no dejar basura.

### CU 15 y CU 15.1 — Admisión y rechazo

1. El Coordinador o Administrador opera desde la bandeja de procesos.
2. La autorización usa `ProcesoPolicy`: `admitir_procesos` para admitir y `registrar_motivo_rechazo` para rechazar.
3. La Action correspondiente abre una transacción, bloquea el proceso y verifica que siga pendiente.
4. El estado destino debe existir en el catálogo y estar activo.
5. En el rechazo, el motivo es obligatorio y se guarda tanto en el proceso como en el historial.
6. El historial se crea automáticamente desde el evento `updated` del modelo `Proceso`.

### CU 20 — Actualizar estado

1. El Profesional asignado, Coordinador o Administrador abre el cambio de estado.
2. La policy valida el permiso y, si es Profesional, que el proceso esté asignado.
3. El sistema exige un estado existente, activo y distinto al actual.
4. `admitido` y `rechazado` quedan bloqueados porque tienen sus propios flujos.
5. La transición se guarda en una transacción y el historial se registra automáticamente.

## Aclaraciones para preguntas del docente

- **¿Por qué CU 9 no pasa por `DocumentoController`?** Porque la pantalla usa Livewire. El controlador y `StoreDocumentoRequest` existen para la ruta HTTP `POST /procesos/{proceso}/documentos`, pero el flujo de la interfaz es el del componente.
- **¿Dónde se registra el historial?** No se crea manualmente en las Actions. `Proceso::transitionTo()` guarda el estado y el evento `updated` del modelo llama a `recordStateChange()`.
- **¿Por qué no aparecen notificaciones?** Porque los flujos implementados de CU 9, CU 15, CU 15.1 y CU 20 no crean `Notificacion`. Los diagramas anteriores las mostraban, pero el código actual no las genera.
- **¿Por qué hay tres diagramas si hay cuatro casos de uso?** Porque CU 15 y CU 15.1 comparten pantalla, actor y flujo de autorización; se representan como dos ramas del mismo diagrama.

## Validación

- Sintaxis Mermaid validada en los seis archivos con el parser de Mermaid 11.
- Contenido de cada `.mermaid` verificado como idéntico al bloque embebido en su `.md`.
- Comparación de cada mensaje contra el código indicado en la tabla de trazabilidad.
- Pruebas ejecutadas: `php artisan test --compact tests/Feature/DocumentoCategoriaFlowTest.php tests/Feature/Livewire/ProcesoIndexTest.php`.
- Resultado: 36 pruebas aprobadas, 204 aserciones.
