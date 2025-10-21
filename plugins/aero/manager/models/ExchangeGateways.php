<?php namespace Aero\Manager\Models;

use Model;

/**
 * Model
 */
class ExchangeGateways extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'aero_manager_exchange_gateways';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
    
    public function scopeFrom($query)
    {
        return $query->where('from_on', 1)->where('public_on', 1);
    }    
    
    public function scopeTo($query)
    {
        return $query->where('to_on', 1)->where('public_on', 1); //$_SERVER['HTTP_HOST'] 'boliviahost.com'
    }      
}
