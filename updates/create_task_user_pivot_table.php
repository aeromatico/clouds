<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateTaskUserPivotTable extends Migration
{
    public function up()
    {
        Schema::create('aero_clouds_task_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->unsignedInteger('user_id');
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

            // Prevent duplicate assignments
            $table->unique(['task_id', 'user_id']);

            // Indexes for performance
            $table->index('task_id');
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('aero_clouds_task_user');
    }
}
