<?php namespace Aero\Clouds\Models;

use Model;
use Backend\Models\User as BackendUser;
use Aero\Clouds\Models\TaskReply;

class Task extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\SoftDelete;
    use \Aero\Clouds\Traits\DomainScoped;
    use \Aero\Clouds\Traits\LogsActivity;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'aero_clouds_tasks';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'domain',
        'title',
        'description',
        'notes',
        'status',
        'priority',
        'due_date',
        'strict_mode',
        'created_by',
        'completed_at',
        'archived_at',
        'order'
    ];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'title' => 'required|max:255',
        'status' => 'required|in:todo,doing,done',
        'priority' => 'nullable|in:low,medium,high,urgent',
        'domain' => 'nullable',
    ];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [
        'due_date' => 'datetime',
        'strict_mode' => 'boolean',
        'completed_at' => 'datetime',
        'archived_at' => 'datetime',
        'order' => 'integer',
    ];

    /**
     * @var array Soft delete dates
     */
    protected $dates = ['deleted_at', 'due_date', 'completed_at', 'archived_at'];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'creator' => [
            BackendUser::class,
            'key' => 'created_by'
        ]
    ];

    /**
     * @var array Many-to-many relations
     */
    public $belongsToMany = [
        'assigned_users' => [
            BackendUser::class,
            'table' => 'aero_clouds_task_user',
            'key' => 'task_id',
            'otherKey' => 'user_id',
            'pivot' => ['created_at', 'updated_at']
        ]
    ];

    /**
     * @var array Has many relations
     */
    public $hasMany = [
        'replies' => [
            TaskReply::class,
            'key' => 'task_id',
            'order' => 'created_at asc',
            'delete' => true  // Delete replies when task is deleted
        ]
    ];

    /**
     * @var array Attach many relations
     */
    public $attachMany = [
        'attachments' => 'System\Models\File'
    ];

    /**
     * Status options for dropdown
     */
    public function getStatusOptions()
    {
        return [
            'todo' => 'To Do',
            'doing' => 'Doing',
            'done' => 'Done'
        ];
    }

    /**
     * Priority options for dropdown
     */
    public function getPriorityOptions()
    {
        return [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'urgent' => 'Urgent'
        ];
    }

    /**
     * Get assignable users for scope filter
     */
    public function listAssignableUsers()
    {
        return BackendUser::lists('login', 'id');
    }

    /**
     * Scope: Filter by status
     */
    public function scopeTodo($query)
    {
        return $query->where('status', 'todo');
    }

    public function scopeDoing($query)
    {
        return $query->where('status', 'doing');
    }

    public function scopeDone($query)
    {
        return $query->where('status', 'done');
    }

    /**
     * Scope: Filter by priority
     */
    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }

    public function scopeHigh($query)
    {
        return $query->where('priority', 'high');
    }

    /**
     * Scope: Filter by assigned user (many-to-many)
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->whereHas('assigned_users', function($q) use ($userId) {
            $q->where('backend_users.id', $userId);
        });
    }

    /**
     * Scope: Only tasks visible to current user
     */
    public function scopeVisibleToUser($query, $userId = null)
    {
        if (!$userId && $user = \BackendAuth::getUser()) {
            $userId = $user->id;
        }

        if (!$userId) {
            return $query;
        }

        return $query->whereHas('assigned_users', function($q) use ($userId) {
            $q->where('backend_users.id', $userId);
        })->orWhere('created_by', $userId);
    }

    /**
     * Scope: Not archived tasks
     */
    public function scopeNotArchived($query)
    {
        return $query->whereNull('archived_at');
    }

    /**
     * Scope: Archived tasks
     */
    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    /**
     * Scope: Overdue tasks
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereNotIn('status', ['done']);
    }

    /**
     * Scope: Order by custom order field
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc')->orderBy('created_at', 'desc');
    }

    /**
     * Before create event
     */
    public function beforeCreate()
    {
        // Auto-assign creator if not set
        if (!$this->created_by && $user = \BackendAuth::getUser()) {
            $this->created_by = $user->id;
        }

        // Auto-assign order if not set
        if (!$this->order) {
            $maxOrder = static::where('status', $this->status)->max('order') ?? 0;
            $this->order = $maxOrder + 1;
        }
    }

    /**
     * After save event - mark as completed when status changes to done
     */
    public function afterSave()
    {
        if ($this->status === 'done' && !$this->completed_at) {
            $this->completed_at = now();
            $this->saveQuietly();
        } elseif ($this->status !== 'done' && $this->completed_at) {
            $this->completed_at = null;
            $this->saveQuietly();
        }
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute()
    {
        return [
            'todo' => 'secondary',
            'doing' => 'warning',
            'done' => 'success'
        ][$this->status] ?? 'secondary';
    }

    /**
     * Get priority badge color
     */
    public function getPriorityColorAttribute()
    {
        return [
            'low' => 'info',
            'medium' => 'primary',
            'high' => 'warning',
            'urgent' => 'danger'
        ][$this->priority] ?? 'secondary';
    }

    /**
     * Archive this task
     */
    public function archive()
    {
        $this->archived_at = now();
        $this->save();
        return $this;
    }

    /**
     * Unarchive this task
     */
    public function unarchive()
    {
        $this->archived_at = null;
        $this->save();
        return $this;
    }

    /**
     * Check if task is archived
     */
    public function isArchived()
    {
        return !is_null($this->archived_at);
    }

    /**
     * Check if user can view this task
     */
    public function canUserView($userId = null)
    {
        if (!$userId && $user = \BackendAuth::getUser()) {
            $userId = $user->id;
        }

        if (!$userId) {
            return false;
        }

        // Creator can always view
        if ($this->created_by == $userId) {
            return true;
        }

        // Assigned users can view
        return $this->assigned_users()->where('backend_users.id', $userId)->exists();
    }

    /**
     * Check if task is frozen (strict mode + overdue)
     */
    public function isFrozen()
    {
        return $this->strict_mode
            && $this->due_date
            && $this->due_date->isPast()
            && $this->status !== 'done';
    }

    /**
     * Check if user can move this task to done
     */
    public function canUserMoveToCompleted($userId = null)
    {
        if (!$userId && $user = \BackendAuth::getUser()) {
            $userId = $user->id;
        }

        if (!$userId) {
            return false;
        }

        // If task is frozen, only creator can move it
        if ($this->isFrozen()) {
            return $this->created_by == $userId;
        }

        // Otherwise, creator and assigned users can move it
        return $this->canUserView($userId);
    }

    /**
     * Check if user can edit/move this task
     */
    public function canUserEdit($userId = null)
    {
        if (!$userId && $user = \BackendAuth::getUser()) {
            $userId = $user->id;
        }

        if (!$userId) {
            return false;
        }

        // Creator can always edit
        if ($this->created_by == $userId) {
            return true;
        }

        // If task is frozen, only creator can edit
        if ($this->isFrozen()) {
            return false;
        }

        // Otherwise, assigned users can edit
        return $this->assigned_users()->where('backend_users.id', $userId)->exists();
    }
}
