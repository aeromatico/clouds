# Guía de Email Events - Sistema de Notificaciones

Este documento explica cómo usar el sistema de eventos de email en el plugin Aero Clouds.

## 📋 Tabla de Contenidos

1. [Descripción General](#descripción-general)
2. [Configuración Inicial](#configuración-inicial)
3. [Gestión de Eventos](#gestión-de-eventos)
4. [Uso en Código](#uso-en-código)
5. [Eventos Disponibles](#eventos-disponibles)
6. [Plantillas de Correo](#plantillas-de-correo)

---

## Descripción General

El sistema de Email Events permite:
- ✅ Vincular eventos del sistema con plantillas de correo
- ✅ Notificar a usuarios finales y/o administradores
- ✅ Configurar qué eventos envían notificaciones
- ✅ Gestionar plantillas de email separadas para usuarios y admins
- ✅ Tracking completo de emails enviados

### Arquitectura

```
Evento del Sistema
    ↓
EmailEvent Model (vincula con plantillas)
    ↓
Plantillas de Correo (user / admin)
    ↓
Destinatarios (usuario específico / lista de admins)
    ↓
EmailLog (registro de envío)
```

---

## Configuración Inicial

### 1. Configuración Global de Email

**Ruta:** `Backend → Clouds Setup → Global Settings → Email`

**Campos:**
- **Habilitar Notificaciones:** Switch maestro que activa/desactiva TODO el sistema
- **Correos de Administradores:** Lista de emails separados por comas que recibirán las notificaciones

```
admin@clouds.com.bo, soporte@clouds.com.bo, gerencia@clouds.com.bo
```

### 2. Gestión de Eventos

**Ruta:** `Backend → Clouds Setup → Email Events`

Aquí puedes configurar cada evento individualmente:
- ✅ Activar/desactivar el evento
- ✅ Vincular con plantilla de usuario
- ✅ Vincular con plantilla de administrador
- ✅ Definir si notifica a usuarios, admins o ambos
- ✅ Establecer prioridad de procesamiento

---

## Gestión de Eventos

### Campos de Configuración

**General:**
- `Evento Activo`: Switch para habilitar/deshabilitar
- `Prioridad`: Número 0-100 (mayor = más importante)
- `Código del Evento`: Identificador único (no editable)
- `Categoría`: orders, invoices, payments, clouds, domains, support, tasks, users
- `Nombre`: Nombre descriptivo del evento
- `Descripción`: Cuándo se dispara el evento

**Plantillas:**
- `Notificar a Usuario`: Switch
- `Código de Plantilla (Usuario)`: ej. `user:new-order`
- `Notificar a Administradores`: Switch
- `Código de Plantilla (Admin)`: ej. `backend:new-order`

**Contexto:**
- `Variables de Contexto`: Array JSON de variables disponibles en las plantillas

---

## Uso en Código

### Método 1: Usar el Helper Estático (Recomendado)

```php
use Aero\Clouds\Models\EmailEvent;

// Disparar un evento con datos contextuales
EmailEvent::fire('order_created', [
    'order_id' => $order->id,
    'order_number' => $order->order_number,
    'user' => $order->user,
    'total' => $order->total_amount,
    'items' => $order->items
], $order->user);
```

### Método 2: Obtener el Evento y Dispararlo Manualmente

```php
use Aero\Clouds\Models\EmailEvent;

// Buscar el evento
$event = EmailEvent::where('event_code', 'invoice_paid')
    ->enabled()
    ->first();

if ($event) {
    // Disparar con contexto
    $result = $event->trigger([
        'invoice_id' => $invoice->id,
        'invoice_number' => $invoice->invoice_number,
        'user' => $invoice->user,
        'amount' => $invoice->total,
        'payment_method' => $payment->method
    ], $invoice->user);

    // $result contiene: ['sent_to_user' => bool, 'sent_to_admins' => bool, 'errors' => []]
}
```

### Método 3: Integración en Modelos

```php
// En tu modelo Order.php
public function afterCreate()
{
    \Aero\Clouds\Models\EmailEvent::fire('order_created', [
        'order_id' => $this->id,
        'order_number' => $this->order_number,
        'user' => $this->user,
        'total' => $this->total_amount,
        'items' => $this->items
    ], $this->user);
}
```

---

## Eventos Disponibles

### Pedidos (orders)
- `order_created` - Nuevo pedido creado
- `order_completed` - Pedido completado
- `order_cancelled` - Pedido cancelado

### Facturas (invoices)
- `invoice_created` - Nueva factura
- `invoice_paid` - Factura pagada
- `invoice_overdue` - Factura vencida

### Pagos (payments)
- `payment_received` - Pago recibido
- `payment_failed` - Pago fallido

### Servidores Cloud (clouds)
- `cloud_created` - Servidor cloud creado
- `cloud_suspended` - Servidor suspendido
- `cloud_expiring` - Próximo a vencer
- `cloud_expired` - Servidor expirado

### Dominios (domains)
- `domain_registered` - Dominio registrado
- `domain_expiring` - Próximo a vencer
- `domain_expired` - Dominio expirado

### Soporte (support)
- `ticket_created` - Nuevo ticket
- `ticket_reply` - Respuesta en ticket

### Tareas (tasks)
- `task_assigned` - Tarea asignada
- `task_completed` - Tarea completada

### Usuarios (users)
- `user_registered` - Nuevo usuario

---

## Plantillas de Correo

### Plantillas Existentes

**Usuario:**
- `user:new-order` - Nuevo pedido
- `user:new-invoice` - Nueva factura
- `user:welcome_email` - Bienvenida

**Administrador:**
- `backend:new-order` - Notificación de pedido
- `backend:contact-form` - Formulario de contacto
- `user:new_user_internal` - Nuevo usuario (para admins)

### Crear Nueva Plantilla

1. Ir a `Settings → Mail Templates`
2. Crear nueva plantilla
3. Asignar código único (ej: `user:cloud-created`)
4. Diseñar el contenido del email
5. Vincular con evento en `Email Events`

### Variables en Plantillas

Las variables se pasan en el array de contexto al disparar el evento:

```twig
Hola {{ user.name }},

Tu pedido #{{ order_number }} ha sido creado exitosamente.

Total: ${{ total }}

Artículos:
{% for item in items %}
- {{ item.name }}: ${{ item.price }}
{% endfor %}

Gracias por tu compra.
```

---

## Ejemplo Completo

### 1. Crear el Evento en Backend

`Clouds Setup → Email Events → Create`

- Código: `cloud_expiring`
- Categoría: `clouds`
- Notificar Usuario: ✅
- Plantilla Usuario: `user:cloud-expiring`
- Notificar Admin: ✅
- Plantilla Admin: `backend:cloud-expiring`

### 2. Crear las Plantillas de Email

**Plantilla Usuario (`user:cloud-expiring`):**
```
Asunto: Tu servidor {{ service.name }} vence en {{ days_left }} días

Hola {{ user.name }},

Tu servidor cloud "{{ service.name }}" vencerá el {{ expiration_date }}.

Para renovarlo, visita: {{ renewal_url }}

Gracias,
Equipo Clouds
```

**Plantilla Admin (`backend:cloud-expiring`):**
```
Asunto: Servidor próximo a vencer - {{ user.email }}

El servidor cloud de {{ user.name }} ({{ user.email }}) vence en {{ days_left }} días.

Detalles:
- Servicio: {{ service.name }}
- Plan: {{ plan.name }}
- Vencimiento: {{ expiration_date }}
```

### 3. Disparar el Evento desde Código

```php
// En un comando programado o tarea
$expiringClouds = Cloud::where('expiration_date', '<=', now()->addDays(7))->get();

foreach ($expiringClouds as $cloud) {
    EmailEvent::fire('cloud_expiring', [
        'cloud_id' => $cloud->id,
        'user' => $cloud->user,
        'service' => $cloud->service,
        'plan' => $cloud->plan,
        'expiration_date' => $cloud->expiration_date->format('d/m/Y'),
        'days_left' => now()->diffInDays($cloud->expiration_date),
        'renewal_url' => url('/account/clouds/' . $cloud->id . '/renew')
    ], $cloud->user);
}
```

---

## Verificación y Debugging

### Ver Eventos Disparados

```php
// En tinker o controlador
$logs = \Aero\Clouds\Models\EmailLog::latest()
    ->where('template_code', 'user:cloud-expiring')
    ->get();

foreach ($logs as $log) {
    echo $log->recipient_email . ' - ' . $log->status . PHP_EOL;
}
```

### Verificar Configuración de Evento

```php
$event = \Aero\Clouds\Models\EmailEvent::where('event_code', 'order_created')->first();
echo 'Habilitado: ' . ($event->enabled ? 'Sí' : 'No') . PHP_EOL;
echo 'Notifica usuario: ' . ($event->notify_user ? 'Sí' : 'No') . PHP_EOL;
echo 'Notifica admin: ' . ($event->notify_admin ? 'Sí' : 'No') . PHP_EOL;
```

---

## Comandos Artisan

### Re-poblar Eventos por Defecto

```bash
php artisan aero:seed-email-events
```

Este comando crea/actualiza los 20 eventos estándar del sistema.

---

## Notas Importantes

⚠️ **IMPORTANTE:**
- El switch `Habilitar Notificaciones` en Global Settings es el interruptor maestro
- Si está desactivado, NO se enviarán emails aunque los eventos estén activos
- Los códigos de plantilla deben coincidir EXACTAMENTE con los códigos en Mail Templates
- Las plantillas se gestionan en `Settings → Mail Templates` (OctoberCMS)

💡 **TIPS:**
- Usa prioridades para eventos críticos (pagos, suspensiones)
- Prueba plantillas antes de producción
- Monitorea EmailLog para verificar entregas
- Mantén las listas de admins actualizadas

---

## Soporte

Para más información o problemas, consulta:
- Documentación de OctoberCMS Mail Templates
- Logs en `EmailLog` model
- Activity Logs del plugin
