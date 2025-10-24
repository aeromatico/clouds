# Sistema de Correos Electrónicos - Clouds.com.bo

Sistema completo y versátil para el envío de correos electrónicos en OctoberCMS, con soporte para colas, logging, tracking y múltiples métodos de uso.

## 🎯 Características

- ✅ Envío inmediato o en cola (Redis)
- ✅ Soporte completo para plantillas de OctoberCMS
- ✅ Logging automático de todos los correos
- ✅ Tracking de estado (enviado, fallido, en cola)
- ✅ Reintentos automáticos en caso de fallo
- ✅ Panel de administración backend
- ✅ Estadísticas de envío
- ✅ Múltiples destinatarios (to, cc, bcc)
- ✅ Archivos adjuntos
- ✅ Variables dinámicas
- ✅ Reply-to personalizado
- ✅ Helpers y traits para uso fácil

## 📦 Estructura

```
plugins/aero/clouds/
├── classes/mail/
│   ├── MailService.php      # Servicio centralizado
│   └── MailHelper.php        # Funciones helper
├── jobs/
│   └── SendMailJob.php       # Job para colas
├── models/
│   ├── EmailLog.php          # Modelo de logs
│   └── emaillog/             # Configuración backend
├── controllers/
│   └── EmailLogs.php         # Controlador backend
└── traits/
    └── Mailable.php          # Trait para modelos
```

## 🚀 Uso Rápido

### 1. Envío Básico

```php
use Aero\Clouds\Classes\Mail\MailService;

// Envío simple
MailService::send(
    'aero.clouds::mail.welcome',  // Código de plantilla
    'usuario@example.com',         // Destinatario
    ['name' => 'Juan']             // Variables
);
```

### 2. Envío en Cola

```php
// Enviar en cola (asíncrono)
MailService::send(
    'aero.clouds::mail.invoice',
    'cliente@example.com',
    ['invoice' => $invoice],
    ['queue' => true]  // ⭐ Activar cola
);

// Con delay (enviar después de 60 segundos)
MailService::send(
    'aero.clouds::mail.reminder',
    'usuario@example.com',
    ['data' => $data],
    ['queue' => true, 'delay' => 60]
);
```

### 3. Usando el Helper

```php
use Aero\Clouds\Classes\Mail\MailHelper;

// Envío inmediato
MailHelper::send('template.code', 'email@example.com', $data);

// Envío en cola
MailHelper::queue('template.code', 'email@example.com', $data);

// Envío con delay
MailHelper::later('template.code', 'email@example.com', 60, $data);
```

## 📧 Métodos Predefinidos

### Correo de Bienvenida

```php
MailService::sendWelcomeEmail($user);

// Con opciones
MailService::sendWelcomeEmail($user, [
    'queue' => true,
    'delay' => 10
]);
```

### Confirmación de Orden

```php
MailService::sendOrderConfirmation($order);
```

### Factura

```php
MailService::sendInvoice($invoice);
```

### Recordatorio de Pago

```php
MailService::sendPaymentReminder($invoice);
```

### Cloud Activado

```php
MailService::sendCloudActivated($cloud);
```

### Cloud Suspendido

```php
MailService::sendCloudSuspended($cloud);
```

### Cloud por Expirar

```php
MailService::sendCloudExpiring($cloud);
```

## 🎨 Uso en Modelos (Trait Mailable)

Agrega el trait a cualquier modelo:

```php
use Aero\Clouds\Traits\Mailable;

class Order extends Model
{
    use Mailable;

    // ...
}
```

Ahora puedes enviar correos directamente desde el modelo:

```php
// Envío simple
$order->sendMail('aero.clouds::mail.order-confirmation');

// Envío en cola
$order->sendMailQueued('aero.clouds::mail.order-confirmation');

// Notificar al usuario propietario
$order->notifyUser('aero.clouds::mail.order-status', [
    'status' => 'completed'
]);

// Notificar al usuario en cola
$order->notifyUserQueued('aero.clouds::mail.order-status', [
    'status' => 'completed'
]);
```

## 📝 Uso en Páginas CMS

