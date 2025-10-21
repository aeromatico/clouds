<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerCargoQuotes2 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_cargo_quotes', function($table)
        {
            $table->text('identifier');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_cargo_quotes', function($table)
        {
            $table->dropColumn('identifier');
        });
    }
}
