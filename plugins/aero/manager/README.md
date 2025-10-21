# API Universal para OctoberCMS - Plugin Aero.Manager

## Descripción
Este plugin provee un endpoint universal `/api` para realizar operaciones CRUD y tareas sobre modelos del plugin Aero.Manager.

## Uso
- Endpoint: `/api`
- Métodos HTTP: GET y POST
- Parámetros principales:
    - model: nombre del modelo (ej: client, user, product)
    - action: acción CRUD (list, get, create, update, delete)
    - id: id del registro (requerido para get, update, delete)
    - task: tareas especiales (ej: sendEmail)
    - data: datos para create/update y para tareas
- Soporta archivos adjuntos en POST (multipart/form-data)

## Ejemplo
Crear cliente:
POST /api
model=client
action=create
data[name]=Sacha
data[email]=sacha@host.com

Enviar email:
POST /api
model=client
task=sendEmail
id=1
data[email]=cliente@correo.com
data[subject]=Hola
data[body]=Contenido del correo.

## Seguridad
Por ahora sin autenticación. Se recomienda proteger con CORS y HTTPS.

