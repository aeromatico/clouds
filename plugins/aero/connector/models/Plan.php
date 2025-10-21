<?php namespace Aero\Connector\Models;

use October\Rain\Database\Model;

class Plan extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\SoftDelete;
    use \October\Rain\Database\Traits\Sortable;

    protected $dates = ['deleted_at'];

    public $table = 'aero_connector_plans';

    public $rules = [
        'name' => 'required|max:255',
        'service_id' => 'required|exists:aero_connector_services,id',
        'price' => 'required|numeric|min:0'
    ];

    protected $fillable = [
        'service_id',
        'name',
        'description',
        'features',
        'price',
        'setup_fee',
        'billing_cycle',
        'is_active',
        'is_popular',
        'sort_order',
        'resource_limits',
        'pricing_options'
    ];

    protected $jsonable = [
        'resource_limits',
        'pricing_options'
    ];

    public $belongsTo = [
        'service' => [
            'Aero\Connector\Models\Service',
            'key' => 'service_id'
        ]
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopePopular($query)
    {
        return $query->where('is_popular', 1);
    }

    public function getServiceNameAttribute()
    {
        return $this->service ? $this->service->name : '';
    }
}