<?php namespace Aero\Clouds\Traits;

use Aero\Clouds\Classes\Mail\MailService;

/**
 * Mailable Trait
 *
 * Agrega funcionalidad de envío de correos a los modelos
 *
 * Uso en un modelo:
 * use \Aero\Clouds\Traits\Mailable;
 */
trait Mailable
{
    /**
     * Enviar correo usando el servicio centralizado
     *
     * @param string $templateCode
     * @param string|array $to
     * @param array $data
     * @param array $options
     * @return bool|array
     */
    public function sendMail($templateCode, $to = null, array $data = [], array $options = [])
    {
        // Si no se especifica destinatario, usar el email del modelo
        if (is_null($to) && isset($this->email)) {
            $to = [$this->email => $this->name ?? null];
        }

        // Agregar el modelo a los datos
        $data = array_merge(['model' => $this], $data);

        // Si el modelo tiene user_id, agregarlo a las opciones
        if (isset($this->user_id) && !isset($options['user_id'])) {
            $options['user_id'] = $this->user_id;
        }

        return MailService::send($templateCode, $to, $data, $options);
    }

    /**
     * Enviar correo en cola
     */
    public function sendMailQueued($templateCode, $to = null, array $data = [], array $options = [])
    {
        $options['queue'] = true;
        return $this->sendMail($templateCode, $to, $data, $options);
    }

    /**
     * Notificar al usuario propietario
     */
    public function notifyUser($templateCode, array $data = [], array $options = [])
    {
        if (!isset($this->user) || !$this->user) {
            return false;
        }

        return $this->sendMail(
            $templateCode,
            [$this->user->email => $this->user->name],
            array_merge(['user' => $this->user], $data),
            array_merge(['user_id' => $this->user->id], $options)
        );
    }

    /**
     * Notificar al usuario en cola
     */
    public function notifyUserQueued($templateCode, array $data = [], array $options = [])
    {
        $options['queue'] = true;
        return $this->notifyUser($templateCode, $data, $options);
    }
}
