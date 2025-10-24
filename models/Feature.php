<?php namespace Aero\Clouds\Models;

use Model;

class Feature extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \Aero\Clouds\Traits\LogsActivity;
    use \Aero\Clouds\Traits\DomainScoped;

    protected $table = 'aero_clouds_features';

    protected $fillable = [
        'domain',
        'name',
        'slug',
        'description',
        'icon',
        'is_active',
        'is_highlighted',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_highlighted' => 'boolean'
    ];

    public $rules = [
        'name' => 'required|max:255',
        'slug' => 'required|unique:aero_clouds_features,slug|max:255|regex:/^[a-z0-9-]+$/',
        'description' => 'nullable',
        'icon' => 'nullable|max:255',
        'is_active' => 'boolean',
        'is_highlighted' => 'boolean',
        'sort_order' => 'integer|min:0'
    ];

    public $belongsToMany = [
        'services' => [
            'Aero\Clouds\Models\Service',
            'table' => 'aero_clouds_feature_service',
            'key' => 'feature_id',
            'otherKey' => 'service_id'
        ]
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

    public function scopeHighlighted($query)
    {
        return $query->where('is_highlighted', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
    }
}