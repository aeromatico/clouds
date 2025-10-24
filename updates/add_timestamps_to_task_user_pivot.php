<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class AddTimestampsToTaskUserPivot extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('aero_clouds_task_user', 'created_at')) {
            Schema::table('aero_clouds_task_user', function (Blueprint $table) {
                $table->timestamp('created_at')->nullable();
            });
        }

        if (!Schema::hasColumn('aero_clouds_task_user', 'updated_at')) {
            Schema::table('aero_clouds_task_user', function (Blueprint $table) {
                $table->timestamp('updated_at')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('aero_clouds_task_user', 'created_at')) {
            Schema::table('aero_clouds_task_user', function (Blueprint $table) {
                $table->dropColumn('created_at');
            });
        }

        if (Schema::hasColumn('aero_clouds_task_user', 'updated_at')) {
            Schema::table('aero_clouds_task_user', function (Blueprint $table) {
                $table->dropColumn('updated_at');
            });
        }
    }
}
