<?php namespace Aero\Clouds\Classes\Mail;

/**
 * MailHelper - Funciones helper para envío de correos
 *
 * Uso global:
 * use Aero\Clouds\Classes\Mail\MailHelper;
 * MailHelper::send(...);
 */
class MailHelper
{
    /**
     * Enviar correo (wrapper del MailService)
     */
    public static function send($templateCode, $to, array $data = [], array $options = [])
    {
        return MailService::send($templateCode, $to, $data, $options);
    }

    /**
     * Enviar correo en cola
     */
    public static function queue($templateCode, $to, array $data = [], array $options = [])
    {
        $options['queue'] = true;
        return MailService::send($templateCode, $to, $data, $options);
    }

    /**
     * Enviar correo con delay
     */
    public static function later($templateCode, $to, $delay, array $data = [], array $options = [])
    {
        $options['queue'] = true;
        $options['delay'] = $delay;
        return MailService::send($templateCode, $to, $data, $options);
    }

    /**
     * Obtener estadísticas de correos enviados
     */
    public static function getStats($days = 7)
    {
        $logs = \Aero\Clouds\Models\EmailLog::lastDays($days)->get();

        return [
            'total' => $logs->count(),
            'sent' => $logs->where('status', 'sent')->count(),
            'failed' => $logs->where('status', 'failed')->count(),
            'success_rate' => $logs->count() > 0
                ? round(($logs->where('status', 'sent')->count() / $logs->count()) * 100, 2)
                : 0,
            'avg_duration' => round($logs->where('status', 'sent')->avg('duration_ms'), 2),
            'by_template' => $logs->groupBy('template_code')->map(function($items) {
                return $items->count();
            })->toArray(),
        ];
    }

    /**
     * Validar email
     */
    public static function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Sanitizar email
     */
    public static function sanitizeEmail($email)
    {
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }

    /**
     * Formatear destinatario
     */
    public static function formatRecipient($email, $name = null)
    {
        if ($name) {
            return [$email => $name];
        }
        return $email;
    }

    /**
     * Crear adjunto desde archivo
     */
    public static function attachment($path, array $options = [])
    {
        return [
            'path' => $path,
            'options' => $options,
        ];
    }

    /**
     * Crear adjunto desde datos
     */
    public static function attachmentData($data, $name, $mime = 'application/octet-stream')
    {
        return [
            'data' => $data,
            'name' => $name,
            'mime' => $mime,
        ];
    }
}