```php
==
<?php
use Aero\Clouds\Classes\Mail\MailService;

function onContactForm()
{
    $data = post();

    MailService::send(
        'aero.clouds::mail.contact',
        'admin@clouds.com.bo',
        [
            'name' => $data['name'],
            'email' => $data['email'],
            'message' => $data['message']
        ],
        ['queue' => true]
    );

    Flash::success('Mensaje enviado');
}
?>
==
```

## 🔧 Opciones Avanzadas

### Múltiples Destinatarios

```php
MailService::send(
    'template.code',
    [
        'user1@example.com' => 'Usuario 1',
        'user2@example.com' => 'Usuario 2'
    ],
    $data,
    [
        'cc' => ['supervisor@example.com'],
        'bcc' => ['admin@example.com']
    ]
);
```

### Archivos Adjuntos

```php
MailService::send(
    'template.code',
    'user@example.com',
    $data,
    [
        'attachments' => [
            '/path/to/file.pdf',
            [
                'path' => '/path/to/invoice.pdf',
                'options' => [
                    'as' => 'Invoice.pdf',
                    'mime' => 'application/pdf'
                ]
            ]
        ]
    ]
);
```

### Reply-To Personalizado

```php
MailService::send(
    'template.code',
    'user@example.com',
    $data,
    [
        'reply_to' => ['support@clouds.com.bo', 'Soporte Clouds']
    ]
);
```

### Metadata Personalizada

```php
MailService::send(
    'template.code',
    'user@example.com',
    $data,
    [
        'user_id' => $user->id,
        'metadata' => [
            'campaign_id' => 123,
            'source' => 'checkout',
            'tags' => ['vip', 'premium']
        ]
    ]
);
```

## 📊 Panel de Administración

El sistema incluye un panel completo en el backend:

**Ubicación:** Cloud Commerce → Email Logs

**Funcionalidades:**
- 📋 Lista de todos los correos enviados
- 🔍 Búsqueda por email, template o estado
- 📈 Estadísticas de envío
- 🔄 Reenviar correos fallidos
- 🗑️ Limpiar logs antiguos
- 👁️ Ver detalles completos (datos, metadata, errores)

### Ver Estadísticas

```php
// Estadísticas de los últimos 7 días
$stats = MailHelper::getStats(7);

// Resultado:
[
    'total' => 150,
    'sent' => 145,
    'failed' => 5,
    'success_rate' => 96.67,
    'avg_duration' => 234.56,  // ms
    'by_template' => [
        'aero.clouds::mail.welcome' => 50,
        'aero.clouds::mail.invoice' => 45,
        // ...
    ]
]
```

## 🎯 Crear Plantillas de Correo

### 1. En OctoberCMS Backend

1. Ve a: **Settings → Mail → Mail Templates**
2. Crea nueva plantilla
3. **Code:** `aero.clouds::mail.custom-template`
4. Configura asunto, contenido HTML y texto plano

### 2. Variables Disponibles

Todas las variables pasadas en `$data` están disponibles:

```twig
Hola {{ name }},

Tu orden #{{ order.id }} ha sido confirmada.

Total: ${{ order.total_amount }}

Gracias por tu compra!
```

## ⚙️ Configuración

### Variables de Entorno (.env)

```bash
# Mailer
MAIL_MAILER=smtp              # log, smtp, mailgun, etc.
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@clouds.com.bo
MAIL_FROM_NAME="Clouds Hosting"

# Colas (ya configurado)
QUEUE_CONNECTION=redis
REDIS_QUEUE_DB=3
```

### Iniciar Worker de Colas

Para procesar correos en cola:

```bash
# Producción
php artisan queue:work redis --queue=default --tries=3 --timeout=90

# Con supervisor (recomendado)
# Ver: https://laravel.com/docs/queues#supervisor-configuration
```

## 🔍 Debugging

### Ver Correos en Log (Development)

Si `MAIL_MAILER=log`, los correos se guardan en:

```
storage/logs/october.log
```

### Ver Cola de Redis

```bash
redis-cli -n 3
KEYS *
LLEN queues:default
```

### Verificar Logs en Base de Datos

