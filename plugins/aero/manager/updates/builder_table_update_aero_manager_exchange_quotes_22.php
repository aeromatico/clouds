<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerExchangeQuotes22 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_exchange_quotes', function($table)
        {
            $table->boolean('chat_alert');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_exchange_quotes', function($table)
        {
            $table->dropColumn('chat_alert');
        });
    }
}
