<?php namespace Aero\Manager\Models;

use Model;

/**
 * Model
 */
class Faqs extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];
    
    protected $fillable = ['question', 'answer', 'public'];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'aero_manager_faqs';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
    
    protected $jsonable = ['buttons'];
    
    public $belongsToMany =[
        
        'service' => [
            
            'Aero\Manager\Models\Services',
            'table'     => 'aero_manager_services_faqs',
            'name'  => 'name'
        
        ],
   
    ];
    
}
