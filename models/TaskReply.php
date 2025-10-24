<?php namespace Aero\Clouds\Models;

use Model;
use Backend\Models\User as BackendUser;

class TaskReply extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'aero_clouds_task_replies';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'task_id',
        'user_id',
        'message'
    ];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'task_id' => 'required|exists:aero_clouds_tasks,id',
        'user_id' => 'required|exists:backend_users,id',
        'message' => 'required'
    ];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'task' => [
            Task::class,
            'key' => 'task_id'
        ],
        'user' => [
            BackendUser::class,
            'key' => 'user_id'
        ]
    ];

    /**
     * Before create - set user_id from current backend user
     */
    public function beforeCreate()
    {
        if (!$this->user_id && $user = \BackendAuth::getUser()) {
            $this->user_id = $user->id;
        }
    }
}
