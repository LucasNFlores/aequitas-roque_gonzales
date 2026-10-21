## ADDED Requirements

### Requirement: Aplicar una base visual compartida de Aequitas al área autenticada

El área autenticada MUST ofrecer una identidad coherente con el acceso mediante tokens reutilizables de color, tipografía, foco, radios y elevación. Figtree MUST ser la tipografía general y Playfair Display MUST reservarse para acentos editoriales. El azul marino MUST identificar las acciones primarias y la navegación; el dorado MUST usarse de manera medida para acentos y foco. Los textos, controles y estados MUST conservar contraste y legibilidad administrativa. La navegación MUST conservar permisos y destinos existentes, y MUST seguir siendo operable en escritorio, móvil y teclado. Los componentes compartidos MUST mantener labels y errores asociados, feedback semántico y estados interactivos visibles. Esta base MUST NOT exigir el rediseño inmediato de pantallas de módulos.

#### Scenario: Persona usa la navegación autenticada

- **WHEN** una persona autorizada navega en escritorio o en un viewport móvil
- **THEN** la marca, el estado activo y el foco visible usan la base visual Aequitas
- **AND** los destinos visibles continúan determinados por los mismos permisos
- **AND** el menú móvil se puede abrir y cerrar con controles accesibles

#### Scenario: Persona opera formularios y acciones compartidas

- **WHEN** una persona enfoca, envía o encuentra una validación en un control compartido
- **THEN** el foco es claramente visible, los errores se muestran junto al campo y el texto mantiene contraste legible
- **AND** botones primarios, secundarios y destructivos conservan estados distintos para hover, active y disabled

#### Scenario: Una pantalla administrativa conserva su contenido de módulo

- **WHEN** una pantalla de módulo usa clases o componentes locales
- **THEN** la introducción de la base compartida no requiere reorganizar ni rediseñar su contenido funcional
