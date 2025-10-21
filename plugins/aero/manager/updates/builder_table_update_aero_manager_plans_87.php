<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerPlans87 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_plans', function($table)
        {
            $table->string('server_storage', 50)->default('null')->change();
            $table->string('server_memory', 50)->default('null')->change();
            $table->string('server_cpu', 50)->default('null')->change();
            $table->string('server_transfer', 50)->nullable()->unsigned(false)->default('null')->change();
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('deleted_at');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_plans', function($table)
        {
            $table->string('server_storage', 50)->default('\'null\'')->change();
            $table->string('server_memory', 50)->default('\'null\'')->change();
            $table->string('server_cpu', 50)->default('\'null\'')->change();
            $table->smallInteger('server_transfer')->nullable(false)->unsigned(false)->default(0)->change();
            $table->timestamp('created_at')->nullable()->default('NULL');
            $table->timestamp('updated_at')->nullable()->default('NULL');
            $table->timestamp('deleted_at')->nullable()->default('NULL');
        });
    }
}
