<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerFeatures21 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_features', function($table)
        {
            $table->boolean('featured');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_features', function($table)
        {
            $table->dropColumn('featured');
        });
    }
}
