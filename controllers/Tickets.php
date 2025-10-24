<?php namespace Aero\Clouds\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Aero\Clouds\Models\Ticket;
use Aero\Clouds\Models\TicketReply;
use Flash;
use BackendAuth;

class Tickets extends Controller
{
    public $implement = [
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\RelationController::class
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $relationConfig = 'config_relation.yaml';

    public $requiredPermissions = ['aero.clouds.support'];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Aero.Clouds', 'clouds-support', 'support-tickets');
    }

    /**
     * Extend list query to eager load relationships
     */
    public function listExtendQuery($query)
    {
        $query->with(['user', 'department', 'assigned_user']);
    }

    /**
     * Close ticket
     */
    public function onClose()
    {
        $ticketId = post('id');
        $ticket = Ticket::find($ticketId);

        if ($ticket) {
            $ticket->closeTicket(BackendAuth::getUser()->id);
            Flash::success('Ticket cerrado exitosamente');
        }

        return $this->listRefresh();
    }

    /**
     * Reopen ticket
     */
    public function onReopen()
    {
        $ticketId = post('id');
        $ticket = Ticket::find($ticketId);

        if ($ticket) {
            $ticket->reopenTicket();
            Flash::success('Ticket reabierto exitosamente');
        }

        return $this->listRefresh();
    }

    /**
     * Assign ticket
     */
    public function onAssign()
    {
        $ticketId = post('id');
        $adminId = post('admin_id');

        $ticket = Ticket::find($ticketId);

        if ($ticket) {
            $ticket->assigned_to = $adminId ?: null;
            $ticket->save();
            Flash::success('Ticket asignado exitosamente');
        }

        return $this->listRefresh();
    }

    /**
     * Add reply to ticket
     */
    public function onAddReply()
    {
        $ticketId = post('ticket_id');
        $message = post('message');
        $isInternal = post('is_internal', false);

        if (!$message) {
            Flash::error('El mensaje es requerido');
            return;
        }

        $reply = TicketReply::create([
            'ticket_id' => $ticketId,
            'admin_id' => BackendAuth::getUser()->id,
            'message' => $message,
            'is_internal' => $isInternal
        ]);

        Flash::success('Respuesta agregada exitosamente');

        return redirect()->back();
    }

    /**
     * Update ticket status
     */
    public function onUpdateStatus()
    {
        $ticketId = post('id');
        $status = post('status');

        $ticket = Ticket::find($ticketId);

        if ($ticket) {
            $ticket->status = $status;
            $ticket->save();
            Flash::success('Estado actualizado exitosamente');
        }

        return $this->listRefresh();
    }

    /**
     * Update ticket priority
     */
    public function onUpdatePriority()
    {
        $ticketId = post('id');
        $priority = post('priority');

        $ticket = Ticket::find($ticketId);

        if ($ticket) {
            $ticket->priority = $priority;
            $ticket->save();
            Flash::success('Prioridad actualizada exitosamente');
        }

        return $this->listRefresh();
    }
}
