# Diagrama de Clases - Modelos de Dominio

## Sistema de Gestión de Turnos y Procesos

```mermaid
classDiagram
    direction LR
    class User {
        << Authenticatable >>
        << Auditable >>
        << HasRoles >>
        << Notifiable >>
        << SoftDeletes >>
        -String name
        -String email
        -String password
        -String dni
        -String telefono
        -String domicilio
        -Date fecha_nacimiento
        -Date fecha_ingreso
        +HasMany turnos()
        +HasMany reportes()
        +HasMany procesosComoProfesional()
        +HasMany procesosComoCoordinador()
        +HasMany notificaciones()
    }

    class Cliente {
        << SoftDeletes >>
        -String nombre
        -String apellido
        -String dni
        -String telefono
        -String correo
        -String domicilio
        -Date fecha_nacimiento
        +HasMany procesos()
        +HasMany comprobantesPago()
        +HasMany notificaciones()
        +HasMany turnos()
    }

    class Proceso {
        << SoftDeletes >>
        -BigInt cliente_id
        -BigInt profesional_id
        -BigInt servicio_id
        -BigInt coordinador_id
        -String nombre
        -Text descripcion
        -Date fecha_inicio
        -String tipo
        -String estado
        -String motivo_rechazo
        +BelongsTo cliente()
        +BelongsTo profesional()
        +BelongsTo coordinador()
        +BelongsTo servicio()
        +HasMany turnos()
        +HasMany documentos()
        +HasMany reportes()
    }

    class Turno {
        << SoftDeletes >>
        -BigInt cliente_id
        -BigInt profesional_id
        -BigInt proceso_id
        -DateTime fecha_hora
        -Boolean es_externo
        -String detalle_externo
        -String tipo
        +BelongsTo cliente()
        +BelongsTo profesional()
        +BelongsTo proceso()
    }

    class Servicio {
        << SoftDeletes >>
        -String nombre
        -Decimal costo_servicio
        +HasMany procesos()
    }

    class Documento {
        << SoftDeletes >>
        -BigInt proceso_id
        -String archivo_path
        -String tipo_documento
        -String nombre
        +BelongsTo proceso()
    }

    class Reporte {
        << SoftDeletes >>
        -BigInt proceso_id
        -BigInt profesional_id
        -Text contenido
        -Date fecha
        +BelongsTo proceso()
        +BelongsTo profesional()
    }

    class Notificacion {
        << SoftDeletes >>
        -BigInt user_id
        -BigInt cliente_id
        -String canal
        -Text mensaje
        -DateTime fecha_envio
        -String estado
        +BelongsTo user()
        +BelongsTo cliente()
    }

    class ComprobantePago {
        << HasFactory >>
        -BigInt cliente_id
        -String archivo_path
        -Date fecha_subida
        -String descripcion
        +BelongsTo cliente()
    }

    %% Relaciones de User
    User "1" --> "N" Turno : turnos
    User "1" --> "N" Reporte : reportes
    User "1" --> "N" Proceso : procesosComoProfesional
    User "1" --> "N" Proceso : procesosComoCoordinador
    User "1" --> "N" Notificacion : notificaciones

    %% Relaciones de Cliente
    Cliente "1" --> "N" Proceso : procesos
    Cliente "1" --> "N" ComprobantePago : comprobantesPago
    Cliente "1" --> "N" Notificacion : notificaciones
    Cliente "1" --> "N" Turno : turnos

    %% Relaciones de Proceso
    Proceso "N" --> "1" Cliente : cliente
    Proceso "N" --> "1" User : profesional
    Proceso "N" --> "1" User : coordinador
    Proceso "N" --> "1" Servicio : servicio
    Proceso "1" --> "N" Turno : turnos
    Proceso "1" --> "N" Documento : documentos
    Proceso "1" --> "N" Reporte : reportes

    %% Relaciones de Servicio
    Servicio "1" --> "N" Proceso : procesos

    %% Relaciones de Turno
    Turno "N" --> "1" Cliente : cliente
    Turno "N" --> "1" User : profesional
    Turno "N" --> "1" Proceso : proceso

    %% Relaciones de Documento
    Documento "N" --> "1" Proceso : proceso

    %% Relaciones de Reporte
    Reporte "N" --> "1" Proceso : proceso
    Reporte "N" --> "1" User : profesional

    %% Relaciones de Notificacion
    Notificacion "N" --> "1" User : user
    Notificacion "N" --> "1" Cliente : cliente

    %% Relaciones de ComprobantePago
    ComprobantePago "N" --> "1" Cliente : cliente
```

## Descripción de Relaciones

### User (Profesional/Coordinador)
- `turnos()` → Turno[] : Un usuario puede tener muchos turnos
- `reportes()` → Reporte[] : Un usuario puede generar muchos reportes
- `procesosComoProfesional()` → Proceso[] : Un usuario puede ser profesional de muchos procesos
- `procesosComoCoordinador()` → Proceso[] : Un usuario puede ser coordinador de muchos procesos
- `notificaciones()` → Notificacion[] : Un usuario puede recibir muchas notificaciones

### Cliente
- `procesos()` → Proceso[] : Un cliente puede tener muchos procesos
- `comprobantesPago()` → ComprobantePago[] : Un cliente puede tener muchos comprobantes
- `notificaciones()` → Notificacion[] : Un cliente puede recibir muchas notificaciones
- `turnos()` → Turno[] : Un cliente puede tener muchos turnos

### Proceso
- `cliente()` → Cliente : Un proceso pertenece a un cliente
- `profesional()` → User : Un proceso tiene un profesional asignado
- `coordinador()` → User : Un proceso tiene un coordinador asignado
- `servicio()` → Servicio : Un proceso ofrece un servicio
- `turnos()` → Turno[] : Un proceso puede tener muchos turnos
- `documentos()` → Documento[] : Un proceso puede tener muchos documentos
- `reportes()` → Reporte[] : Un proceso puede tener muchos reportes

### Servicio
- `procesos()` → Proceso[] : Un servicio puede estar en muchos procesos

### Turno
- `cliente()` → Cliente : Un turno pertenece a un cliente
- `profesional()` → User : Un turno tiene un profesional asignado
- `proceso()` → Proceso : Un turno puede pertenecer a un proceso

### Documento
- `proceso()` → Proceso : Un documento pertenece a un proceso

### Reporte
- `proceso()` → Proceso : Un reporte pertenece a un proceso
- `profesional()` → User : Un reporte es creado por un profesional

### Notificacion
- `user()` → User : Una notificación pertenece a un usuario
- `cliente()` → Cliente : Una notificación puede pertenecer a un cliente

### ComprobantePago
- `cliente()` → Cliente : Un comprobante pertenece a un cliente

## Notas de Implementación

- **SoftDeletes**: Todos los modelos (excepto ComprobantePago) implementan SoftDeletes para eliminación lógica
- **HasFactory**: Todos los modelos tienen factories asociadas para testing
- **Auditable**: User implementa Auditable de OwenIt para auditoría de cambios
- **HasRoles**: User usa Spatie Permission para gestión de roles y permisos
- **Casts**: Los campos de fecha/fecha_hora están casteados correctamente
- **Fillable**: Todos los campos editables están definidos en $fillable
