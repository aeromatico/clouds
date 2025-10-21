<?php namespace Aero\Manager\Models;

use Model;

/**
 * Model
 */
class Addons extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'aero_manager_addons';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
    
    
    
    protected $jsonable = ['pricing'];
}
