<?php namespace Aero\Clouds\Models;

use Model;
use Auth;

/**
 * Ticket Model
 */
class Ticket extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \Aero\Clouds\Traits\LogsActivity;
    use \Aero\Clouds\Traits\DomainScoped;

    protected $table = 'aero_clouds_tickets';

    protected $fillable = [
        'domain',
        'user_id',
        'department_id',
        'assigned_to',
        'ticket_number',
        'subject',
        'message',
        'priority',
        'status',
        'last_reply_at',
        'last_reply_by',
        'closed_at',
        'closed_by'
    ];

    protected $casts = [
        'last_reply_at' => 'datetime',
        'closed_at' => 'datetime'
    ];

    public $rules = [
        'user_id' => 'required|exists:users,id',
        'department_id' => 'required|exists:aero_clouds_support_departments,id',
        'subject' => 'required|max:255',
        'message' => 'required',
        'priority' => 'required|in:low,normal,high,urgent',
        'status' => 'required|in:open,in_progress,waiting_on_customer,waiting_on_staff,closed'
    ];

    public $belongsTo = [
        'user' => [
            'RainLab\User\Models\User',
            'key' => 'user_id'
        ],
        'department' => [
            'Aero\Clouds\Models\SupportDepartment',
            'key' => 'department_id'
        ],
        'assigned_user' => [
            'Backend\Models\User',
            'key' => 'assigned_to'
        ],
        'closed_by_user' => [
            'Backend\Models\User',
            'key' => 'closed_by'
        ]
    ];

    public $hasMany = [
        'replies' => [
            'Aero\Clouds\Models\TicketReply',
            'key' => 'ticket_id',
            'order' => 'created_at asc'
        ]
    ];

    /**
     * Generate unique ticket number before create
     */
    public function beforeCreate()
    {
        if (empty($this->ticket_number)) {
            $this->ticket_number = $this->generateTicketNumber();
        }
    }

    /**
     * Generate unique ticket number
     */
    protected function generateTicketNumber()
    {
        $prefix = 'TKT';
        $date = date('Ymd');

        // Find last ticket number for today
        $lastTicket = static::where('ticket_number', 'LIKE', $prefix . $date . '%')
            ->orderBy('ticket_number', 'desc')
            ->first();

        if ($lastTicket) {
            $lastNumber = (int) substr($lastTicket->ticket_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Scope for open tickets
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_progress', 'waiting_on_customer', 'waiting_on_staff']);
    }

    /**
     * Scope for closed tickets
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    /**
     * Scope for user tickets
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for assigned tickets
     */
    public function scopeAssignedTo($query, $adminId)
    {
        return $query->where('assigned_to', $adminId);
    }

    /**
     * Scope for unassigned tickets
     */
    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    /**
     * Scope for priority
     */
    public function scopePriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Get priority options
     */
    public function getPriorityOptions()
    {
        return [
            'low' => 'Baja',
            'normal' => 'Normal',
            'high' => 'Alta',
            'urgent' => 'Urgente'
        ];
    }

    /**
     * Get status options
     */
    public function getStatusOptions()
    {
        return [
            'open' => 'Abierto',
            'in_progress' => 'En Progreso',
            'waiting_on_customer' => 'Esperando Cliente',
            'waiting_on_staff' => 'Esperando Staff',
            'closed' => 'Cerrado'
        ];
    }

    /**
     * Get assigned to options
     */
    public function getAssignedToOptions()
    {
        return \Backend\Models\User::all()->pluck('full_name', 'id')->toArray();
    }

    /**
     * Get department options
     */
    public function getDepartmentIdOptions()
    {
        return SupportDepartment::active()->ordered()->pluck('name', 'id')->toArray();
    }

    /**
     * Check if ticket is open
     */
    public function isOpen()
    {
        return in_array($this->status, ['open', 'in_progress', 'waiting_on_customer', 'waiting_on_staff']);
    }

    /**
     * Check if ticket is closed
     */
    public function isClosed()
    {
        return $this->status === 'closed';
    }

    /**
     * Close ticket
     */
    public function closeTicket($adminId = null)
    {
        $this->status = 'closed';
        $this->closed_at = now();
        $this->closed_by = $adminId;
        $this->save();
    }

    /**
     * Reopen ticket
     */
    public function reopenTicket()
    {
        $this->status = 'open';
        $this->closed_at = null;
        $this->closed_by = null;
        $this->save();
    }

    /**
     * Get priority badge class
     */
    public function getPriorityBadgeAttribute()
    {
        $badges = [
            'low' => 'badge-success',
            'normal' => 'badge-info',
            'high' => 'badge-warning',
            'urgent' => 'badge-error'
        ];

        return $badges[$this->priority] ?? 'badge-ghost';
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'open' => 'badge-success',
            'in_progress' => 'badge-primary',
            'waiting_on_customer' => 'badge-warning',
            'waiting_on_staff' => 'badge-info',
            'closed' => 'badge-ghost'
        ];

        return $badges[$this->status] ?? 'badge-ghost';
    }

    /**
     * Get replies count
     */
    public function getRepliesCountAttribute()
    {
        return $this->replies()->count();
    }
}
