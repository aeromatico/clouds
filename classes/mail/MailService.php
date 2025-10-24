<?php namespace Aero\Clouds\Classes\Mail;

use Mail;
use Event;
use Queue;
use Aero\Clouds\Jobs\SendMailJob;
use Aero\Clouds\Models\EmailLog;
use Exception;
use Log;

/**
 * MailService - Servicio centralizado para envío de correos electrónicos
 *
 * Características:
 * - Envío inmediato o en cola
 * - Soporte para plantillas de OctoberCMS
 * - Logging de correos enviados
 * - Eventos para tracking
 * - Variables dinámicas
 * - Archivos adjuntos
 */
class MailService
{
    /**
     * Enviar correo usando plantilla de OctoberCMS
     *
     * @param string $templateCode Código de la plantilla (ej: 'aero.clouds::mail.welcome')
     * @param string|array $to Destinatario(s)
     * @param array $data Variables para la plantilla
     * @param array $options Opciones adicionales
     * @return bool|array
     */
    public static function send($templateCode, $to, array $data = [], array $options = [])
    {
        $defaultOptions = [
            'queue' => false,           // Enviar en cola
            'delay' => 0,               // Retraso en segundos (si queue=true)
            'cc' => [],                 // Copia
            'bcc' => [],                // Copia oculta
            'attachments' => [],        // Archivos adjuntos
            'reply_to' => null,         // Reply-to
            'log' => true,              // Guardar en log
            'user_id' => null,          // ID del usuario relacionado
            'metadata' => [],           // Metadata adicional
        ];

        $options = array_merge($defaultOptions, $options);

        // Validar destinatario
        if (empty($to)) {
            Log::error('MailService: Destinatario vacío', ['template' => $templateCode]);
            return false;
        }

        // Normalizar destinatarios
        $recipients = self::normalizeRecipients($to);

        // Si es en cola, despachar job
        if ($options['queue']) {
            return self::sendQueued($templateCode, $recipients, $data, $options);
        }

        // Envío inmediato
        return self::sendNow($templateCode, $recipients, $data, $options);
    }

    /**
     * Enviar correo inmediatamente
     */
    public static function sendNow($templateCode, array $recipients, array $data = [], array $options = [])
    {
        try {
            $startTime = microtime(true);
            $success = false;
            $error = null;

            // Disparar evento antes de enviar
            Event::fire('aero.clouds.mail.beforeSend', [$templateCode, $recipients, $data]);

            // Enviar correo usando OctoberCMS Mail
            Mail::send($templateCode, $data, function ($message) use ($recipients, $options) {
                // Destinatarios
                foreach ($recipients as $email => $name) {
                    if (is_numeric($email)) {
                        $message->to($name);
                    } else {
                        $message->to($email, $name);
                    }
                }

                // CC
                if (!empty($options['cc'])) {
                    foreach (self::normalizeRecipients($options['cc']) as $email => $name) {
                        $message->cc($email, $name);
                    }
                }

                // BCC
                if (!empty($options['bcc'])) {
                    foreach (self::normalizeRecipients($options['bcc']) as $email => $name) {
                        $message->bcc($email, $name);
                    }
                }

                // Reply-To
                if (!empty($options['reply_to'])) {
                    if (is_array($options['reply_to'])) {
                        $message->replyTo($options['reply_to'][0], $options['reply_to'][1] ?? null);
                    } else {
                        $message->replyTo($options['reply_to']);
                    }
                }

                // Adjuntos
                if (!empty($options['attachments'])) {
                    foreach ($options['attachments'] as $attachment) {
                        if (is_string($attachment)) {
                            $message->attach($attachment);
                        } elseif (is_array($attachment)) {
                            $message->attach(
                                $attachment['path'],
                                $attachment['options'] ?? []
                            );
                        }
                    }
                }
            });

            $success = true;
            $duration = round((microtime(true) - $startTime) * 1000, 2); // ms

            // Disparar evento después de enviar
            Event::fire('aero.clouds.mail.afterSend', [$templateCode, $recipients, $data, $success]);

            // Log del correo
            if ($options['log']) {
                self::logEmail($templateCode, $recipients, $data, $options, $success, $error, $duration);
            }

            return [
                'success' => true,
                'duration' => $duration,
                'recipients' => count($recipients),
            ];

        } catch (Exception $e) {
            $error = $e->getMessage();
            Log::error('MailService: Error al enviar correo', [
                'template' => $templateCode,
                'recipients' => $recipients,
                'error' => $error,
                'trace' => $e->getTraceAsString(),
            ]);

            // Disparar evento de error
            Event::fire('aero.clouds.mail.sendFailed', [$templateCode, $recipients, $error]);

            // Log del error
            if ($options['log']) {
                self::logEmail($templateCode, $recipients, $data, $options, false, $error);
            }

            return [
                'success' => false,
                'error' => $error,
            ];
        }
    }

