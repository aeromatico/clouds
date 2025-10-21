<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerThemes2 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_themes', function($table)
        {
            $table->dropColumn('deleted_at');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_themes', function($table)
        {
            $table->timestamp('deleted_at')->nullable();
        });
    }
}
