<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerExchangesQuotes6 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_exchanges_quotes', function($table)
        {
            $table->smallInteger('fee_ext');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_exchanges_quotes', function($table)
        {
            $table->dropColumn('fee_ext');
        });
    }
}
