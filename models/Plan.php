<?php namespace Aero\Clouds\Models;

use Model;

class Plan extends Model
{
    use \October\Rain\Database\Traits\Validation;

    protected $table = 'aero_clouds_plans';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'is_featured',
        'promo',
        'free_domain',
        'ssh',
        'ssl',
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
        'ssl' => 'boolean',
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
        'ssl' => 'boolean',
        'dedicated_ip' => 'boolean',
        'sort_order' => 'integer|min:0',
        'pricing' => 'nullable|array',
        'features' => 'nullable|array',
        'limits' => 'nullable|array'
    ];

    public $jsonable = ['pricing', 'features', 'limits'];

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
        return $query->where('ssl', true);
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