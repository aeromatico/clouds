<?php namespace Aero\Clouds\Models;

use Model;

/**
 * EmailEvent Model
 *
 * Gestiona los eventos del sistema y su vinculación con plantillas de correo
 */
class EmailEvent extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \Aero\Clouds\Traits\DomainScoped;

    protected $table = 'aero_clouds_email_events';

    protected $fillable = [
        'event_code',
        'event_name',
        'event_category',
        'description',
        'user_template_code',
        'admin_template_code',
        'notify_user',
        'notify_admin',
        'enabled',
        'priority',
        'context_vars',
        'domain',
    ];

    protected $casts = [
        'notify_user' => 'boolean',
        'notify_admin' => 'boolean',
        'enabled' => 'boolean',
        'priority' => 'integer',
    ];

    /**
     * Accessor para context_vars - convierte JSON a string para el formulario
     */
    public function getContextVarsAttribute($value)
    {
        if (is_null($value)) {
            return '';
        }

        // Si ya es un string, intentar decodificar y formatear
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }
            return $value;
        }

        // Si es un array, convertir a JSON formateado
        if (is_array($value)) {
            return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        return '';
    }

    /**
     * Mutator para context_vars - convierte string a JSON para guardar
     */
    public function setContextVarsAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['context_vars'] = null;
            return;
        }

        // Si es un string, intentar decodificar para validar JSON
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->attributes['context_vars'] = json_encode($decoded);
            } else {
                $this->attributes['context_vars'] = $value;
            }
            return;
        }

        // Si es un array, convertir a JSON
        if (is_array($value)) {
            $this->attributes['context_vars'] = json_encode($value);
            return;
        }

        $this->attributes['context_vars'] = $value;
    }

    public $rules = [
        'event_code' => 'required|unique:aero_clouds_email_events',
        'event_name' => 'required|max:255',
        'event_category' => 'required|max:50',
        'user_template_code' => 'nullable|max:255',
        'admin_template_code' => 'nullable|max:255',
        'notify_user' => 'boolean',
        'notify_admin' => 'boolean',
        'enabled' => 'boolean',
        'priority' => 'integer|min:0|max:100',
    ];

    /**
     * Categorías disponibles
     */
    public static function getCategories()
    {
        return [
            'orders' => 'Pedidos',
            'invoices' => 'Facturas',
            'payments' => 'Pagos',
            'clouds' => 'Servidores Cloud',
            'domains' => 'Dominios',
            'support' => 'Soporte',
            'tasks' => 'Tareas',
            'users' => 'Usuarios',
            'general' => 'General',
        ];
    }

    /**
     * Obtener eventos por categoría
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('event_category', $category);
    }

    /**
     * Obtener eventos habilitados
     */
    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    /**
     * Obtener eventos que notifican a usuarios
     */
    public function scopeNotifyingUsers($query)
    {
        return $query->where('notify_user', true);
    }

    /**
     * Obtener eventos que notifican a administradores
     */
    public function scopeNotifyingAdmins($query)
    {
        return $query->where('notify_admin', true);
    }

    /**
     * Verificar si debe enviar email a usuarios
     */
    public function shouldNotifyUser()
    {
        return $this->enabled && $this->notify_user && !empty($this->user_template_code);
    }

    /**
     * Verificar si debe enviar email a administradores
     */
    public function shouldNotifyAdmin()
    {
        return $this->enabled && $this->notify_admin && !empty($this->admin_template_code);
    }

    /**
     * Obtener la configuración global de emails
     */
    public function getAdminEmails()
    {
        $settings = Setting::getCurrentSettings();

        if (!$settings || !$settings->email_notifications_enabled) {
            return [];
        }

        if (empty($settings->admin_emails)) {
            return [];
        }

        // Convertir string de emails separados por comas a array
        $emails = explode(',', $settings->admin_emails);
        return array_map('trim', $emails);
    }

    /**
     * Disparar el evento y enviar emails correspondientes
     *
     * @param array $context Datos contextuales del evento
     * @param \RainLab\User\Models\User|null $user Usuario relacionado
     * @return array Resultado del envío
     */
    public function trigger(array $context = [], $user = null)
    {
        $result = [
            'sent_to_user' => false,
            'sent_to_admins' => false,
            'errors' => [],
        ];

        // Enviar a usuario si corresponde
        if ($this->shouldNotifyUser() && $user && $user->email) {
            try {
                \Mail::send($this->user_template_code, $context, function($message) use ($user) {
                    $message->to($user->email, $user->full_name);
                });

                $result['sent_to_user'] = true;

                // Log the email
                EmailLog::create([
                    'template_code' => $this->user_template_code,
                    'recipient_email' => $user->email,
                    'recipient_name' => $user->full_name,
                    'user_id' => $user->id,
                    'data' => json_encode($context),
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);

            } catch (\Exception $e) {
                $result['errors'][] = 'User email failed: ' . $e->getMessage();
            }
        }

        // Enviar a administradores si corresponde
        if ($this->shouldNotifyAdmin()) {
            $adminEmails = $this->getAdminEmails();

            if (!empty($adminEmails)) {
                foreach ($adminEmails as $email) {
                    try {
                        \Mail::send($this->admin_template_code, $context, function($message) use ($email) {
                            $message->to($email);
                        });

                        // Log the email
                        EmailLog::create([
                            'template_code' => $this->admin_template_code,
                            'recipient_email' => $email,
                            'recipient_name' => 'Administrator',
                            'data' => json_encode($context),
                            'status' => 'sent',
                            'sent_at' => now(),
                        ]);

                    } catch (\Exception $e) {
                        $result['errors'][] = 'Admin email to ' . $email . ' failed: ' . $e->getMessage();
                    }
                }

                $result['sent_to_admins'] = count($adminEmails) > 0;
            }
        }

        return $result;
    }

    /**
     * Helper estático para disparar un evento por código
     *
     * @param string $eventCode Código del evento
     * @param array $context Datos contextuales
     * @param \RainLab\User\Models\User|null $user Usuario relacionado
     * @return array|null
     */
    public static function fire($eventCode, array $context = [], $user = null)
    {
        $event = static::where('event_code', $eventCode)
            ->enabled()
            ->first();

        if (!$event) {
            return null;
        }

        return $event->trigger($context, $user);
    }
}
