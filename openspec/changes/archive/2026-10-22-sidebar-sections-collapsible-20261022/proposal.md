# Navegación lateral compacta y desplegable

## Why

La navegación ya está agrupada, pero el encabezado «Navegación» agrega una etiqueta redundante y todas las opciones de cada grupo siguen visibles. Esto alarga la barra lateral y dificulta localizar una función de un vistazo.

## What Changes

- Quitar el encabezado «Navegación» y mantener Dashboard agrupado bajo «Inicio».
- Reducir el tamaño y el espacio vertical de los títulos de grupo.
- Convertir Gestión jurídica, Administración e Informes en secciones colapsables independientes.
- Iniciar cada grupo cerrado salvo el que contiene la ruta actual, y conservar visibilidad según permisos.
- Mantener una interacción accesible por teclado con el estado expuesto mediante ARIA.

## Enfoque

Usar Alpine en cada grupo con `<button>`/`x-show`, un identificador para el panel y una condición Blade basada en la ruta actual. No cambiar rutas ni autorizaciones.

## Riesgos

- Un grupo activo debe expandirse al entrar directamente a una de sus pantallas para que el enlace actual siga visible.
- Reducir la jerarquía visual no debe reducir los objetivos táctiles de los controles.
