<?php namespace Aero\Clouds\Models;

use Model;

/**
 * SupportDepartment Model
 */
class SupportDepartment extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \Aero\Clouds\Traits\LogsActivity;
    use \Aero\Clouds\Traits\DomainScoped;

    protected $table = 'aero_clouds_support_departments';

    protected $fillable = [
        'domain',
        'name',
        'slug',
        'description',
        'email',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public $rules = [
        'name' => 'required|max:255',
        'slug' => 'required|unique:aero_clouds_support_departments,slug|max:255|regex:/^[a-z0-9-]+$/',
        'email' => 'nullable|email|max:255',
        'is_active' => 'boolean',
        'sort_order' => 'integer|min:0'
    ];

    public $hasMany = [
        'tickets' => [
            'Aero\Clouds\Models\Ticket',
            'key' => 'department_id'
        ]
    ];

    /**
     * Scope to get only active departments
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
    }

    /**
     * Auto-generate slug before validation
     */
    public function beforeValidate()
    {
        if (empty($this->slug) && !empty($this->name)) {
            $this->slug = $this->generateSlug($this->name);
        }
    }

    /**
     * Generate unique slug from name
     */
    public function generateSlug($name)
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');

        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->where('id', '!=', $this->id)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get open tickets count
     */
    public function getOpenTicketsCountAttribute()
    {
        return $this->tickets()
            ->whereIn('status', ['open', 'in_progress', 'waiting_on_customer'])
            ->count();
    }
}
