<?php namespace Aero\Clouds\Components;

use Cms\Classes\ComponentBase;
use Aero\Clouds\Models\Ticket;
use Aero\Clouds\Models\TicketReply;
use Aero\Clouds\Models\SupportDepartment;
use Auth;
use Redirect;
use Flash;
use ValidationException;

/**
 * Tickets Component
 */
class Tickets extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Tickets Component',
            'description' => 'Manage support tickets for customers'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    /**
     * Get user tickets
     */
    public function onRun()
    {
        $user = Auth::getUser();

        if (!$user) {
            return Redirect::to('/login');
        }

        $this->page['user'] = $user;
        $this->page['departments'] = SupportDepartment::active()->ordered()->get();
    }

    /**
     * Get tickets for listing
     */
    public function getUserTickets($status = null)
    {
        $user = Auth::getUser();

        if (!$user) {
            return collect([]);
        }

        $query = Ticket::forUser($user->id)
            ->with(['department', 'replies'])
            ->orderBy('created_at', 'desc');

        if ($status === 'open') {
            $query->open();
        } elseif ($status === 'closed') {
            $query->closed();
        }

        return $query->get();
    }

    /**
     * Get single ticket
     */
    public function getTicket($ticketNumber)
    {
        $user = Auth::getUser();

        if (!$user) {
            return null;
        }

        return Ticket::where('ticket_number', $ticketNumber)
            ->forUser($user->id)
            ->with(['department', 'replies.user', 'replies.admin'])
            ->first();
    }

    /**
     * Create new ticket
     */
    public function onCreateTicket()
    {
        $user = Auth::getUser();

        if (!$user) {
            throw new \ApplicationException('Debes iniciar sesión para crear un ticket');
        }

        $rules = [
            'department_id' => 'required|exists:aero_clouds_support_departments,id',
            'subject' => 'required|max:255',
            'message' => 'required',
            'priority' => 'required|in:low,normal,high,urgent'
        ];

        $validation = \Validator::make(post(), $rules);

        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        $ticket = Ticket::create([
            'user_id' => $user->id,
            'department_id' => post('department_id'),
            'subject' => post('subject'),
            'message' => post('message'),
            'priority' => post('priority', 'normal'),
            'status' => 'open'
        ]);

        Flash::success('Ticket creado exitosamente. Número: ' . $ticket->ticket_number);

        return [
            'redirect' => '/dashboard/support/' . $ticket->ticket_number
        ];
    }

    /**
     * Add reply to ticket
     */
    public function onAddReply()
    {
        $user = Auth::getUser();

        if (!$user) {
            throw new \ApplicationException('Debes iniciar sesión');
        }

        $ticketNumber = post('ticket_number');
        $message = post('message');

        if (!$message) {
            throw new ValidationException(['message' => 'El mensaje es requerido']);
        }

        $ticket = Ticket::where('ticket_number', $ticketNumber)
            ->forUser($user->id)
            ->first();

        if (!$ticket) {
            throw new \ApplicationException('Ticket no encontrado');
        }

        if ($ticket->isClosed()) {
            throw new \ApplicationException('Este ticket está cerrado');
        }

        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $message,
            'is_internal' => false
        ]);

        Flash::success('Respuesta enviada exitosamente');

        return Redirect::to('/dashboard/support/' . $ticketNumber);
    }

    /**
     * Close ticket
     */
    public function onCloseTicket()
    {
        $user = Auth::getUser();

        if (!$user) {
            throw new \ApplicationException('Debes iniciar sesión');
        }

        $ticketNumber = post('ticket_number');

        $ticket = Ticket::where('ticket_number', $ticketNumber)
            ->forUser($user->id)
            ->first();

        if (!$ticket) {
            throw new \ApplicationException('Ticket no encontrado');
        }

        $ticket->closeTicket();

        Flash::success('Ticket cerrado exitosamente');

        return Redirect::to('/dashboard/support');
    }

    /**
     * Reopen ticket
     */
    public function onReopenTicket()
    {
        $user = Auth::getUser();

        if (!$user) {
            throw new \ApplicationException('Debes iniciar sesión');
        }

        $ticketNumber = post('ticket_number');

        $ticket = Ticket::where('ticket_number', $ticketNumber)
            ->forUser($user->id)
            ->first();

        if (!$ticket) {
            throw new \ApplicationException('Ticket no encontrado');
        }

        $ticket->reopenTicket();

        Flash::success('Ticket reabierto exitosamente');

        return Redirect::to('/dashboard/support/' . $ticketNumber);
    }

    /**
     * Get ticket statistics
     */
    public function getTicketStats()
    {
        $user = Auth::getUser();

        if (!$user) {
            return [
                'total' => 0,
                'open' => 0,
                'closed' => 0,
                'waiting' => 0
            ];
        }

        $tickets = Ticket::forUser($user->id);

        return [
            'total' => $tickets->count(),
            'open' => $tickets->where('status', 'open')->count(),
            'in_progress' => $tickets->where('status', 'in_progress')->count(),
            'waiting_on_staff' => $tickets->where('status', 'waiting_on_staff')->count(),
            'closed' => $tickets->where('status', 'closed')->count()
        ];
    }

    /**
     * Get priority label
     */
    public function getPriorityLabel($priority)
    {
        $labels = [
            'low' => 'Baja',
            'normal' => 'Normal',
            'high' => 'Alta',
            'urgent' => 'Urgente'
        ];

        return $labels[$priority] ?? $priority;
    }

    /**
     * Get status label
     */
    public function getStatusLabel($status)
    {
        $labels = [
            'open' => 'Abierto',
            'in_progress' => 'En Progreso',
            'waiting_on_customer' => 'Esperando tu respuesta',
            'waiting_on_staff' => 'Esperando respuesta del staff',
            'closed' => 'Cerrado'
        ];

        return $labels[$status] ?? $status;
    }
}
