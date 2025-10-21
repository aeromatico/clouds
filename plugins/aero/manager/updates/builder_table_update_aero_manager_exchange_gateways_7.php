<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerExchangeGateways7 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_exchange_gateways', function($table)
        {
            $table->decimal('fee_own', 10, 2)->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_exchange_gateways', function($table)
        {
            $table->decimal('fee_own', 10, 2)->nullable(false)->change();
        });
    }
}
