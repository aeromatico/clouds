<?php namespace Aero\Clouds\Models;

use Model;

/**
 * EmailLog Model
 *
 * Registra todos los correos electrónicos enviados
 */
class EmailLog extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \Aero\Clouds\Traits\DomainScoped;

    /**
     * @var string table associated with the model
     */
    public $table = 'aero_clouds_email_logs';

    /**
     * @var array guarded attributes aren't mass assignable
     */
    protected $guarded = ['*'];

    /**
     * @var array fillable attributes are mass assignable
     */
    protected $fillable = [
        'domain',
        'template_code',
        'recipient_email',
        'recipient_name',
        'user_id',
        'data',
        'metadata',
        'status',
        'error',
        'duration_ms',
        'sent_at',
    ];

    /**
     * @var array rules for validation
     */
    public $rules = [
        'template_code' => 'required',
        'recipient_email' => 'required|email',
        'status' => 'required|in:sent,failed,queued',
    ];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [
        'sent_at' => 'datetime',
        'duration_ms' => 'float',
    ];

    /**
     * @var array Attributes to be cast to JSON
     */
    protected $jsonable = ['data', 'metadata'];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'user' => [\Backend\Models\User::class, 'key' => 'user_id'],
    ];

    /**
     * Scope: Solo correos enviados exitosamente
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope: Solo correos fallidos
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope: Por template
     */
    public function scopeTemplate($query, $templateCode)
    {
        return $query->where('template_code', $templateCode);
    }

    /**
     * Scope: Por usuario
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Últimos días
     */
    public function scopeLastDays($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'sent' => '<span class="badge badge-success">Enviado</span>',
            'failed' => '<span class="badge badge-danger">Fallido</span>',
            'queued' => '<span class="badge badge-info">En cola</span>',
        ];

        return $badges[$this->status] ?? $this->status;
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute()
    {
        if (!$this->duration_ms) {
            return '-';
        }

        if ($this->duration_ms < 1000) {
            return number_format($this->duration_ms, 0) . ' ms';
        }

        return number_format($this->duration_ms / 1000, 2) . ' s';
    }

    /**
     * Get template name (friendly)
     */
    public function getTemplateNameAttribute()
    {
        $templates = [
            'aero.clouds::mail.welcome' => 'Bienvenida',
            'aero.clouds::mail.order-confirmation' => 'Confirmación de Orden',
            'aero.clouds::mail.invoice' => 'Factura',
            'aero.clouds::mail.payment-reminder' => 'Recordatorio de Pago',
            'aero.clouds::mail.cloud-activated' => 'Cloud Activado',
            'aero.clouds::mail.cloud-suspended' => 'Cloud Suspendido',
            'aero.clouds::mail.cloud-expiring' => 'Cloud por Expirar',
        ];

        return $templates[$this->template_code] ?? $this->template_code;
    }
}
