<?php namespace Aero\Clouds\Models;

use Model;

/**
 * DomainProvider Model
 * Manages domain registrar/provider integrations
 */
class DomainProvider extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \Aero\Clouds\Traits\LogsActivity;
    use \Aero\Clouds\Traits\DomainScoped;

    /**
     * @var string table name
     */
    public $table = 'aero_clouds_domain_providers';

    /**
     * @var array fillable fields
     */
    protected $fillable = [
        'domain',
        'name',
        'slug',
        'provider_type',
        'api_url',
        'api_key',
        'api_secret',
        'api_username',
        'api_password',
        'sandbox_mode',
        'is_active',
        'priority',
        'settings',
        'notes'
    ];

    /**
     * @var array validation rules
     */
    public $rules = [
        'name' => 'required|max:255',
        'slug' => 'required|max:255|unique:aero_clouds_domain_providers',
        'provider_type' => 'required|in:dynadot,resellerclub,namecheap,godaddy,cloudflare,nic_bo,custom',
        'api_url' => 'nullable|url|max:255',
        'priority' => 'nullable|integer|min:0|max:100'
    ];

    /**
     * @var array attributes to cast
     */
    protected $casts = [
        'sandbox_mode' => 'boolean',
        'is_active' => 'boolean',
        'priority' => 'integer',
        'settings' => 'array'
    ];

    /**
     * @var array encrypted attributes
     */
    protected $encryptable = [
        'api_key',
        'api_secret',
        'api_password'
    ];

    /**
     * @var array Relations
     */
    public $hasMany = [
        'extensions' => [
            'Aero\Clouds\Models\DomainExtension',
            'key' => 'provider_id'
        ]
    ];

    /**
     * Get provider type options
     */
    public static function getProviderTypeOptions()
    {
        return [
            'dynadot' => 'Dynadot',
            'resellerclub' => 'Reseller Club',
            'namecheap' => 'Namecheap',
            'godaddy' => 'GoDaddy',
            'cloudflare' => 'Cloudflare Registrar',
            'nic_bo' => 'NIC Bolivia (.bo)',
            'custom' => 'Custom/Other'
        ];
    }

    /**
     * Scope: Active providers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->orderBy('priority', 'desc');
    }

    /**
     * Scope: Sandbox providers
     */
    public function scopeSandbox($query)
    {
        return $query->where('sandbox_mode', true);
    }

    /**
     * Scope: Production providers
     */
    public function scopeProduction($query)
    {
        return $query->where('sandbox_mode', false);
    }

    /**
     * Check if provider supports a specific extension
     */
    public function supportsExtension($tld)
    {
        return $this->extensions()
            ->where('tld', $tld)
            ->where('is_available', true)
            ->exists();
    }

    /**
     * Get provider configuration
     */
    public function getConfig()
    {
        return [
            'api_url' => $this->api_url,
            'api_key' => $this->api_key,
            'api_secret' => $this->api_secret,
            'api_username' => $this->api_username,
            'api_password' => $this->api_password,
            'sandbox_mode' => $this->sandbox_mode,
            'settings' => $this->settings
        ];
    }

    /**
     * Before create - generate slug
     */
    public function beforeCreate()
    {
        if (empty($this->slug)) {
            $this->slug = \Str::slug($this->name);
        }
    }

    /**
     * Before validate - ensure unique slug
     */
    public function beforeValidate()
    {
        if (empty($this->slug)) {
            $this->slug = \Str::slug($this->name);
        }
    }
}
