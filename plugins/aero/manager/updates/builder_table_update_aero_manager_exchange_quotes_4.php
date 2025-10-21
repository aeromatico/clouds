<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerExchangeQuotes4 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_exchange_quotes', function($table)
        {
            $table->text('note');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_exchange_quotes', function($table)
        {
            $table->dropColumn('note');
        });
    }
}
