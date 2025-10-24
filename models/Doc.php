<?php namespace Aero\Clouds\Models;

use Model;

class Doc extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \Aero\Clouds\Traits\DomainScoped;

    protected $table = 'aero_clouds_docs';

    protected $fillable = [
        'domain',
        'title',
        'slug',
        'content',
        'excerpt',
        'category',
        'tags',
        'is_active',
        'is_featured',
        'sort_order',
        'author'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'tags' => 'array'
    ];

    public $rules = [
        'title' => 'required|max:255',
        'slug' => 'required|unique:aero_clouds_docs,slug|max:255|regex:/^[a-z0-9-]+$/',
        'content' => 'required',
        'excerpt' => 'nullable',
        'category' => 'nullable|max:255',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer|min:0',
        'author' => 'nullable|max:255'
    ];

    public $belongsToMany = [
        'services' => [
            'Aero\Clouds\Models\Service',
            'table' => 'aero_clouds_doc_service',
            'key' => 'doc_id',
            'otherKey' => 'service_id'
        ]
    ];

    public $attachMany = [
        'attachments' => 'System\Models\File'
    ];

    public $jsonable = ['tags'];

    public function beforeValidate()
    {
        // Auto-generate slug from title if not provided
        if (empty($this->slug) && !empty($this->title)) {
            $this->slug = $this->generateSlug($this->title);
        }
    }

    public function generateSlug($title)
    {
        // Convert to lowercase and replace spaces/special chars with hyphens
        $slug = strtolower(trim($title));
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
        return $query->orderBy('sort_order', 'asc')->orderBy('title', 'asc');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}