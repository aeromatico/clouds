<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerThemes extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_themes', function($table)
        {
            $table->text('img')->nullable(false)->unsigned(false)->default(null)->comment(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_themes', function($table)
        {
            $table->smallInteger('img')->nullable(false)->unsigned(false)->default(null)->comment(null)->change();
        });
    }
}
