<?php
/**
 * EJEMPLOS DE USO DEL SISTEMA DE CORREOS
 * Sistema completo para envío de emails en clouds.com.bo
 *
 * @see plugins/aero/clouds/MAIL_SYSTEM.md para documentación completa
 */

use Aero\Clouds\Classes\Mail\MailService;
use Aero\Clouds\Classes\Mail\MailHelper;
use Aero\Clouds\Models\Order;
use Aero\Clouds\Models\Invoice;

// ============================================================================
// EJEMPLO 1: ENVÍO BÁSICO
// ============================================================================

// Envío simple e inmediato
MailService::send(
    'aero.clouds::mail.welcome',
    'usuario@example.com',
    ['name' => 'Juan Pérez']
);

// ============================================================================
// EJEMPLO 2: ENVÍO EN COLA (ASÍNCRONO)
// ============================================================================

// Enviar en cola para mejor performance
MailService::send(
    'aero.clouds::mail.invoice',
    'cliente@example.com',
    ['invoice' => $invoice],
    ['queue' => true]
);

// Enviar con delay (60 segundos)
MailService::send(
    'aero.clouds::mail.reminder',
    'usuario@example.com',
    ['data' => $data],
    ['queue' => true, 'delay' => 60]
);

// ============================================================================
// EJEMPLO 3: USANDO MÉTODOS PREDEFINIDOS
// ============================================================================

// Correo de bienvenida
MailService::sendWelcomeEmail($user);

// Confirmación de orden
MailService::sendOrderConfirmation($order);

// Factura
MailService::sendInvoice($invoice);

// Recordatorio de pago
MailService::sendPaymentReminder($invoice);

// Cloud activado
MailService::sendCloudActivated($cloud);

// ============================================================================
// EJEMPLO 4: MÚLTIPLES DESTINATARIOS
// ============================================================================

MailService::send(
    'aero.clouds::mail.notification',
    [
        'user1@example.com' => 'Usuario 1',
        'user2@example.com' => 'Usuario 2',
        'user3@example.com'  // Sin nombre
    ],
    ['message' => 'Hola a todos'],
    [
        'cc' => ['manager@example.com'],
        'bcc' => ['admin@example.com']
    ]
);

// ============================================================================
// EJEMPLO 5: CON ARCHIVOS ADJUNTOS
// ============================================================================

MailService::send(
    'aero.clouds::mail.invoice',
    'cliente@example.com',
    ['invoice' => $invoice],
    [
        'attachments' => [
            // Adjunto simple
            '/path/to/invoice.pdf',

            // Adjunto con opciones
            [
                'path' => storage_path('app/invoices/INV-123.pdf'),
                'options' => [
                    'as' => 'Factura-Enero-2025.pdf',
                    'mime' => 'application/pdf'
                ]
            ]
        ]
    ]
);

// ============================================================================
// EJEMPLO 6: USANDO EL HELPER
// ============================================================================

// Envío inmediato
MailHelper::send('template.code', 'email@example.com', $data);

// Envío en cola
MailHelper::queue('template.code', 'email@example.com', $data);

// Envío con delay
MailHelper::later('template.code', 'email@example.com', 60, $data);

// ============================================================================
// EJEMPLO 7: DESDE UN MODELO (TRAIT MAILABLE)
// ============================================================================

// En el modelo Order
class Order extends Model
{
    use \Aero\Clouds\Traits\Mailable;
}

// Uso:
$order = Order::find(1);

// Enviar correo
$order->sendMail('aero.clouds::mail.order-confirmation');

// Enviar en cola
$order->sendMailQueued('aero.clouds::mail.order-confirmation');

// Notificar al usuario propietario
$order->notifyUser('aero.clouds::mail.order-status', [
    'status' => 'completed'
]);

// ============================================================================
// EJEMPLO 8: DESDE UNA PÁGINA CMS
// ============================================================================

/*
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

    Flash::success('Tu mensaje ha sido enviado');
}
?>
==
*/

// ============================================================================
// EJEMPLO 9: REGISTRO DE USUARIO
// ============================================================================

function onRegister()
{
    // Crear usuario
    $user = User::create([
        'email' => post('email'),
        'name' => post('name'),
        'password' => bcrypt(post('password'))
    ]);

    // Enviar correo de bienvenida en cola
    MailService::sendWelcomeEmail($user, [
        'queue' => true,
        'delay' => 5  // Esperar 5 segundos
    ]);

    return ['success' => true];
}

