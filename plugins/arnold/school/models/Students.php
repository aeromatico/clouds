<?php namespace Arnold\School\Models;

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
    public $table = 'arnold_school_students';

    /**
     * @var array rules for validation.
     */
    public $rules = [
    ];
  
    public $belongsTo = [
        'course' => [
            'Arnold\School\Models\Courses',
            'key' => 'course_id'
        ]
    ];

    public $attachOne = [
        'photo' => \System\Models\File::class,
        'cv' => \System\Models\File::class
    ];

    public $attachMany = [
        'gallery' => \System\Models\File::class
    ];

	protected $jsonable = ['social'];
}
