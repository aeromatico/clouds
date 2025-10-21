<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerPlans83 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_plans', function($table)
        {
            $table->boolean('landscape_plans_on')->default(1);
            $table->string('server_storage', 50)->default('null')->change();
            $table->string('server_memory', 50)->default('null')->change();
            $table->string('server_cpu', 50)->default('null')->change();
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('deleted_at');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_plans', function($table)
        {
            $table->dropColumn('landscape_plans_on');
            $table->string('server_storage', 50)->default('\'null\'')->change();
            $table->string('server_memory', 50)->default('\'null\'')->change();
            $table->string('server_cpu', 50)->default('\'null\'')->change();
            $table->timestamp('created_at')->nullable()->default('NULL');
            $table->timestamp('updated_at')->nullable()->default('NULL');
            $table->timestamp('deleted_at')->nullable()->default('NULL');
        });
    }
}
