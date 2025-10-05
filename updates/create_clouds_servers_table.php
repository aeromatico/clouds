<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateCloudsServersTable extends Migration
{
    public function up()
    {
        Schema::create('aero_clouds_servers', function($table)
        {
            $table->id();

            // Relationships
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('service_id')->nullable()->index();
            $table->unsignedBigInteger('plan_id')->nullable()->index();
            $table->unsignedBigInteger('order_id')->nullable()->index();

            // Basic Info
            $table->string('name');
            $table->string('domain')->nullable();

            // Panel Access
            $table->string('panel_url')->nullable();
            $table->string('panel_user')->nullable();
            $table->text('panel_password')->nullable(); // Encrypted

            // Server Info
            $table->string('ip_address', 45)->nullable();
            $table->string('server_type')->nullable(); // shared, vps, dedicated, cloud, etc.

            // Status
            $table->string('status')->default('pending')->index(); // pending, active, suspended, terminated, expired

            // Dates
            $table->timestamp('created_date')->nullable();
            $table->timestamp('expiration_date')->nullable()->index();
            $table->timestamp('last_renewal_date')->nullable();
            $table->timestamp('suspension_date')->nullable();
            $table->timestamp('termination_date')->nullable();

            // Reasons
            $table->text('suspension_reason')->nullable();
            $table->text('termination_reason')->nullable();

            // Settings
            $table->boolean('auto_renew')->default(false);
            $table->text('notes')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('service_id')
                ->references('id')
                ->on('aero_clouds_services')
                ->onDelete('set null');

            $table->foreign('plan_id')
                ->references('id')
                ->on('aero_clouds_plans')
                ->onDelete('set null');

            $table->foreign('order_id')
                ->references('id')
                ->on('aero_clouds_orders')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('aero_clouds_servers');
    }
}
