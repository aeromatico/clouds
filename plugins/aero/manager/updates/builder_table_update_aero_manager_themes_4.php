<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerThemes4 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_themes', function($table)
        {
            $table->string('collection');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_themes', function($table)
        {
            $table->dropColumn('collection');
        });
    }
}
