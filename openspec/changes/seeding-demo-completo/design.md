# Diseño

`DatabaseSeeder` seguirá preparando roles, estados, categorías, cuentas base, servicios y clientes base. Al final llamará a un seeder dedicado de demostración. Este localizará cada fixture por una clave estable y reconocible (correo, DNI, nombre de proceso o ruta de archivo), restaurará únicamente fixtures propios con baja lógica y no hará borrados ni reemplazos masivos.

El seeder añadirá profesionales sintéticos y los vinculará a los servicios que pueden atender. Para cada cliente de demostración creará un proceso con un estado vigente y una trayectoria explícita de historial fechada desde el límite de seis meses hasta fechas recientes. Los reportes, comprobantes, documentos, versiones y turnos usarán relaciones existentes y claves estables para conservar la idempotencia.

La agenda reservará turnos internos sólo en huecos libres; los compromisos externos se ubicarán en días completos libres. Las cancelaciones quedarán con estado `cancelado`. Los PDFs se guardarán bajo `storage/app/private/demo/`, en rutas administradas por el seeder. No se modifican migraciones.

La prueba feature usará SQLite en memoria y `Storage::fake('local')`, ejecutará el seeder dos veces y comprobará cantidades estables, roles, relaciones, estados y presencia de PDFs.
