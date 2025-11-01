<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class FixTasksDatetimeColumns extends Migration
{
    public function up()
    {
        Schema::table('aero_clouds_tasks', function (Blueprint $table) {
            // Change due_date from date to datetime
            $table->datetime('due_date')->nullable()->change();

            // Change completed_at from date to datetime
            $table->datetime('completed_at')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('aero_clouds_tasks', function (Blueprint $table) {
            // Revert back to date type
            $table->date('due_date')->nullable()->change();
            $table->date('completed_at')->nullable()->change();
        });
    }
}
