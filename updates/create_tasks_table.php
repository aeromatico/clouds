<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateTasksTable extends Migration
{
    public function up()
    {
        Schema::create('aero_clouds_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('domain')->index();

            // Task details
            $table->string('title');
            $table->text('description')->nullable();

            // Status and priority
            $table->enum('status', ['todo', 'doing', 'done'])->default('todo')->index();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium')->index();

            // Dates
            $table->timestamp('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Assignments
            $table->unsignedInteger('assigned_to')->nullable()->index();
            $table->unsignedInteger('created_by')->nullable()->index();

            // Ordering within status columns
            $table->integer('order')->default(0)->index();

            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('assigned_to')
                ->references('id')
                ->on('backend_users')
                ->onDelete('set null');

            $table->foreign('created_by')
                ->references('id')
                ->on('backend_users')
                ->onDelete('set null');

            // Indexes for performance
            $table->index(['domain', 'status']);
            $table->index(['domain', 'assigned_to']);
            $table->index(['status', 'order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('aero_clouds_tasks');
    }
}
