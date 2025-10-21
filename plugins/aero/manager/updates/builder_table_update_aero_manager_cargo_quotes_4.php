<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerCargoQuotes4 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_cargo_quotes', function($table)
        {
            $table->string('endtime');
            $table->string('status')->default('Pendiente');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_cargo_quotes', function($table)
        {
            $table->dropColumn('endtime');
            $table->dropColumn('status');
        });
    }
}
