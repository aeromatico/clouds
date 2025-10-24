<?php namespace Aero\Clouds\Jobs;

use Aero\Clouds\Classes\Mail\MailService;
use Log;

/**
 * SendMailJob - Job para envío de correos en cola
 */
class SendMailJob
{
    protected $templateCode;
    protected $recipients;
    protected $data;
    protected $options;

    /**
     * Constructor
     */
    public function __construct($templateCode, array $recipients, array $data = [], array $options = [])
    {
        $this->templateCode = $templateCode;
        $this->recipients = $recipients;
        $this->data = $data;
        $this->options = $options;
    }

    /**
     * Ejecutar el job
     */
    public function fire($job, $data)
    {
        try {
            // Enviar el correo
            $result = MailService::sendNow(
                $this->templateCode,
                $this->recipients,
                $this->data,
                $this->options
            );

            if ($result['success']) {
                // Job completado exitosamente
                $job->delete();
            } else {
                // Error al enviar, reintentar
                if ($job->attempts() < 3) {
                    $job->release(60); // Reintentar en 60 segundos
                } else {
                    // Máximo de intentos alcanzado
                    Log::error('SendMailJob: Máximo de intentos alcanzado', [
                        'template' => $this->templateCode,
                        'recipients' => $this->recipients,
                        'error' => $result['error'] ?? 'Unknown error',
                    ]);
                    $job->delete();
                }
            }

        } catch (\Exception $e) {
            Log::error('SendMailJob: Error al ejecutar job', [
                'template' => $this->templateCode,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($job->attempts() < 3) {
                $job->release(60);
            } else {
                $job->delete();
            }
        }
    }
}
