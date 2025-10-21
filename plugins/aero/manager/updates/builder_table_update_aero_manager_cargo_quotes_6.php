<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerCargoQuotes6 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_cargo_quotes', function($table)
        {
            $table->boolean('digital_on')->nullable()->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_cargo_quotes', function($table)
        {
            $table->dropColumn('digital_on');
        });
    }
}
