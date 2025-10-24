<?php namespace Aero\Clouds\Models;

use Model;
use Auth;

/**
 * TicketReply Model
 */
class TicketReply extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \Aero\Clouds\Traits\DomainScoped;

    protected $table = 'aero_clouds_ticket_replies';

    protected $fillable = [
        'domain',
        'ticket_id',
        'user_id',
        'admin_id',
        'message',
        'is_internal'
    ];

    protected $casts = [
        'is_internal' => 'boolean'
    ];

    public $rules = [
        'ticket_id' => 'required|exists:aero_clouds_tickets,id',
        'message' => 'required',
        'is_internal' => 'boolean'
    ];

    public $belongsTo = [
        'ticket' => [
            'Aero\Clouds\Models\Ticket',
            'key' => 'ticket_id'
        ],
        'user' => [
            'RainLab\User\Models\User',
            'key' => 'user_id'
        ],
        'admin' => [
            'Backend\Models\User',
            'key' => 'admin_id'
        ]
    ];

    public $attachMany = [
        'attachments' => [
            'System\Models\File'
        ]
    ];

    /**
     * After create, update ticket's last reply
     */
    public function afterCreate()
    {
        $ticket = $this->ticket;

        if ($ticket) {
            $ticket->last_reply_at = now();

            if ($this->admin_id) {
                $ticket->last_reply_by = 'staff';
                // If staff replies, set status to waiting on customer
                if ($ticket->status === 'waiting_on_staff') {
                    $ticket->status = 'waiting_on_customer';
                }
            } else {
                $ticket->last_reply_by = 'customer';
                // If customer replies, set status to waiting on staff
                if ($ticket->status === 'waiting_on_customer') {
                    $ticket->status = 'waiting_on_staff';
                }
            }

            $ticket->save();
        }
    }

    /**
     * Get author name
     */
    public function getAuthorNameAttribute()
    {
        if ($this->admin_id && $this->admin) {
            return $this->admin->full_name;
        } elseif ($this->user_id && $this->user) {
            return $this->user->name;
        }

        return 'Desconocido';
    }

    /**
     * Check if reply is from staff
     */
    public function isFromStaff()
    {
        return !empty($this->admin_id);
    }

    /**
     * Check if reply is from customer
     */
    public function isFromCustomer()
    {
        return !empty($this->user_id);
    }
}
