<?php namespace Arnold\School\Models;

use Model;

/**
 * Model
 */
class Courses extends Model
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
    public $table = 'arnold_school_courses';

    /**
     * @var array rules for validation.
     */
    public $rules = [
    ];

  	public $belongsTo = [
        'student' => [
            'Arnold\School\Models\Students',
            'key' => 'student_id'
        ]
    ];
  
  	public $attachOne = [
        'photo' => \System\Models\File::class,
        'listStudents' => \System\Models\File::class
    ];

  	public $attachMany = [
        'galleryClassroom' => \System\Models\File::class
    ];
  
  	protected $jsonable = ['webpage'];
}
