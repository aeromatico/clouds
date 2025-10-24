<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class AddDeletedAtToTasks extends Migration
{
    public function up()
    {
        // Check if column doesn't exist and add it
        if (!Schema::hasColumn('aero_clouds_tasks', 'deleted_at')) {
            Schema::table('aero_clouds_tasks', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('aero_clouds_tasks', 'deleted_at')) {
            Schema::table('aero_clouds_tasks', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
}
