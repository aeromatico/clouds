<?php namespace Aero\Manager\Models;

use Model;

/**
 * Model
 */
class Features extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'aero_manager_features';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
    
    public $belongsTo = [
        'service_id' => 'Aero\Manager\Models\Services'
    ];
    
    public $attachOne = [
        'img' => 'System\Models\File'
    ];    
    
    public $attachMany = [
    'galery' => 'System\Models\File',
    ];
     
    public $belongsToMany =[
        
        'service' => [
            
            'Aero\Manager\Models\Services',
            'table'     => 'aero_manager_services_features',
            'name'  => 'name'
        
        ],
   
    ];
}
