<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerExchangesGateways2 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_exchanges_gateways', function($table)
        {
            $table->decimal('fee_fixed', 10, 0)->default(0)->change();
            $table->decimal('fee_percentage', 10, 0)->default(0)->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_exchanges_gateways', function($table)
        {
            $table->decimal('fee_fixed', 10, 0)->default(null)->change();
            $table->decimal('fee_percentage', 10, 0)->default(null)->change();
        });
    }
}
