<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerPlans84 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_plans', function($table)
        {
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('server_storage', 50)->default('null')->change();
            $table->string('server_memory', 50)->default('null')->change();
            $table->string('server_cpu', 50)->default('null')->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_plans', function($table)
        {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('deleted_at');
            $table->string('server_storage', 50)->default('\'null\'')->change();
            $table->string('server_memory', 50)->default('\'null\'')->change();
            $table->string('server_cpu', 50)->default('\'null\'')->change();
        });
    }
}
