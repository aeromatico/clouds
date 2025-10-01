<?php namespace Aero\Clouds\Models;

use Model;

class Service extends Model
{
    use \October\Rain\Database\Traits\Validation;

    protected $table = 'aero_clouds_services';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'menu_description',
        'html_description',
        'icon',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public $rules = [
        'name' => 'required|max:255',
        'slug' => 'required|unique:aero_clouds_services,slug|max:255|regex:/^[a-z0-9-]+$/',
        'short_description' => 'nullable|max:255',
        'menu_description' => 'nullable',
        'html_description' => 'nullable',
        'description' => 'nullable',
        'icon' => 'nullable|max:255',
        'is_active' => 'boolean',
        'sort_order' => 'integer|min:0'
    ];

    public $belongsToMany = [
        'plans' => [
            'Aero\Clouds\Models\Plan',
            'table' => 'aero_clouds_plan_service',
            'key' => 'service_id',
            'otherKey' => 'plan_id'
        ],
        'features' => [
            'Aero\Clouds\Models\Feature',
            'table' => 'aero_clouds_feature_service',
            'key' => 'service_id',
            'otherKey' => 'feature_id'
        ],
        'addons' => [
            'Aero\Clouds\Models\Addon',
            'table' => 'aero_clouds_addon_service',
            'key' => 'service_id',
            'otherKey' => 'addon_id'
        ],
        'faqs' => [
            'Aero\Clouds\Models\Faq',
            'table' => 'aero_clouds_faq_service',
            'key' => 'service_id',
            'otherKey' => 'faq_id'
        ],
        'docs' => [
            'Aero\Clouds\Models\Doc',
            'table' => 'aero_clouds_doc_service',
            'key' => 'service_id',
            'otherKey' => 'doc_id'
        ]
    ];

    public $attachOne = [
        'img' => 'System\Models\File'
    ];

    public $attachMany = [
        'gallery' => 'System\Models\File'
    ];

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

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
    }
}