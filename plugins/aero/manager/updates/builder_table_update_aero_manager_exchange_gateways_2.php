<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerExchangeGateways2 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_exchange_gateways', function($table)
        {
            $table->decimal('fee_fixed', 10, 2)->change();
            $table->decimal('fee_percentage', 10, 2)->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_exchange_gateways', function($table)
        {
            $table->decimal('fee_fixed', 10, 0)->change();
            $table->decimal('fee_percentage', 10, 0)->change();
        });
    }
}
