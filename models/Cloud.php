<?php namespace Aero\Clouds\Models;

use Model;

/**
 * Cloud Server Instance Model
 */
class Cloud extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \Aero\Clouds\Traits\LogsActivity;
    use \Aero\Clouds\Traits\DomainScoped;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'aero_clouds_clouds';

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'domain',
        'user_id',
        'service_id',
        'plan_id',
        'order_id',
        'name',
        'panel_url',
        'panel_user',
        'panel_password',
        'ip_address',
        'server_type',
        'status',
        'created_date',
        'expiration_date',
        'last_renewal_date',
        'suspension_date',
        'termination_date',
        'suspension_reason',
        'termination_reason',
        'auto_renew',
        'notes'
    ];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'user_id' => 'required|exists:users,id',
        'name' => 'required|max:255',
        'panel_url' => 'nullable|url|max:255',
        'panel_user' => 'nullable|max:255',
        'ip_address' => 'nullable|ip|max:45',
        'status' => 'required|in:pending,active,suspended,terminated,expired',
        'server_type' => 'nullable|max:50',
        'created_date' => 'required|date',
        'expiration_date' => 'nullable|date|after:created_date'
    ];

    /**
     * @var array Attributes that should be cast
     */
    protected $casts = [
        'created_date' => 'datetime',
        'expiration_date' => 'datetime',
        'last_renewal_date' => 'datetime',
        'suspension_date' => 'datetime',
        'termination_date' => 'datetime',
        'auto_renew' => 'boolean'
    ];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'user' => [
            'RainLab\User\Models\User',
            'key' => 'user_id'
        ],
        'service' => [
            'Aero\Clouds\Models\Service',
            'key' => 'service_id'
        ],
        'plan' => [
            'Aero\Clouds\Models\Plan',
            'key' => 'plan_id'
        ],
        'order' => [
            'Aero\Clouds\Models\Order',
            'key' => 'order_id'
        ]
    ];

    /**
     * @var array Attributes to be encrypted
     */
    protected $encryptable = ['panel_password'];

    /**
     * Get status options
     */
    public static function getStatusOptions()
    {
        return [
            'pending' => 'Pending',
            'active' => 'Active',
            'suspended' => 'Suspended',
            'terminated' => 'Terminated',
            'expired' => 'Expired'
        ];
    }

    /**
     * Get server type options
     */
    public static function getServerTypeOptions()
    {
        return [
            'shared' => 'Shared Hosting',
            'vps' => 'VPS',
            'dedicated' => 'Dedicated Server',
            'cloud' => 'Cloud Server',
            'reseller' => 'Reseller Hosting',
            'email' => 'Email Hosting',
            'other' => 'Other'
        ];
    }

    /**
     * Get user options for dropdown
     */
    public function getUserIdOptions()
    {
        $users = \RainLab\User\Models\User::orderBy('email')->get();
        $options = [];

        foreach ($users as $user) {
            $label = $user->email;
            if ($user->first_name || $user->last_name) {
                $label = trim($user->first_name . ' ' . $user->last_name) . ' (' . $user->email . ')';
            }
            $options[$user->id] = $label;
        }

        return $options;
    }

    /**
     * Get service options for dropdown
     */
    public function getServiceIdOptions()
    {
        return Service::orderBy('name')->lists('name', 'id');
    }

    /**
     * Get plan options for dropdown
     */
    public function getPlanIdOptions()
    {
        return Plan::orderBy('name')->lists('name', 'id');
    }

    /**
     * Get order options for dropdown
     */
    public function getOrderIdOptions()
    {
        return Order::orderBy('id', 'desc')->lists('id', 'id');
    }

    /**
     * Scope: Active servers
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Suspended servers
     */
    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    /**
     * Scope: Expired servers
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
                    ->orWhere(function($q) {
                        $q->where('expiration_date', '<', now())
                          ->whereIn('status', ['active', 'suspended']);
                    });
    }

    /**
     * Scope: Expiring soon (within 30 days)
     */
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('status', 'active')
                    ->where('expiration_date', '>', now())
                    ->where('expiration_date', '<=', now()->addDays($days));
    }

    /**
     * Check if server is expired
     */
    public function getIsExpiredAttribute()
    {
        return $this->expiration_date && $this->expiration_date < now();
    }

    /**
     * Get days until expiration
     */
    public function getDaysUntilExpirationAttribute()
    {
        if (!$this->expiration_date) {
            return null;
        }

        return now()->diffInDays($this->expiration_date, false);
    }

    /**
     * Activate server
     */
    public function activate()
    {
        $this->status = 'active';
        $this->suspension_date = null;
        $this->suspension_reason = null;
        $this->save();

        $this->log('activated', 'Server activated');
    }

    /**
     * Suspend server
     */
    public function suspend($reason = null)
    {
        $this->status = 'suspended';
        $this->suspension_date = now();
        $this->suspension_reason = $reason;
        $this->save();

        $this->log('suspended', 'Server suspended', ['reason' => $reason]);
    }

    /**
     * Reactivate suspended server
     */
    public function reactivate()
    {
        if ($this->status === 'suspended') {
            $this->activate();
            $this->log('reactivated', 'Server reactivated from suspension');
        }
    }

    /**
     * Terminate server
     */
    public function terminate($reason = null)
    {
        $this->status = 'terminated';
        $this->termination_date = now();
        $this->termination_reason = $reason;
        $this->save();

        $this->log('terminated', 'Server terminated', ['reason' => $reason]);
    }

    /**
     * Renew server
     */
    public function renew($months = 1)
    {
        // If no expiration date, set from now
        if (!$this->expiration_date) {
            $this->expiration_date = now()->addMonths($months);
        } else {
            // If already expired, renew from now
            if ($this->expiration_date < now()) {
                $this->expiration_date = now()->addMonths($months);
            } else {
                // Extend from current expiration
                $this->expiration_date = $this->expiration_date->addMonths($months);
            }
        }

        $this->last_renewal_date = now();

        // Reactivate if suspended or expired
        if (in_array($this->status, ['suspended', 'expired'])) {
            $this->status = 'active';
            $this->suspension_date = null;
            $this->suspension_reason = null;
        }

        $this->save();

        $this->log('renewed', 'Server renewed', [
            'months' => $months,
            'new_expiration' => $this->expiration_date->toDateTimeString()
        ]);
    }

    /**
     * Generate panel password if empty
     */
    public function beforeCreate()
    {
        if (empty($this->panel_password)) {
            $this->panel_password = \Str::random(16);
        }

        if (empty($this->created_date)) {
            $this->created_date = now();
        }
    }
}
