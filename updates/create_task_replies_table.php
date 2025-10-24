<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateTaskRepliesTable extends Migration
{
    public function up()
    {
        Schema::create('aero_clouds_task_replies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->unsignedInteger('user_id');
            $table->text('message');
            $table->timestamps();

            // Foreign keys
            $table->foreign('task_id')
                ->references('id')
                ->on('aero_clouds_tasks')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('backend_users')
                ->onDelete('cascade');

            // Indexes
            $table->index('task_id');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('aero_clouds_task_replies');
    }
}
