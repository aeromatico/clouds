<?php namespace Aero\Manager\Models;

use Model;

/**
 * Model
 */
class Contents extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'aero_manager_contents';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
    public $attachMany = [
    'photos' => 'System\Models\File'
    ];
}
