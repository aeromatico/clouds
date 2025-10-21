<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerThemes5 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_themes', function($table)
        {
            $table->string('collection', 191)->default('Bienes Raíces')->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_themes', function($table)
        {
            $table->string('collection', 191)->default(null)->change();
        });
    }
}
