<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerServices101 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_services', function($table)
        {
            $table->string('theme', 10)->nullable(false)->unsigned(false)->default('1')->comment(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_services', function($table)
        {
            $table->smallInteger('theme')->nullable(false)->unsigned(false)->default(1)->comment(null)->change();
        });
    }
}
