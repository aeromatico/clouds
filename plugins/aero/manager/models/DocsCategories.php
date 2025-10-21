<?php namespace Aero\Manager\Models;

use Model;

/**
 * Model
 */
class DocsCategories extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'aero_manager_docs_categories';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
    
    public $belongsTo = [
        'parent' => 'Aero\Manager\Models\DocsCategories',
    ];
    
    public $belongsToMany = [
        'docs' => [
            'Aero\Manager\Models\Docs',
            'table' => 'aero_manager_docs_docs_categories',
            'name' => 'name'
        ],
    ];    
}
