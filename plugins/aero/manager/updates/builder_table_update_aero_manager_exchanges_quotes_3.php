<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerExchangesQuotes3 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_exchanges_quotes', function($table)
        {
            $table->string('identifier');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_exchanges_quotes', function($table)
        {
            $table->dropColumn('identifier');
        });
    }
}
