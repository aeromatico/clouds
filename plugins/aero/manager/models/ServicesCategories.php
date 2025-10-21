<?php namespace Aero\Manager\Models;

use Model;

/**
 * Model
 */
class ServicesCategories extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];

    public function beforeCreate(){
        $this->domain = str_replace('wwww.','',$_SERVER['HTTP_HOST']);
    }  

    /**
     * @var string The database table used by the model.
     */
    public $table = 'aero_manager_services_categories';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
    
    public $belongsToMany = [
        'services' => [
            'Aero\Manager\Models\Services',
            'table' => 'aero_manager_services_services_categories',
            'name' => 'name',
            'scope' => 'domain'
        ],
    ]; 
    
}
