## ADDED Requirements

### Requirement: Mantener utilizable el dashboard con el selector de tema

El dashboard autenticado MUST completar su carga y permanecer interactivo cuando se renderiza el selector de tema. La sincronización de la etiqueta y el icono MUST NOT producir actualizaciones continuas que bloqueen la interfaz.

#### Scenario: El Administrador cambia el tema desde el dashboard

- **WHEN** un Administrador abre el dashboard y alterna entre modo claro y oscuro
- **THEN** el dashboard permanece interactivo y el control refleja el tema activo
- **AND** la preferencia elegida se conserva al navegar entre páginas autenticadas
