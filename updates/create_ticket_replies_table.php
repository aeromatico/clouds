<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * CreateTicketRepliesTable Migration
 */
class CreateTicketRepliesTable extends Migration
{
    public function up()
    {
        Schema::create('aero_clouds_ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedInteger('admin_id')->nullable();
            $table->text('message');
            $table->boolean('is_internal')->default(false);
            $table->timestamps();

            $table->foreign('ticket_id')->references('id')->on('aero_clouds_tickets')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('backend_users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('aero_clouds_ticket_replies');
    }
}
