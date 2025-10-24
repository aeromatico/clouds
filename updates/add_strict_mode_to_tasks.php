<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddStrictModeToTasks extends Migration
{
    public function up()
    {
        Schema::table('aero_clouds_tasks', function($table)
        {
            $table->boolean('strict_mode')->default(false)->after('due_date');
        });
    }

    public function down()
    {
        Schema::table('aero_clouds_tasks', function($table)
        {
            $table->dropColumn('strict_mode');
        });
    }
}
