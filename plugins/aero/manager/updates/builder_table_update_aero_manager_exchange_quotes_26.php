<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerExchangeQuotes26 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_exchange_quotes', function($table)
        {
            $table->decimal('fee_gateway', 10, 0)->default(0)->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_exchange_quotes', function($table)
        {
            $table->decimal('fee_gateway', 10, 0)->default(null)->change();
        });
    }
}
