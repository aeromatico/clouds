<?php namespace Aero\Clouds\Models;

use Model;

/**
 * DomainExtension Model
 * Manages domain TLDs and their pricing
 */
class DomainExtension extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \Aero\Clouds\Traits\LogsActivity;
    use \Aero\Clouds\Traits\DomainScoped;

    /**
     * @var string table name
     */
    public $table = 'aero_clouds_domain_extensions';

    /**
     * @var array fillable fields
     */
    protected $fillable = [
        'domain',
        'provider_id',
        'tld',
        'name',
        'category',
        'registration_price',
        'renewal_price',
        'transfer_price',
        'redemption_price',
        'currency',
        'sale_price',
        'min_years',
        'max_years',
        'is_available',
        'is_featured',
        'requires_extra_attributes',
        'extra_attributes',
        'whois_privacy_available',
        'whois_privacy_price',
        'notes'
    ];

    /**
     * @var array validation rules
     */
    public $rules = [
        'provider_id' => 'required|exists:aero_clouds_domain_providers,id',
        'tld' => 'required|max:50',
        'name' => 'required|max:255',
        'category' => 'nullable|in:generic,country,sponsored,geographic,brand,new',
        'registration_price' => 'required|numeric|min:0',
        'renewal_price' => 'required|numeric|min:0',
        'transfer_price' => 'nullable|numeric|min:0',
        'redemption_price' => 'nullable|numeric|min:0',
        'min_years' => 'required|integer|min:1|max:10',
        'max_years' => 'required|integer|min:1|max:10'
    ];

    /**
     * @var array attributes to cast
     */
    protected $casts = [
        'registration_price' => 'decimal:2',
        'renewal_price' => 'decimal:2',
        'transfer_price' => 'decimal:2',
        'redemption_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'min_years' => 'integer',
        'max_years' => 'integer',
        'is_available' => 'boolean',
        'is_featured' => 'boolean',
        'requires_extra_attributes' => 'boolean',
        'extra_attributes' => 'array',
        'whois_privacy_available' => 'boolean',
        'whois_privacy_price' => 'decimal:2'
    ];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'provider' => [
            'Aero\Clouds\Models\DomainProvider',
            'key' => 'provider_id'
        ]
    ];

    /**
     * Get category options
     */
    public static function getCategoryOptions()
    {
        return [
            'generic' => 'Generic (gTLD) - .com, .net, .org',
            'country' => 'Country Code (ccTLD) - .bo, .us, .uk',
            'sponsored' => 'Sponsored - .gov, .edu, .mil',
            'geographic' => 'Geographic - .nyc, .london',
            'brand' => 'Brand - .google, .amazon',
            'new' => 'New gTLD - .app, .dev, .io'
        ];
    }

    /**
     * Get provider options for dropdown
     */
    public function getProviderIdOptions()
    {
        return DomainProvider::where('is_active', true)
            ->orderBy('name')
            ->lists('name', 'id');
    }

    /**
     * Scope: Available extensions
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope: Featured extensions
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)
            ->where('is_available', true);
    }

    /**
     * Scope: By category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: Cheap first
     */
    public function scopeCheapFirst($query)
    {
        return $query->orderBy('registration_price', 'asc');
    }

    /**
     * Get display name with TLD
     */
    public function getDisplayNameAttribute()
    {
        return $this->name . ' (' . $this->tld . ')';
    }

    /**
     * Get formatted registration price
     */
    public function getFormattedRegistrationPriceAttribute()
    {
        $symbol = $this->getCurrencySymbol();
        return $symbol . number_format($this->registration_price, 2);
    }

    /**
     * Get formatted renewal price
     */
    public function getFormattedRenewalPriceAttribute()
    {
        $symbol = $this->getCurrencySymbol();
        return $symbol . number_format($this->renewal_price, 2);
    }

    /**
     * Get currency symbol based on currency code
     */
    protected function getCurrencySymbol()
    {
        $symbols = [
            'USD' => '$',
            'BOB' => 'Bs ',
            'EUR' => '€',
            'GBP' => '£',
        ];

        return $symbols[$this->currency ?? 'USD'] ?? $this->currency . ' ';
    }

    /**
     * Check if TLD is a ccTLD (country code)
     */
    public function getIsCountryCodeAttribute()
    {
        return $this->category === 'country';
    }

    /**
     * Before create - ensure TLD format
     */
    public function beforeCreate()
    {
        // Ensure TLD starts with a dot
        if (!empty($this->tld) && substr($this->tld, 0, 1) !== '.') {
            $this->tld = '.' . $this->tld;
        }
    }

    /**
     * Before validate - format TLD
     */
    public function beforeValidate()
    {
        // Ensure TLD starts with a dot
        if (!empty($this->tld) && substr($this->tld, 0, 1) !== '.') {
            $this->tld = '.' . $this->tld;
        }

        // Convert TLD to lowercase
        if (!empty($this->tld)) {
            $this->tld = strtolower($this->tld);
        }
    }
}
