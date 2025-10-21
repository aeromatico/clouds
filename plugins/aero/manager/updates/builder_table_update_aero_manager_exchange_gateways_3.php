<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerExchangeGateways3 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_exchange_gateways', function($table)
        {
            $table->boolean('from_on');
            $table->boolean('to_on');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_exchange_gateways', function($table)
        {
            $table->dropColumn('from_on');
            $table->dropColumn('to_on');
        });
    }
}
