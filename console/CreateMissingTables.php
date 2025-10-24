<?php namespace Aero\Clouds\Console;

use Illuminate\Console\Command;
use Schema;
use October\Rain\Database\Schema\Blueprint;

class CreateMissingTables extends Command
{
    protected $signature = 'aero:create-missing-tables';
    protected $description = 'Create missing Aero.Clouds database tables';

    public function handle()
    {
        $this->info('Creating missing tables...');

        $this->createInvoicesTable();
        $this->createPaymentGatewaysTable();
        $this->createAddonsTable();
        $this->createAddonServiceTable();
        $this->createCloudsTable();
        $this->createDomainsTable();
        $this->createDomainProvidersTable();
        $this->createDomainExtensionsTable();
        $this->createSupportDepartmentsTable();
        $this->createTicketsTable();
        $this->createTicketRepliesTable();
        $this->createEmailLogsTable();
        $this->createTasksTable();
        $this->createTaskRepliesTable();
        $this->createTaskUserTable();

        $this->info('✓ All missing tables created successfully!');
    }

    protected function createInvoicesTable()
    {
        if (Schema::hasTable('aero_clouds_invoices')) {
            $this->warn('  - aero_clouds_invoices already exists');
            return;
        }

        Schema::create('aero_clouds_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('invoice_number')->unique();
            $table->date('invoice_date');
            $table->date('due_date');
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled', 'refunded'])->default('draft');
            $table->json('items');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->unsignedBigInteger('payment_gateway_id')->nullable();
            $table->text('notes')->nullable();
            $table->string('domain')->index()->default('clouds.com.bo');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('user_id');
            $table->index('status');
            $table->index('invoice_date');
        });

