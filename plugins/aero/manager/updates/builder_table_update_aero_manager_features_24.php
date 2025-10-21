<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerFeatures24 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_features', function($table)
        {
            $table->string('img_alt');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_features', function($table)
        {
            $table->dropColumn('img_alt');
        });
    }
}
