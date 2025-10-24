<?php namespace Aero\Clouds\Console;

use Aero\Clouds\Models\EmailEvent;
use Illuminate\Console\Command;

class SeedEmailEvents extends Command
{
    protected $signature = 'aero:seed-email-events';
    protected $description = 'Seed email events with default data';

    public function handle()
    {
        $this->info('Seeding email events...');

        $events = [
            // Orders Events
            [
                'event_code' => 'order_created',
                'event_name' => 'Nuevo pedido creado',
                'event_category' => 'orders',
                'description' => 'Se crea un nuevo pedido en el sistema',
                'user_template_code' => 'user:new-order',
                'admin_template_code' => 'backend:new-order',
                'notify_user' => true,
                'notify_admin' => true,
                'enabled' => true,
                'priority' => 10,
                'context_vars' => ['order_id', 'order_number', 'user', 'total', 'items']
            ],
            [
                'event_code' => 'order_completed',
                'event_name' => 'Pedido completado',
                'event_category' => 'orders',
                'description' => 'Un pedido ha sido completado exitosamente',
                'user_template_code' => 'user:order-completed',
                'admin_template_code' => null,
                'notify_user' => true,
                'notify_admin' => false,
                'enabled' => true,
                'priority' => 8,
                'context_vars' => ['order_id', 'order_number', 'user']
            ],
            [
                'event_code' => 'order_cancelled',
                'event_name' => 'Pedido cancelado',
                'event_category' => 'orders',
                'description' => 'Un pedido ha sido cancelado',
                'user_template_code' => 'user:order-cancelled',
                'admin_template_code' => 'backend:order-cancelled',
                'notify_user' => true,
                'notify_admin' => true,
                'enabled' => true,
                'priority' => 7,
                'context_vars' => ['order_id', 'order_number', 'user', 'reason']
            ],

            // Invoices Events
            [
                'event_code' => 'invoice_created',
                'event_name' => 'Nueva factura creada',
                'event_category' => 'invoices',
                'description' => 'Se crea una nueva factura',
                'user_template_code' => 'user:new-invoice',
                'admin_template_code' => null,
                'notify_user' => true,
                'notify_admin' => false,
                'enabled' => true,
                'priority' => 9,
                'context_vars' => ['invoice_id', 'invoice_number', 'user', 'total', 'due_date']
            ],
            [
                'event_code' => 'invoice_paid',
                'event_name' => 'Factura pagada',
                'event_category' => 'invoices',
                'description' => 'Una factura ha sido pagada',
                'user_template_code' => 'user:invoice-paid',
                'admin_template_code' => 'backend:invoice-paid',
                'notify_user' => true,
                'notify_admin' => true,
                'enabled' => true,
                'priority' => 10,
                'context_vars' => ['invoice_id', 'invoice_number', 'user', 'amount', 'payment_method']
            ],
            [
                'event_code' => 'invoice_overdue',
                'event_name' => 'Factura vencida',
                'event_category' => 'invoices',
                'description' => 'Una factura ha pasado su fecha de vencimiento',
                'user_template_code' => 'user:invoice-overdue',
                'admin_template_code' => 'backend:invoice-overdue',
                'notify_user' => true,
                'notify_admin' => true,
                'enabled' => true,
                'priority' => 8,
                'context_vars' => ['invoice_id', 'invoice_number', 'user', 'total', 'days_overdue']
            ],

            // Payment Events
            [
                'event_code' => 'payment_received',
                'event_name' => 'Pago recibido',
                'event_category' => 'payments',
                'description' => 'Se ha recibido un pago',
                'user_template_code' => 'user:payment-received',
                'admin_template_code' => 'backend:payment-received',
                'notify_user' => true,
                'notify_admin' => true,
                'enabled' => true,
                'priority' => 10,
                'context_vars' => ['payment_id', 'user', 'amount', 'method', 'invoice_id']
            ],
            [
                'event_code' => 'payment_failed',
                'event_name' => 'Pago fallido',
                'event_category' => 'payments',
                'description' => 'Un intento de pago ha fallado',
                'user_template_code' => 'user:payment-failed',
                'admin_template_code' => 'backend:payment-failed',
                'notify_user' => true,
                'notify_admin' => true,
                'enabled' => true,
                'priority' => 9,
                'context_vars' => ['payment_id', 'user', 'amount', 'error_message']
            ],

            // Cloud Servers Events
            [
                'event_code' => 'cloud_created',
                'event_name' => 'Servidor cloud creado',
                'event_category' => 'clouds',
                'description' => 'Un nuevo servidor cloud ha sido creado',
                'user_template_code' => 'user:cloud-created',
                'admin_template_code' => 'backend:cloud-created',
                'notify_user' => true,
                'notify_admin' => true,
                'enabled' => true,
                'priority' => 9,
                'context_vars' => ['cloud_id', 'user', 'service', 'plan', 'credentials']
            ],
            [
                'event_code' => 'cloud_suspended',
                'event_name' => 'Servidor cloud suspendido',
                'event_category' => 'clouds',
                'description' => 'Un servidor cloud ha sido suspendido',
                'user_template_code' => 'user:cloud-suspended',
                'admin_template_code' => 'backend:cloud-suspended',
                'notify_user' => true,
                'notify_admin' => true,
                'enabled' => true,
                'priority' => 8,
                'context_vars' => ['cloud_id', 'user', 'reason', 'suspension_date']
            ],
            [
                'event_code' => 'cloud_expiring',
                'event_name' => 'Servidor cloud próximo a vencer',
                'event_category' => 'clouds',
                'description' => 'Un servidor cloud está próximo a vencer',
                'user_template_code' => 'user:cloud-expiring',
                'admin_template_code' => 'backend:cloud-expiring',
                'notify_user' => true,
                'notify_admin' => true,
                'enabled' => true,
                'priority' => 7,
                'context_vars' => ['cloud_id', 'user', 'expiration_date', 'days_left']
            ],
            [
                'event_code' => 'cloud_expired',
                'event_name' => 'Servidor cloud expirado',
                'event_category' => 'clouds',
                'description' => 'Un servidor cloud ha expirado',
                'user_template_code' => 'user:cloud-expired',
                'admin_template_code' => 'backend:cloud-expired',
                'notify_user' => true,
                'notify_admin' => true,
                'enabled' => true,
                'priority' => 9,
                'context_vars' => ['cloud_id', 'user', 'expiration_date']
            ],

            // Domain Events
            [
                'event_code' => 'domain_registered',
                'event_name' => 'Dominio registrado',
                'event_category' => 'domains',
                'description' => 'Un nuevo dominio ha sido registrado',
                'user_template_code' => 'user:domain-registered',
                'admin_template_code' => 'backend:domain-registered',
                'notify_user' => true,
                'notify_admin' => true,
                'enabled' => true,
                'priority' => 8,
                'context_vars' => ['domain_name', 'user', 'registration_date', 'expiration_date']
            ],
            [
                'event_code' => 'domain_expiring',
                'event_name' => 'Dominio próximo a vencer',
                'event_category' => 'domains',
                'description' => 'Un dominio está próximo a vencer',
                'user_template_code' => 'user:domain-expiring',
                'admin_template_code' => 'backend:domain-expiring',
                'notify_user' => true,
                'notify_admin' => true,
                'enabled' => true,
                'priority' => 7,
                'context_vars' => ['domain_name', 'user', 'expiration_date', 'days_left']
            ],
            [
                'event_code' => 'domain_expired',
                'event_name' => 'Dominio expirado',
                'event_category' => 'domains',
                'description' => 'Un dominio ha expirado',
                'user_template_code' => 'user:domain-expired',
                'admin_template_code' => 'backend:domain-expired',
                'notify_user' => true,
                'notify_admin' => true,
                'enabled' => true,
                'priority' => 8,
                'context_vars' => ['domain_name', 'user', 'expiration_date']
            ],

            // Support Events
            [
                'event_code' => 'ticket_created',
                'event_name' => 'Nuevo ticket de soporte',
                'event_category' => 'support',
                'description' => 'Se ha creado un nuevo ticket de soporte',
                'user_template_code' => 'user:ticket-created',
                'admin_template_code' => 'backend:ticket-created',
                'notify_user' => true,
                'notify_admin' => true,
                'enabled' => true,
                'priority' => 9,
                'context_vars' => ['ticket_id', 'user', 'subject', 'department', 'priority']
            ],
            [
                'event_code' => 'ticket_reply',
                'event_name' => 'Respuesta en ticket',
                'event_category' => 'support',
                'description' => 'Se ha agregado una respuesta a un ticket',
                'user_template_code' => 'user:ticket-reply',
                'admin_template_code' => 'backend:ticket-reply',
                'notify_user' => true,
                'notify_admin' => true,
                'enabled' => true,
                'priority' => 8,
                'context_vars' => ['ticket_id', 'user', 'subject', 'reply_content', 'author']
            ],

            // Task Events
            [
                'event_code' => 'task_assigned',
                'event_name' => 'Tarea asignada',
                'event_category' => 'tasks',
                'description' => 'Una tarea ha sido asignada a un usuario',
                'user_template_code' => 'user:task-assigned',
                'admin_template_code' => null,
                'notify_user' => true,
                'notify_admin' => false,
                'enabled' => true,
                'priority' => 7,
                'context_vars' => ['task_id', 'title', 'assignee', 'due_date', 'priority']
            ],
            [
                'event_code' => 'task_completed',
                'event_name' => 'Tarea completada',
                'event_category' => 'tasks',
                'description' => 'Una tarea ha sido completada',
                'user_template_code' => null,
                'admin_template_code' => 'backend:task-completed',
                'notify_user' => false,
                'notify_admin' => true,
                'enabled' => true,
                'priority' => 6,
                'context_vars' => ['task_id', 'title', 'completed_by', 'completion_date']
            ],

            // User Events
            [
                'event_code' => 'user_registered',
                'event_name' => 'Nuevo usuario registrado',
                'event_category' => 'users',
                'description' => 'Un nuevo usuario se ha registrado en el sistema',
                'user_template_code' => 'user:welcome_email',
                'admin_template_code' => 'user:new_user_internal',
                'notify_user' => true,
                'notify_admin' => true,
                'enabled' => true,
                'priority' => 8,
                'context_vars' => ['user_id', 'name', 'email', 'registration_date']
            ],
        ];

        $count = 0;
        foreach ($events as $eventData) {
            EmailEvent::updateOrCreate(
                ['event_code' => $eventData['event_code']],
                $eventData
            );
            $count++;
        }

        $this->info("✓ {$count} eventos de email creados/actualizados exitosamente");
    }
}
