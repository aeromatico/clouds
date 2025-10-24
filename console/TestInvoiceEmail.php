<?php namespace Aero\Clouds\Console;

use Illuminate\Console\Command;
use Aero\Clouds\Models\EmailEvent;
use RainLab\User\Models\User;

class TestInvoiceEmail extends Command
{
    protected $signature = 'aero:test-invoice-email {user_id?}';
    protected $description = 'Test invoice_created email event';

    public function handle()
    {
        $userId = $this->argument('user_id') ?? 1;

        $user = User::find($userId);
        if (!$user) {
            $this->error("User with ID {$userId} not found");
            return;
        }

        $this->info("Testing invoice_created email event...");
        $this->info("User: {$user->email}");

        // Prepare test context
        $context = [
            'invoice_id' => 123,
            'invoice_number' => 'INV-TEST-001',
            'order_id' => 456,
            'user' => [
                'name' => $user->full_name ?? $user->email,
                'email' => $user->email,
                'first_name' => $user->first_name ?? 'Usuario',
            ],
            'invoice_date' => date('d/m/Y'),
            'due_date' => date('d/m/Y', strtotime('+30 days')),
            'subtotal' => '100.00',
            'tax' => '0.00',
            'total' => '100.00',
            'items' => [
                [
                    'description' => 'Plan Test - Monthly',
                    'quantity' => 1,
                    'unit_price' => 100.00
                ]
            ],
            'status' => 'draft',
        ];

        // Fire the event
        $result = EmailEvent::fire('invoice_created', $context, $user);

        if ($result === null) {
            $this->error('Event not found or disabled!');
            return;
        }

        // Show results
        $this->info('Results:');
        $this->line('  User email sent: ' . ($result['sent_to_user'] ? '✓ Yes' : '✗ No'));
        $this->line('  Admin emails sent: ' . ($result['sent_to_admins'] ? '✓ Yes' : '✗ No'));

        if (!empty($result['errors'])) {
            $this->error('Errors:');
            foreach ($result['errors'] as $error) {
                $this->error('  ' . $error);
            }
        } else {
            $this->info('✓ All emails sent successfully!');
        }
    }
}
