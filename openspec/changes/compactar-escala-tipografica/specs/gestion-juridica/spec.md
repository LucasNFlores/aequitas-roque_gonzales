## ADDED Requirements

### Requirement: Mantener una escala tipográfica compacta y legible

La interfaz MUST usar un tamaño base de 12 px y una escala compartida de texto compacta, con valores redondeados. El contenido de lectura normal MUST NOT usar tamaños inferiores a 12 px. La jerarquía entre texto auxiliar, contenido y títulos MUST mantenerse en los temas Claro y Oscuro.

#### Scenario: Leer el contenido de una pantalla

- **WHEN** el usuario abre una pantalla pública o autenticada
- **THEN** el texto base y el contenido normal se presentan a 12 px como mínimo
- **AND** los títulos y las etiquetas conservan una jerarquía tipográfica consistente

#### Scenario: Cambiar el tema

- **WHEN** el usuario alterna entre Claro y Oscuro
- **THEN** la escala tipográfica permanece igual y legible en ambos temas
