<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class UpdateTasksTableV2 extends Migration
{
    public function up()
    {
        Schema::table('aero_clouds_tasks', function (Blueprint $table) {
            // Add notes field for important information
            $table->text('notes')->nullable()->after('description');

            // Add archived_at for archiving completed tasks
            $table->timestamp('archived_at')->nullable()->after('completed_at');

            // Remove assigned_to since we're moving to many-to-many
            $table->dropForeign(['assigned_to']);
            $table->dropColumn('assigned_to');
        });
    }

    public function down()
    {
        Schema::table('aero_clouds_tasks', function (Blueprint $table) {
            $table->dropColumn(['notes', 'archived_at']);

            // Restore assigned_to
            $table->unsignedInteger('assigned_to')->nullable()->index()->after('completed_at');
            $table->foreign('assigned_to')
                ->references('id')
                ->on('backend_users')
                ->onDelete('set null');
        });
    }
}