// ============================================================================
// EJEMPLO 10: PROCESO DE CHECKOUT
// ============================================================================

function onCheckout()
{
    // Crear orden
    $order = Order::create($orderData);

    // Enviar confirmación al cliente (en cola)
    MailService::sendOrderConfirmation($order, [
        'queue' => true
    ]);

    // Notificar al equipo de ventas
    MailService::send(
        'aero.clouds::mail.new-order-admin',
        'ventas@clouds.com.bo',
        ['order' => $order],
        ['queue' => true]
    );

    // Crear factura
    $invoice = Invoice::create($invoiceData);

    // Enviar factura con PDF
    MailService::sendInvoice($invoice, [
        'attachments' => [
            [
                'path' => $invoice->generatePDF(),
                'options' => [
                    'as' => "Factura-{$invoice->invoice_number}.pdf"
                ]
            ]
        ]
    ]);

    return ['success' => true];
}

// ============================================================================
// EJEMPLO 11: RECORDATORIOS AUTOMÁTICOS (COMANDO/TAREA)
// ============================================================================

// En un comando de consola o tarea programada
use Aero\Clouds\Models\Cloud;

$expiringSoon = Cloud::where('expiration_date', '<=', now()->addDays(7))
    ->where('status', 'active')
    ->where('auto_renew', false)
    ->get();

foreach ($expiringSoon as $cloud) {
    MailService::sendCloudExpiring($cloud, [
        'queue' => true,
        'delay' => rand(1, 300)  // Distribuir envíos (1-5 min)
    ]);
}

// ============================================================================
// EJEMPLO 12: ESTADÍSTICAS
// ============================================================================

// Obtener estadísticas de los últimos 7 días
$stats = MailHelper::getStats(7);

/*
Array
(
    [total] => 150
    [sent] => 145
    [failed] => 5
    [success_rate] => 96.67
    [avg_duration] => 234.56
    [by_template] => Array
        (
            [aero.clouds::mail.welcome] => 50
            [aero.clouds::mail.invoice] => 45
            [aero.clouds::mail.order-confirmation] => 30
        )
)
*/

// ============================================================================
// EJEMPLO 13: VALIDACIÓN Y SANITIZACIÓN
// ============================================================================

$email = post('email');

// Validar
if (!MailHelper::isValidEmail($email)) {
    return ['error' => 'Email inválido'];
}

// Sanitizar
$cleanEmail = MailHelper::sanitizeEmail($email);

// Enviar
MailService::send('template.code', $cleanEmail, $data);

// ============================================================================
// EJEMPLO 14: CONSULTAR LOGS
// ============================================================================

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

// Por usuario
$userEmails = EmailLog::forUser($userId)
    ->sent()
    ->get();

// ============================================================================
// EJEMPLO 15: OPCIONES AVANZADAS
// ============================================================================

MailService::send(
    'aero.clouds::mail.custom',
    'cliente@example.com',
    [
        'name' => 'Cliente VIP',
        'data' => $customData
    ],
    [
        // Cola y delay
        'queue' => true,
        'delay' => 30,

        // Destinatarios adicionales
        'cc' => ['manager@example.com'],
        'bcc' => ['admin@example.com'],

        // Reply-to personalizado
        'reply_to' => ['soporte@clouds.com.bo', 'Soporte Técnico'],

        // Adjuntos
        'attachments' => [
            '/path/to/file.pdf',
            [
                'path' => '/path/to/image.jpg',
                'options' => ['as' => 'preview.jpg']
            ]
        ],

        // Logging y metadata
        'log' => true,
        'user_id' => $user->id,
        'metadata' => [
            'campaign_id' => 123,
            'source' => 'checkout',
            'tags' => ['vip', 'premium'],
            'custom_field' => 'custom_value'
        ]
    ]
);

// ============================================================================
// FIN DE EJEMPLOS
// ============================================================================

/*
 * NOTAS IMPORTANTES:
 *
 * 1. Configuración SMTP: Configura las variables en .env
 * 2. Queue Worker: Ejecuta `php artisan queue:work` para procesar colas
 * 3. Plantillas: Crea plantillas en Settings → Mail → Mail Templates
 * 4. Logs: Revisa logs en backend: Cloud Commerce → Email Logs
 * 5. Desarrollo: Usa MAIL_MAILER=log para testing
 *
 * Documentación completa: plugins/aero/clouds/MAIL_SYSTEM.md
 */
