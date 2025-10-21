<?php namespace Aero\Manager\Models;

use Model;

/**
 * Model
 */
class StoreCollections extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'aero_manager_store_collections';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
    
    public $belongsToMany =[
        

        'items' => [
            
            'Aero\Manager\Models\StoreItems',
            'table' => 'aero_manager_store_items_collections',
            'name'  => 'name'
        
        ],
  
   
    ];   
}
