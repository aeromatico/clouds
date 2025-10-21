<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerPlans68 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_plans', function($table)
        {
            $table->smallInteger('server_storage')->default(0);
            $table->smallInteger('server_memory')->default(0);
            $table->string('server_cpu_type')->default('virtual');
            $table->smallInteger('server_cpu')->default(0);
            $table->smallInteger('server_transfer')->default(0);
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('deleted_at');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_plans', function($table)
        {
            $table->dropColumn('server_storage');
            $table->dropColumn('server_memory');
            $table->dropColumn('server_cpu_type');
            $table->dropColumn('server_cpu');
            $table->dropColumn('server_transfer');
            $table->timestamp('created_at')->nullable()->default('NULL');
            $table->timestamp('updated_at')->nullable()->default('NULL');
            $table->timestamp('deleted_at')->nullable()->default('NULL');
        });
    }
}
