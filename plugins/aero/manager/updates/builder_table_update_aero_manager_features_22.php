<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerFeatures22 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_features', function($table)
        {
            $table->string('button_text');
            $table->string('button_link');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_features', function($table)
        {
            $table->dropColumn('button_text');
            $table->dropColumn('button_link');
        });
    }
}
