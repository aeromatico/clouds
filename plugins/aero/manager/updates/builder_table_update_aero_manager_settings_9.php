<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerSettings9 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_settings', function($table)
        {
            $table->string('facebook_likes_counter');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_settings', function($table)
        {
            $table->dropColumn('facebook_likes_counter');
        });
    }
}
