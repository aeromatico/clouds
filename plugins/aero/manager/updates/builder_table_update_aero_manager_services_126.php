<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerServices126 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_services', function($table)
        {
            $table->text('slug_api')->nullable();
            $table->string('sort_order', 10)->nullable()->unsigned(false)->default('0')->comment(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_services', function($table)
        {
            $table->dropColumn('slug_api');
            $table->integer('sort_order')->nullable()->unsigned(false)->default(0)->comment(null)->change();
        });
    }
}
