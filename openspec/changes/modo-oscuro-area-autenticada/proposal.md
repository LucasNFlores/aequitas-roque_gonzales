# Modo oscuro del área autenticada

## Problema

El área autenticada ya comparte una identidad visual Aequitas, pero declara superficies, textos, bordes y estados principalmente para fondos claros. La preferencia del navegador y el uso nocturno no se reflejan en navegación ni en los módulos operativos.

## Alcance

- Añadir tokens semánticos para temas claro y oscuro, manteniendo Claro como valor inicial.
- Ofrecer selector accesible Claro / Oscuro / Sistema en la navegación autenticada, persistir la elección localmente y resolver el tema antes del primer render.
- Adaptar layout, componentes compartidos y las vistas autenticadas de todos los roles: dashboard, perfil, clientes, turnos, comprobantes, procesos, legajos, documentos, reportes, usuarios, servicios, estados y categorías.
- Revisar formularios, tablas, modales, feedback, badges y gráficos canvas en escritorio y viewport de 390 px.

## Restricciones

- No rediseñar login ni modificar permisos, rutas, consultas o lógica de negocio.
- No agregar dependencias ni ejecutar pruebas de backend.
- Conservar el aspecto Claro actual y asegurar contraste legible en ambos temas.

## Enfoque

Extender las variables semánticas CSS compartidas por tema y conservar los colores de utilidad existentes para evitar migrar cada vista a una variante explícita. Limitar la resolución de tema al documento autenticado, sincronizarla con el selector nativo y adaptar los gráficos al tema actual.

## Riesgos

- Vistas de módulos contienen colores Tailwind y algunos valores hex directos; el selector de tema debe cubrir ambos patrones y los estados dinámicos.
- Los gráficos canvas no heredan CSS y requieren volver a dibujarse con colores del tema.
- Las preferencias del sistema pueden cambiar durante una sesión cuando se eligió Sistema.

## Áreas afectadas

`resources/css/app.css`, `tailwind.config.js`, layout y navegación autenticados, `resources/js/`, vistas activas bajo `resources/views/`, `DESIGN.md` y OpenSpec.
