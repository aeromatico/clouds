<?php namespace Aero\Test\Models;

use Model;

/**
 * Model
 */
class Students extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\SoftDelete;

    /**
     * @var array dates to cast from the database.
     */
    protected $dates = ['deleted_at'];

    /**
     * @var string table in the database used by the model.
     */
    public $table = 'aero_test_students';

    /**
     * @var array rules for validation.
     */
    public $rules = [
    ];
	
  	public $belongsTo = [
        'curse' => [
            'Aero\Test\Models\Curses',
            'key' => 'curse_id'
        ]
    ];
}
