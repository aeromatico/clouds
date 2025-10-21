<?php namespace Aero\Manager\Models;

use Model;

/**
 * Model
 */
class Docs extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'aero_manager_docs';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
    
    protected $jsonable = ['links'];


    public $belongsToMany = [
        'categories' => [
            'Aero\Manager\Models\DocsCategories',
            'table' => 'aero_manager_docs_docs_categories',
            'name' => 'name'
        ],
        'service' => [
            
            'Aero\Manager\Models\Services',
            'table'     => 'aero_manager_services_docs',
            'name'  => 'name'
        
        ],
        
    ];
    


}
