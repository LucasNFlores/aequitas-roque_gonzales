Table users {
  id bigint [pk, increment]
  name varchar
  email varchar [unique]
  email_verified_at timestamp [null]
  password varchar
  remember_token varchar [null]
  // profesional fields (null si no es profesional)
  dni varchar [null, unique]
  telefono varchar [null]
  domicilio varchar [null]
  fecha_nacimiento date [null]
  fecha_ingreso date [null]
  created_at timestamp
  updated_at timestamp
  deleted_at timestamp [null]
}

Table roles {
  id bigint [pk, increment]
  name varchar
  guard_name varchar
}

Table permissions {
  id bigint [pk, increment]
  name varchar
  guard_name varchar
}

// Nota: model_id es una FK polimórfica (Spatie).
// En producción no tiene constraint real en la BD.
// En este sistema model_type siempre es 'App\Models\User',
// por lo que se referencia a users.id solo para visualización.
Table model_has_roles {
  role_id bigint [ref: > roles.id]
  model_type varchar
  model_id bigint
}

Table role_has_permissions {
  role_id bigint [ref: > roles.id]
  permission_id bigint [ref: > permissions.id]
}

Table model_has_permissions {
  permission_id bigint [ref: > permissions.id]
  model_type varchar
  model_id bigint
}

Table clientes {
  id bigint [pk, increment]
  nombre varchar
  apellido varchar
  dni varchar [unique]
  telefono varchar
  correo varchar
  domicilio varchar
  fecha_nacimiento date
  created_at timestamp
  updated_at timestamp
  deleted_at timestamp [null]
}

Table servicios {
  id bigint [pk, increment]
  nombre varchar
  costo_servicio decimal
  created_at timestamp
  updated_at timestamp
  deleted_at timestamp [null]
}

Table user_servicios {
  user_id bigint [ref: > users.id]
  servicio_id bigint [ref: > servicios.id]
}

Table procesos {
  id bigint [pk, increment]
  cliente_id bigint [ref: > clientes.id]
  profesional_id bigint [null, ref: > users.id]
  servicio_id bigint [ref: > servicios.id]
  coordinador_id bigint [ref: > users.id]
  nombre varchar
  descripcion text
  fecha_inicio date
  tipo varchar [note: 'Civil | Comercial | Familia']
  estado varchar [note: 'pendiente | admitido | iniciado | en_proceso | finalizado | en_espera | rechazado']
  motivo_rechazo text [null]
  created_at timestamp
  updated_at timestamp
  deleted_at timestamp [null]
}

Table turnos {
  id bigint [pk, increment]
  cliente_id bigint [ref: > clientes.id]
  profesional_id bigint [ref: > users.id]
  proceso_id bigint [null, ref: > procesos.id]
  fecha_hora datetime
  es_externo boolean [default: false]
  detalle_externo text [null]
  tipo varchar [note: 'consulta_inicial | seguimiento | externo']
  created_at timestamp
  updated_at timestamp
  deleted_at timestamp [null]
}

Table documentos {
  id bigint [pk, increment]
  proceso_id bigint [ref: > procesos.id]
  archivo_path varchar
  tipo_documento varchar
  nombre varchar
  created_at timestamp
  updated_at timestamp
  deleted_at timestamp [null]
}

Table reportes {
  id bigint [pk, increment]
  proceso_id bigint [ref: > procesos.id]
  profesional_id bigint [ref: > users.id]
  contenido text
  fecha date
  created_at timestamp
  updated_at timestamp
  deleted_at timestamp [null]
}

Table comprobante_pagos {
  id bigint [pk, increment]
  cliente_id bigint [ref: > clientes.id]
  archivo_path varchar
  fecha_subida date
  descripcion text [null]
  created_at timestamp
  updated_at timestamp
}

Table notificaciones {
  id bigint [pk, increment]
  user_id bigint [null, ref: > users.id]
  cliente_id bigint [null, ref: > clientes.id]
  canal varchar [note: 'email | whatsapp']
  mensaje text
  fecha_envio timestamp
  estado varchar [note: 'enviado | fallido']
  created_at timestamp
  updated_at timestamp
  deleted_at timestamp [null]
}