```php
use Aero\Clouds\Models\EmailLog;

// Últimos correos enviados
$recent = EmailLog::sent()
    ->orderByDesc('created_at')
    ->limit(10)
    ->get();

// Correos fallidos
$failed = EmailLog::failed()
    ->orderByDesc('created_at')
    ->get();

// Por template
$welcomeEmails = EmailLog::template('aero.clouds::mail.welcome')
    ->lastDays(7)
    ->get();
```

## 🎨 Ejemplos Completos

### 1. Sistema de Registro

```php
// En el controlador de registro
public function onRegister()
{
    $user = $this->createUser(post());

    // Enviar email de bienvenida en cola
    MailService::sendWelcomeEmail($user, [
        'queue' => true,
        'delay' => 5  // Esperar 5 segundos
    ]);

    Flash::success('Registro exitoso. Revisa tu email.');
}
```

### 2. Sistema de Órdenes

```php
// Al crear una orden
$order = Order::create($data);

// Confirmar al cliente
$order->notifyUser('aero.clouds::mail.order-confirmation', [
    'items' => $order->items,
    'total' => $order->total
]);

// Notificar al admin
MailService::send(
    'aero.clouds::mail.new-order-admin',
    'admin@clouds.com.bo',
    ['order' => $order],
    ['queue' => true]
);
```

### 3. Sistema de Facturas

```php
// Al generar factura
$invoice = Invoice::create($data);

// Enviar factura con PDF adjunto
MailService::send(
    'aero.clouds::mail.invoice',
    [$invoice->user->email => $invoice->user->name],
    ['invoice' => $invoice],
    [
        'attachments' => [
            [
                'path' => $invoice->generatePDF(),
                'options' => [
                    'as' => "Factura-{$invoice->invoice_number}.pdf",
                    'mime' => 'application/pdf'
                ]
            ]
        ]
    ]
);
```

### 4. Recordatorios Programados

```php
// En un comando de consola o tarea programada
$expiringSoon = Cloud::where('expiration_date', '<=', now()->addDays(7))
    ->where('status', 'active')
    ->get();

foreach ($expiringSoon as $cloud) {
    MailService::sendCloudExpiring($cloud, [
        'queue' => true,
        'delay' => rand(1, 60)  // Distribuir envíos
    ]);
}
```

## 🔒 Seguridad y Mejores Prácticas

1. **Nunca enviar datos sensibles:** Limpia las variables antes de enviar
2. **Validar emails:** Usa `MailHelper::isValidEmail($email)`
3. **Rate limiting:** Usa colas para evitar spam
4. **Logging:** Mantén logs por al menos 30 días
5. **Testing:** Usa `MAIL_MAILER=log` en desarrollo
6. **Monitoreo:** Revisa correos fallidos regularmente

## 📚 API Reference

### MailService

```php
// Método principal
MailService::send($templateCode, $to, $data, $options)

// Métodos específicos
MailService::sendWelcomeEmail($user, $options)
MailService::sendOrderConfirmation($order, $options)
MailService::sendInvoice($invoice, $options)
MailService::sendPaymentReminder($invoice, $options)
MailService::sendCloudActivated($cloud, $options)
MailService::sendCloudSuspended($cloud, $options)
MailService::sendCloudExpiring($cloud, $options)
MailService::sendCustom($templateCode, $to, $data, $options)
```

### MailHelper

```php
MailHelper::send($templateCode, $to, $data, $options)
MailHelper::queue($templateCode, $to, $data, $options)
MailHelper::later($templateCode, $to, $delay, $data, $options)
MailHelper::getStats($days)
MailHelper::isValidEmail($email)
MailHelper::sanitizeEmail($email)
MailHelper::formatRecipient($email, $name)
```

### Trait Mailable

```php
$model->sendMail($templateCode, $to, $data, $options)
$model->sendMailQueued($templateCode, $to, $data, $options)
$model->notifyUser($templateCode, $data, $options)
$model->notifyUserQueued($templateCode, $data, $options)
```

## 🎉 Listo para Usar

El sistema está completamente configurado y listo para usar. Solo necesitas:

1. ✅ Crear plantillas de correo en OctoberCMS
2. ✅ Configurar SMTP en `.env`
3. ✅ Iniciar queue worker (opcional, para colas)
4. ✅ Empezar a enviar correos!

---

**Documentación actualizada:** 2025-01-06
**Versión del sistema:** 1.9.0
