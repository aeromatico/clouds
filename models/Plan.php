<?php namespace Aero\Clouds\Models;

use Model;

class Plan extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \Aero\Clouds\Traits\LogsActivity;
    use \Aero\Clouds\Traits\DomainScoped;

    protected $table = 'aero_clouds_plans';

    protected $fillable = [
        'domain',
        'name',
        'slug',
        'description',
        'is_active',
        'is_featured',
        'promo',
        'free_domain',
        'ssh',
        'ssl_enabled',
        'dedicated_ip',
        'sort_order',
        'pricing',
        'features',
        'limits'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'promo' => 'boolean',
        'free_domain' => 'boolean',
        'ssh' => 'boolean',
        'ssl_enabled' => 'boolean',
        'dedicated_ip' => 'boolean',
        'pricing' => 'array',
        'features' => 'array',
        'limits' => 'array'
    ];

    public $rules = [
        'name' => 'required|max:255',
        'slug' => 'required|unique:aero_clouds_plans,slug|max:255|regex:/^[a-z0-9-]+$/',
        'description' => 'nullable',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'promo' => 'boolean',
        'free_domain' => 'boolean',
        'ssh' => 'boolean',
        'ssl_enabled' => 'boolean',
        'dedicated_ip' => 'boolean',
        'sort_order' => 'integer|min:0',
        'pricing' => 'nullable|array',
        'features' => 'nullable|array',
        'limits' => 'nullable|array'
    ];

    public $jsonable = ['pricing', 'features', 'limits'];

    public $belongsToMany = [
        'services' => [
            'Aero\Clouds\Models\Service',
            'table' => 'aero_clouds_plan_service',
            'key' => 'plan_id',
            'otherKey' => 'service_id'
        ]
    ];

    /**
     * Get the first/primary service for this plan
     */
    public function getServiceAttribute()
    {
        return $this->services()->first();
    }

    /**
     * Get price attribute (default monthly price, or lowest available)
     */
    public function getPriceAttribute()
    {
        // Try to get monthly price first
        $monthlyPricing = $this->getPricingForCycle('monthly');
        if ($monthlyPricing) {
            return $monthlyPricing['price'];
        }

        // If no monthly price, return the lowest available price
        $lowestPrice = $this->getLowestPriceAttribute();
        return $lowestPrice ? $lowestPrice['price'] : 0;
    }

    /**
     * Get the billing cycle for the displayed price
     */
    public function getPriceCycleAttribute()
    {
        // Try monthly first
        $monthlyPricing = $this->getPricingForCycle('monthly');
        if ($monthlyPricing) {
            return 'monthly';
        }

        // Return the cycle of the lowest price
        $lowestPrice = $this->getLowestPriceAttribute();
        return $lowestPrice ? $lowestPrice['billing_cycle'] : 'monthly';
    }

    /**
     * Get quarterly price
     */
    public function getQuarterlyPriceAttribute()
    {
        $pricing = $this->getPricingForCycle('quarterly');
        return $pricing ? $pricing['price'] : null;
    }

    /**
     * Get semi-annually price
     */
    public function getSemiAnnuallyPriceAttribute()
    {
        $pricing = $this->getPricingForCycle('semi_annually');
        return $pricing ? $pricing['price'] : null;
    }

    /**
     * Get annually price
     */
    public function getAnnuallyPriceAttribute()
    {
        $pricing = $this->getPricingForCycle('annually');
        return $pricing ? $pricing['price'] : null;
    }

    /**
     * Get biennially price
     */
    public function getBienniallyPriceAttribute()
    {
        $pricing = $this->getPricingForCycle('biennially');
        return $pricing ? $pricing['price'] : null;
    }

    /**
     * Get features list
     */
    public function getFeaturesListAttribute()
    {
        if (!$this->features || !is_array($this->features)) {
            return [];
        }

        $list = [];
        foreach ($this->features as $feature) {
            if (!empty($feature['name'])) {
                $list[] = $feature['name'];
            } elseif (!empty($feature['description'])) {
                $list[] = $feature['description'];
            }
        }

        return $list;
    }

    /**
     * Get formatted limits with icons
     */
    public function getFormattedLimitsAttribute()
    {
        if (!$this->limits || !is_array($this->limits)) {
            return [];
        }

        $icons = [
            'domains' => 'fa-globe',
            'storage' => 'fa-hard-drive',
            'email_accounts' => 'fa-envelope',
            'subdomain' => 'fa-sitemap',
            'databases' => 'fa-database',
            'memory' => 'fa-memory',
            'cpu' => 'fa-microchip',
            'bandwidth' => 'fa-arrows-alt-h',
            'ftp_accounts' => 'fa-user',
            'ssl' => 'fa-lock',
            'backup' => 'fa-cloud-upload-alt'
        ];

        $labels = [
            'domains' => 'Dominios',
            'storage' => 'Almacenamiento',
            'email_accounts' => 'Cuentas Email',
            'subdomain' => 'Subdominios',
            'databases' => 'Bases de Datos',
            'memory' => 'Memoria RAM',
            'cpu' => 'CPU',
            'bandwidth' => 'Ancho de Banda',
            'ftp_accounts' => 'Cuentas FTP',
            'ssl' => 'Certificado SSL',
            'backup' => 'Backups'
        ];

        $formatted = [];
        foreach ($this->limits as $limit) {
            if (isset($limit['type']) && isset($limit['value'])) {
                $type = $limit['type'];
                $formatted[] = [
                    'type' => $type,
                    'value' => $limit['value'],
                    'icon' => $icons[$type] ?? 'fa-check',
                    'label' => $labels[$type] ?? ucfirst(str_replace('_', ' ', $type))
                ];
            }
        }

        return $formatted;
    }

    public function beforeValidate()
    {
        // Auto-generate slug from name if not provided
        if (empty($this->slug) && !empty($this->name)) {
            $this->slug = $this->generateSlug($this->name);
        }
    }

    public function generateSlug($name)
    {
        // Convert to lowercase and replace spaces/special chars with hyphens
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');

        // Ensure uniqueness
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->where('id', '!=', $this->id)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
    }

    public function scopePromo($query)
    {
        return $query->where('promo', true);
    }

    public function scopeWithFreeDomain($query)
    {
        return $query->where('free_domain', true);
    }

    public function scopeWithSSH($query)
    {
        return $query->where('ssh', true);
    }

    public function scopeWithSSL($query)
    {
        return $query->where('ssl_enabled', true);
    }

    public function scopeWithDedicatedIP($query)
    {
        return $query->where('dedicated_ip', true);
    }

    public function getBillingCycleOptions()
    {
        return [
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly (3 months)',
            'semi_annually' => 'Semi-annually (6 months)',
            'annually' => 'Annually (12 months)',
            'biennially' => 'Biennially (24 months)',
            'triennially' => 'Triennially (36 months)'
        ];
    }

    /**
     * Get billing cycle label in Spanish
     */
    public function getBillingCycleLabel($cycle)
    {
        $labels = [
            'monthly' => 'Mensual',
            'quarterly' => 'Trimestral',
            'semi_annually' => 'Semestral',
            'annually' => 'Anual',
            'biennially' => 'Bienal',
            'triennially' => 'Trienal'
        ];

        return $labels[$cycle] ?? $cycle;
    }

    /**
     * Get available pricing options for this plan
     */
    public function getAvailablePricingAttribute()
    {
        if (!$this->pricing || !is_array($this->pricing)) {
            return [];
        }

        $available = [];
        foreach ($this->pricing as $priceOption) {
            if (isset($priceOption['billing_cycle']) && isset($priceOption['price'])) {
                $available[] = [
                    'cycle' => $priceOption['billing_cycle'],
                    'price' => $priceOption['price'],
                    'currency' => $priceOption['currency'] ?? 'USD',
                    'setup_fee' => $priceOption['setup_fee'] ?? 0,
                    'label' => $this->getBillingCycleLabel($priceOption['billing_cycle'])
                ];
            }
        }

        return $available;
    }

    public function getLowestPriceAttribute()
    {
        if (!$this->pricing || !is_array($this->pricing) || empty($this->pricing)) {
            return null;
        }

        $lowestPrice = null;
        foreach ($this->pricing as $priceOption) {
            if (isset($priceOption['price']) && ($lowestPrice === null || $priceOption['price'] < $lowestPrice['price'])) {
                $lowestPrice = $priceOption;
            }
        }

        return $lowestPrice;
    }

    public function getFormattedLowestPriceAttribute()
    {
        $lowestPrice = $this->getLowestPriceAttribute();
        if (!$lowestPrice) {
            return 'N/A';
        }

        return $lowestPrice['currency'] . ' ' . number_format($lowestPrice['price'], 2) . ' / ' . $lowestPrice['billing_cycle'];
    }

    public function getPricingForCycle($cycle)
    {
        if (!$this->pricing || !is_array($this->pricing)) {
            return null;
        }

        foreach ($this->pricing as $priceOption) {
            if (isset($priceOption['billing_cycle']) && $priceOption['billing_cycle'] === $cycle) {
                return $priceOption;
            }
        }

        return null;
    }

}