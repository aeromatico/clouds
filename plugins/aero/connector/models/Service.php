<?php namespace Aero\Connector\Models;

use October\Rain\Database\Model;

class Service extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\SoftDelete;
    use \October\Rain\Database\Traits\Sortable;

    protected $dates = ['deleted_at'];

    public $table = 'aero_connector_services';

    public $rules = [
        'name' => 'required|max:255',
        'slug' => 'required|unique:aero_connector_services,slug|max:255'
    ];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'is_active',
        'is_public',
        'sort_order',
        'category',
        'type'
    ];

    public $hasMany = [
        'plans' => [
            'Aero\Connector\Models\Plan',
            'key' => 'service_id'
        ]
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', 1);
    }

    public function beforeCreate()
    {
        if (!$this->slug) {
            $this->slug = \Str::slug($this->name);
        }
    }

    public function beforeUpdate()
    {
        if (!$this->slug) {
            $this->slug = \Str::slug($this->name);
        }
    }
}