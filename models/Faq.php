<?php namespace Aero\Clouds\Models;

use Model;

class Faq extends Model
{
    use \October\Rain\Database\Traits\Validation;

    protected $table = 'aero_clouds_faqs';

    protected $fillable = [
        'question',
        'answer',
        'links',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'links' => 'json'
    ];

    public $rules = [
        'question' => 'required',
        'answer' => 'required',
        'is_active' => 'boolean',
        'sort_order' => 'integer|min:0'
    ];

    public $belongsToMany = [
        'services' => [
            'Aero\Clouds\Models\Service',
            'table' => 'aero_clouds_faq_service',
            'key' => 'faq_id',
            'otherKey' => 'service_id'
        ]
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
    }

}