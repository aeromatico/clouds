<?php namespace Aero\Manager\Models;

use Model;

/**
 * Model
 */
class Payments extends Model
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
    public $table = 'aero_manager_orders_payments';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
}
