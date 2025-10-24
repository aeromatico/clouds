<?php namespace Aero\Clouds\Models;

use Model;

/**
 * Domain Model
 * Manages user domains and their configuration
 */
class Domain extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \Aero\Clouds\Traits\LogsActivity;
    use \Aero\Clouds\Traits\DomainScoped;

    /**
     * @var string table name
     */
    public $table = 'aero_clouds_domains';

    /**
     * @var array fillable fields
     */
    protected $fillable = [
        'user_id',
        'order_id',
        'domain_name',
        'extension_id',
        'provider_id',
        'registration_date',
        'expiration_date',
        'auto_renew',
        'status',
        'nameservers',
        'dns_records',
        'is_locked',
        'epp_code',
        'whois_privacy',
        'notes'
    ];

    /**
     * @var array validation rules
     */
    public $rules = [
        'user_id' => 'required|exists:users,id',
        'domain_name' => 'required|max:255',
        'extension_id' => 'required|exists:aero_clouds_domain_extensions,id',
        'provider_id' => 'required|exists:aero_clouds_domain_providers,id',
        'registration_date' => 'required|date',
        'expiration_date' => 'required|date|after:registration_date',
        'status' => 'required|in:pending,active,expired,suspended,cancelled,transferred'
    ];

    /**
     * @var array attributes to cast
     */
    protected $casts = [
        'registration_date' => 'date',
        'expiration_date' => 'date',
        'auto_renew' => 'boolean',
        'is_locked' => 'boolean',
        'whois_privacy' => 'boolean',
        'nameservers' => 'array',
        'dns_records' => 'array'
    ];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'user' => ['RainLab\User\Models\User', 'key' => 'user_id'],
        'order' => ['Aero\Clouds\Models\Order', 'key' => 'order_id'],
        'extension' => ['Aero\Clouds\Models\DomainExtension', 'key' => 'extension_id'],
        'provider' => ['Aero\Clouds\Models\DomainProvider', 'key' => 'provider_id']
    ];

    /**
     * Get full domain name with extension
     */
    public function getFullDomainAttribute()
    {
        if ($this->extension) {
            return $this->domain_name . $this->extension->tld;
        }
        return $this->domain_name;
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
     * Check if domain is expired
     */
    public function getIsExpiredAttribute()
    {
        if (!$this->expiration_date) {
            return false;
        }

        return $this->expiration_date->isPast();
    }

    /**
     * Check if domain is expiring soon (within 30 days)
     */
    public function getIsExpiringSoonAttribute()
    {
        $daysLeft = $this->days_until_expiration;
        return $daysLeft !== null && $daysLeft > 0 && $daysLeft <= 30;
    }

    /**
     * Scope: Active domains
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Expiring soon
     */
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('expiration_date', '>', now())
            ->where('expiration_date', '<=', now()->addDays($days));
    }

    /**
     * Scope: By user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get user options for dropdown
     */
    public function getUserIdOptions()
    {
        return \RainLab\User\Models\User::orderBy('email')
            ->get()
            ->pluck('email', 'id')
            ->toArray();
    }

    /**
     * Get extension options for dropdown
     */
    public function getExtensionIdOptions()
    {
        return DomainExtension::where('is_available', true)
            ->orderBy('tld')
            ->get()
            ->mapWithKeys(function($ext) {
                return [$ext->id => $ext->tld . ' - ' . $ext->name];
            })
            ->toArray();
    }

    /**
     * Get provider options for dropdown
     */
    public function getProviderIdOptions()
    {
        return DomainProvider::where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * Get order options for dropdown
     */
    public function getOrderIdOptions()
    {
        return Order::with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->mapWithKeys(function($order) {
                $label = 'Order #' . $order->id;
                if ($order->user) {
                    $label .= ' - ' . $order->user->email;
                }
                $label .= ' (' . $order->order_date->format('d/m/Y') . ')';
                return [$order->id => $label];
            })
            ->toArray();
    }

    /**
     * Get registrar instance for this domain
     */
    public function getRegistrar()
    {
        if (!$this->provider) {
            throw new \ApplicationException('Domain provider not found');
        }

        $registrarClass = $this->provider->registrar_class;

        if (!class_exists($registrarClass)) {
            throw new \ApplicationException("Registrar class {$registrarClass} not found");
        }

        return new $registrarClass($this->provider);
    }

    /**
     * Update nameservers via API
     */
    public function updateNameservers(array $nameservers)
    {
        $registrar = $this->getRegistrar();
        $result = $registrar->setNameservers($this->full_domain, $nameservers);

        if ($result['success']) {
            $this->nameservers = $nameservers;
            $this->save();
        }

        return $result;
    }

    /**
     * Sync nameservers from API
     */
    public function syncNameservers()
    {
        $registrar = $this->getRegistrar();
        $result = $registrar->getNameservers($this->full_domain);

        if ($result['success'] && isset($result['nameservers'])) {
            $this->nameservers = $result['nameservers'];
            $this->save();
        }

        return $result;
    }

    /**
     * Update DNS records via API
     */
    public function updateDnsRecords(array $records)
    {
        $registrar = $this->getRegistrar();
        $result = $registrar->setDnsRecords($this->full_domain, $records);

        if ($result['success']) {
            $this->dns_records = $records;
            $this->save();
        }

        return $result;
    }

    /**
     * Sync DNS records from API
     */
    public function syncDnsRecords()
    {
        $registrar = $this->getRegistrar();
        $result = $registrar->getDnsRecords($this->full_domain);

        if ($result['success'] && isset($result['records'])) {
            $this->dns_records = $result['records'];
            $this->save();
        }

        return $result;
    }

    /**
     * Lock domain
     */
    public function lock()
    {
        $registrar = $this->getRegistrar();
        $result = $registrar->setLock($this->full_domain, true);

        if ($result['success']) {
            $this->is_locked = true;
            $this->save();
        }

        return $result;
    }

    /**
     * Unlock domain
     */
    public function unlock()
    {
        $registrar = $this->getRegistrar();
        $result = $registrar->setLock($this->full_domain, false);

        if ($result['success']) {
            $this->is_locked = false;
            $this->save();
        }

        return $result;
    }

    /**
     * Get EPP/Auth code
     */
    public function getAuthCode()
    {
        $registrar = $this->getRegistrar();
        $result = $registrar->getAuthCode($this->full_domain);

        if ($result['success'] && isset($result['auth_code'])) {
            $this->epp_code = $result['auth_code'];
            $this->save();
        }

        return $result;
    }

    /**
     * Set order_id attribute - convert empty string to null
     */
    public function setOrderIdAttribute($value)
    {
        $this->attributes['order_id'] = empty($value) ? null : $value;
    }

    /**
     * Set epp_code attribute - convert empty string to null
     */
    public function setEppCodeAttribute($value)
    {
        $this->attributes['epp_code'] = empty($value) ? null : $value;
    }

    /**
     * Set notes attribute - convert empty string to null
     */
    public function setNotesAttribute($value)
    {
        $this->attributes['notes'] = empty($value) ? null : $value;
    }

    /**
     * Set nameservers attribute - filter empty values
     */
    public function setNameserversAttribute($value)
    {
        if (is_array($value)) {
            // Filter out empty nameserver entries
            $value = array_filter($value, function($item) {
                if (is_array($item)) {
                    return !empty($item['nameserver']);
                }
                return !empty($item);
            });

            // Extract just the nameserver values if array of arrays
            if (!empty($value) && is_array(reset($value))) {
                $value = array_column($value, 'nameserver');
            }

            // Re-index array
            $value = array_values($value);
        }

        $this->attributes['nameservers'] = json_encode($value ?: []);
    }

    /**
     * Set dns_records attribute - filter empty values
     */
    public function setDnsRecordsAttribute($value)
    {
        if (is_array($value)) {
            // Filter out completely empty DNS records
            $value = array_filter($value, function($record) {
                return !empty($record['type']) && !empty($record['value']);
            });

            // Re-index array
            $value = array_values($value);
        }

        $this->attributes['dns_records'] = json_encode($value ?: []);
    }

    /**
     * Before create - set defaults
     */
    public function beforeCreate()
    {
        // Set default nameservers if not provided
        if (empty($this->nameservers)) {
            $this->nameservers = [];
        }

        // Set default DNS records if not provided
        if (empty($this->dns_records)) {
            $this->dns_records = [];
        }

        // Default to locked for security
        if ($this->is_locked === null) {
            $this->is_locked = true;
        }
    }
}
