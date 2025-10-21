<?php namespace Aero\Manager\Models;

use Model;

/**
 * Model
 */
class StoreItems extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'aero_manager_store_items';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
    
    public $attachMany = [
        'images' => 'System\Models\File',
    ];    
    
    protected $jsonable = ['variants'];
    
    public $belongsToMany =[
        

        'collections' => [
            
            'Aero\Manager\Models\StoreCollections',
            'table'     => 'aero_manager_store_items_collections',
            'name'  => 'name'
        
        ],
  
   
    ];    
}
