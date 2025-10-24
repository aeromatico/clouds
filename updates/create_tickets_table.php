<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * CreateTicketsTable Migration
 */
class CreateTicketsTable extends Migration
{
    public function up()
    {
        Schema::create('aero_clouds_tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('department_id');
            $table->unsignedInteger('assigned_to')->nullable();
            $table->string('ticket_number')->unique();
            $table->string('subject');
            $table->text('message');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', ['open', 'in_progress', 'waiting_on_customer', 'waiting_on_staff', 'closed'])->default('open');
            $table->timestamp('last_reply_at')->nullable();
            $table->enum('last_reply_by', ['customer', 'staff'])->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->unsignedInteger('closed_by')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('aero_clouds_support_departments')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('backend_users')->onDelete('set null');
            $table->foreign('closed_by')->references('id')->on('backend_users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('aero_clouds_tickets');
    }
}