        $this->info('  ✓ aero_clouds_invoices created');
    }

    protected function createPaymentGatewaysTable()
    {
        if (Schema::hasTable('aero_clouds_payment_gateways')) {
            $this->warn('  - aero_clouds_payment_gateways already exists');
            return;
        }

        Schema::create('aero_clouds_payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('gateway_code')->unique();
            $table->boolean('is_active')->default(true);
            $table->json('config')->nullable();
            $table->string('domain')->index()->default('clouds.com.bo');
            $table->timestamps();
        });

        $this->info('  ✓ aero_clouds_payment_gateways created');
    }

    protected function createAddonsTable()
    {
        if (Schema::hasTable('aero_clouds_addons')) {
            $this->warn('  - aero_clouds_addons already exists');
            return;
        }

        Schema::create('aero_clouds_addons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2);
            $table->boolean('is_active')->default(true);
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->string('domain')->index()->default('clouds.com.bo');
            $table->timestamps();
        });

        $this->info('  ✓ aero_clouds_addons created');
    }

    protected function createAddonServiceTable()
    {
        if (Schema::hasTable('aero_clouds_addon_service')) {
            $this->warn('  - aero_clouds_addon_service already exists');
            return;
        }

        Schema::create('aero_clouds_addon_service', function (Blueprint $table) {
            $table->unsignedBigInteger('addon_id');
            $table->unsignedBigInteger('service_id');
            $table->foreign('addon_id')->references('id')->on('aero_clouds_addons')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('aero_clouds_services')->onDelete('cascade');
            $table->primary(['addon_id', 'service_id']);
        });

        $this->info('  ✓ aero_clouds_addon_service created');
    }

    protected function createCloudsTable()
    {
        if (Schema::hasTable('aero_clouds_clouds')) {
            $this->warn('  - aero_clouds_clouds already exists');
            return;
        }

        Schema::create('aero_clouds_clouds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('service_id')->nullable();
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('server_name');
            $table->enum('status', ['pending', 'active', 'suspended', 'terminated', 'expired'])->default('pending');
            $table->date('created_date');
            $table->date('expiration_date')->nullable();
            $table->date('renewal_date')->nullable();
            $table->boolean('auto_renew')->default(false);
            $table->string('ip_address')->nullable();
            $table->string('domain_name')->nullable();
            $table->string('panel_url')->nullable();
            $table->string('panel_username')->nullable();
            $table->string('panel_password')->nullable();
            $table->string('server_type', 50)->nullable();
            $table->text('notes')->nullable();
            $table->string('domain')->index()->default('clouds.com.bo');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'status']);
        });

        $this->info('  ✓ aero_clouds_clouds created');
    }

    protected function createDomainsTable()
    {
        if (Schema::hasTable('aero_clouds_domains')) {
            $this->warn('  - aero_clouds_domains already exists');
            return;
        }

        Schema::create('aero_clouds_domains', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('domain_name')->unique();
            $table->unsignedBigInteger('domain_extension_id')->nullable();
            $table->unsignedBigInteger('provider_id')->nullable();
            $table->enum('status', ['active', 'pending', 'expired', 'cancelled'])->default('pending');
            $table->date('registration_date');
            $table->date('expiration_date');
            $table->boolean('auto_renew')->default(false);
            $table->json('nameservers')->nullable();
            $table->string('domain')->index()->default('clouds.com.bo');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        $this->info('  ✓ aero_clouds_domains created');
    }

    protected function createDomainProvidersTable()
    {
        if (Schema::hasTable('aero_clouds_domain_providers')) {
            $this->warn('  - aero_clouds_domain_providers already exists');
            return;
        }

        Schema::create('aero_clouds_domain_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('api_key')->nullable();
            $table->string('api_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('domain')->index()->default('clouds.com.bo');
            $table->timestamps();
        });

        $this->info('  ✓ aero_clouds_domain_providers created');
    }

    protected function createDomainExtensionsTable()
    {
        if (Schema::hasTable('aero_clouds_domain_extensions')) {
            $this->warn('  - aero_clouds_domain_extensions already exists');
            return;
        }

        Schema::create('aero_clouds_domain_extensions', function (Blueprint $table) {
            $table->id();
            $table->string('extension');
            $table->decimal('price', 8, 2);
            $table->decimal('sale_price', 8, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->boolean('is_active')->default(true);
            $table->string('domain')->index()->default('clouds.com.bo');
            $table->timestamps();
        });

        $this->info('  ✓ aero_clouds_domain_extensions created');
    }

    protected function createSupportDepartmentsTable()
    {
        if (Schema::hasTable('aero_clouds_support_departments')) {
            $this->warn('  - aero_clouds_support_departments already exists');
            return;
        }

        Schema::create('aero_clouds_support_departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('domain')->index()->default('clouds.com.bo');
            $table->timestamps();
        });

        $this->info('  ✓ aero_clouds_support_departments created');
    }

    protected function createTicketsTable()
    {
        if (Schema::hasTable('aero_clouds_tickets')) {
            $this->warn('  - aero_clouds_tickets already exists');
            return;
        }

        Schema::create('aero_clouds_tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('department_id')->nullable();
            $table->string('ticket_number')->unique();
            $table->string('subject');
            $table->text('message');
            $table->enum('status', ['open', 'replied', 'customer-reply', 'on-hold', 'closed'])->default('open');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->string('domain')->index()->default('clouds.com.bo');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'status']);
        });

        $this->info('  ✓ aero_clouds_tickets created');
    }

    protected function createTicketRepliesTable()
    {
        if (Schema::hasTable('aero_clouds_ticket_replies')) {
            $this->warn('  - aero_clouds_ticket_replies already exists');
            return;
        }

        Schema::create('aero_clouds_ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('user_id');
            $table->text('message');
            $table->boolean('is_staff')->default(false);
            $table->string('domain')->index()->default('clouds.com.bo');
            $table->timestamps();

            $table->foreign('ticket_id')->references('id')->on('aero_clouds_tickets')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        $this->info('  ✓ aero_clouds_ticket_replies created');
    }

    protected function createEmailLogsTable()
    {
        if (Schema::hasTable('aero_clouds_email_logs')) {
            $this->warn('  - aero_clouds_email_logs already exists');
            return;
        }

        Schema::create('aero_clouds_email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('template_code');
            $table->string('recipient_email');
            $table->string('recipient_name')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->json('data')->nullable();
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->string('domain')->index()->default('clouds.com.bo');
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        $this->info('  ✓ aero_clouds_email_logs created');
    }

    protected function createTasksTable()
    {
        if (Schema::hasTable('aero_clouds_tasks')) {
            $this->warn('  - aero_clouds_tasks already exists');
            return;
        }

        Schema::create('aero_clouds_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['todo', 'in_progress', 'review', 'done'])->default('todo');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->unsignedBigInteger('created_by');
            $table->date('due_date')->nullable();
            $table->date('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('strict_mode')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->string('domain')->index()->default('clouds.com.bo');
            $table->timestamps();

            $table->index(['status', 'priority']);
        });

        $this->info('  ✓ aero_clouds_tasks created');
    }

    protected function createTaskRepliesTable()
    {
        if (Schema::hasTable('aero_clouds_task_replies')) {
            $this->warn('  - aero_clouds_task_replies already exists');
            return;
        }

        Schema::create('aero_clouds_task_replies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('user_id');
            $table->text('message');
            $table->string('domain')->index()->default('clouds.com.bo');
            $table->timestamps();

            $table->foreign('task_id')->references('id')->on('aero_clouds_tasks')->onDelete('cascade');
        });

        $this->info('  ✓ aero_clouds_task_replies created');
    }

    protected function createTaskUserTable()
    {
        if (Schema::hasTable('aero_clouds_task_user')) {
            $this->warn('  - aero_clouds_task_user already exists');
            return;
        }

        Schema::create('aero_clouds_task_user', function (Blueprint $table) {
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('task_id')->references('id')->on('aero_clouds_tasks')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->primary(['task_id', 'user_id']);
        });

        $this->info('  ✓ aero_clouds_task_user created');
    }
}