    /**
     * Enviar correo en cola
     */
    public static function sendQueued($templateCode, array $recipients, array $data = [], array $options = [])
    {
        try {
            $job = new SendMailJob($templateCode, $recipients, $data, $options);

            if ($options['delay'] > 0) {
                Queue::later($options['delay'], $job);
            } else {
                Queue::push($job);
            }

            // Disparar evento
            Event::fire('aero.clouds.mail.queued', [$templateCode, $recipients, $data]);

            return [
                'success' => true,
                'queued' => true,
                'recipients' => count($recipients),
            ];

        } catch (Exception $e) {
            Log::error('MailService: Error al encolar correo', [
                'template' => $templateCode,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Normalizar destinatarios a formato [email => name]
     */
    protected static function normalizeRecipients($recipients)
    {
        if (is_string($recipients)) {
            return [$recipients => null];
        }

        if (is_array($recipients)) {
            $normalized = [];
            foreach ($recipients as $key => $value) {
                if (is_numeric($key)) {
                    // ['email@example.com']
                    $normalized[$value] = null;
                } else {
                    // ['email@example.com' => 'Name']
                    $normalized[$key] = $value;
                }
            }
            return $normalized;
        }

        return [];
    }

    /**
     * Registrar correo en log
     */
    protected static function logEmail($templateCode, array $recipients, array $data, array $options, $success, $error = null, $duration = null)
    {
        try {
            foreach ($recipients as $email => $name) {
                EmailLog::create([
                    'template_code' => $templateCode,
                    'recipient_email' => is_numeric($email) ? $name : $email,
                    'recipient_name' => is_numeric($email) ? null : $name,
                    'user_id' => $options['user_id'] ?? null,
                    'data' => json_encode($data),
                    'metadata' => json_encode($options['metadata'] ?? []),
                    'status' => $success ? 'sent' : 'failed',
                    'error' => $error,
                    'duration_ms' => $duration,
                    'sent_at' => $success ? now() : null,
                ]);
            }
        } catch (Exception $e) {
            Log::error('MailService: Error al guardar log de correo', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Enviar correo de bienvenida
     */
    public static function sendWelcomeEmail($user, array $options = [])
    {
        return self::send(
            'aero.clouds::mail.welcome',
            [$user->email => $user->name],
            [
                'user' => $user,
                'name' => $user->name,
                'email' => $user->email,
            ],
            array_merge(['user_id' => $user->id], $options)
        );
    }

    /**
     * Enviar correo de confirmación de orden
     */
    public static function sendOrderConfirmation($order, array $options = [])
    {
        return self::send(
            'aero.clouds::mail.order-confirmation',
            [$order->user->email => $order->user->name],
            [
                'order' => $order,
                'user' => $order->user,
                'items' => json_decode($order->items, true),
                'total' => $order->total_amount,
            ],
            array_merge(['user_id' => $order->user_id], $options)
        );
    }

    /**
     * Enviar correo de factura
     */
    public static function sendInvoice($invoice, array $options = [])
    {
        return self::send(
            'aero.clouds::mail.invoice',
            [$invoice->user->email => $invoice->user->name],
            [
                'invoice' => $invoice,
                'user' => $invoice->user,
                'items' => json_decode($invoice->items, true),
                'total' => $invoice->total_amount,
            ],
            array_merge(
                ['user_id' => $invoice->user_id],
                $options
            )
        );
    }

    /**
     * Enviar correo de recordatorio de pago
     */
    public static function sendPaymentReminder($invoice, array $options = [])
    {
        return self::send(
            'aero.clouds::mail.payment-reminder',
            [$invoice->user->email => $invoice->user->name],
            [
                'invoice' => $invoice,
                'user' => $invoice->user,
                'days_overdue' => now()->diffInDays($invoice->due_date),
                'total' => $invoice->total_amount,
            ],
            array_merge(['user_id' => $invoice->user_id], $options)
        );
    }

    /**
     * Enviar correo de cloud activado
     */
    public static function sendCloudActivated($cloud, array $options = [])
    {
        return self::send(
            'aero.clouds::mail.cloud-activated',
            [$cloud->user->email => $cloud->user->name],
            [
                'cloud' => $cloud,
                'user' => $cloud->user,
                'panel_url' => $cloud->panel_url,
                'panel_username' => $cloud->panel_username,
            ],
            array_merge(['user_id' => $cloud->user_id], $options)
        );
    }

    /**
     * Enviar correo de cloud suspendido
     */
    public static function sendCloudSuspended($cloud, array $options = [])
    {
        return self::send(
            'aero.clouds::mail.cloud-suspended',
            [$cloud->user->email => $cloud->user->name],
            [
                'cloud' => $cloud,
                'user' => $cloud->user,
                'suspension_reason' => $cloud->suspension_reason,
            ],
            array_merge(['user_id' => $cloud->user_id], $options)
        );
    }

    /**
     * Enviar correo de cloud por expirar
     */
    public static function sendCloudExpiring($cloud, array $options = [])
    {
        return self::send(
            'aero.clouds::mail.cloud-expiring',
            [$cloud->user->email => $cloud->user->name],
            [
                'cloud' => $cloud,
                'user' => $cloud->user,
                'days_until_expiration' => $cloud->days_until_expiration,
                'expiration_date' => $cloud->expiration_date,
            ],
            array_merge(['user_id' => $cloud->user_id], $options)
        );
    }

    /**
     * Enviar correo personalizado
     */
    public static function sendCustom($templateCode, $to, array $data = [], array $options = [])
    {
        return self::send($templateCode, $to, $data, $options);
    }
}
