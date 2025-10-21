<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerPlans81 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_plans', function($table)
        {
            $table->string('server_storage', 50)->nullable()->default(null)->change();
            $table->string('server_memory', 50)->nullable()->default(null)->change();
            $table->string('server_cpu', 50)->nullable()->default(null)->change();
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('deleted_at');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_plans', function($table)
        {
            $table->string('server_storage', 10)->nullable(false)->default('\'0\'')->change();
            $table->string('server_memory', 10)->nullable(false)->default('\'1\'')->change();
            $table->string('server_cpu', 10)->nullable(false)->default('\'1\'')->change();
            $table->timestamp('created_at')->nullable()->default('NULL');
            $table->timestamp('updated_at')->nullable()->default('NULL');
            $table->timestamp('deleted_at')->nullable()->default('NULL');
        });
    }
}
