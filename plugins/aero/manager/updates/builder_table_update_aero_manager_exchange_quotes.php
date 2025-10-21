<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerExchangeQuotes extends Migration
{
    public function up()
    {
        Schema::rename('aero_manager_exchanges_quotes', 'aero_manager_exchange_quotes');
    }
    
    public function down()
    {
        Schema::rename('aero_manager_exchange_quotes', 'aero_manager_exchanges_quotes');
    }
}
