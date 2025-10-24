<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class AddOrderToTasks extends Migration
{
    public function up()
    {
        // Check if column doesn't exist and add it
        if (!Schema::hasColumn('aero_clouds_tasks', 'order')) {
            Schema::table('aero_clouds_tasks', function (Blueprint $table) {
                $table->integer('order')->default(0)->index()->after('created_by');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('aero_clouds_tasks', 'order')) {
            Schema::table('aero_clouds_tasks', function (Blueprint $table) {
                $table->dropColumn('order');
            });
        }
    }
}
