<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerExchangeQuotes18 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_exchange_quotes', function($table)
        {
            $table->decimal('endtime', 3, 1)->default(1)->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_exchange_quotes', function($table)
        {
            $table->decimal('endtime', 3, 1)->default(null)->change();
        });
    }
}
