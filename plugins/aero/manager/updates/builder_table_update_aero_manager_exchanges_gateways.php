<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerExchangesGateways extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_exchanges_gateways', function($table)
        {
            $table->decimal('fee_fixed', 10, 0)->nullable(false)->unsigned(false)->default(null)->comment(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_exchanges_gateways', function($table)
        {
            $table->boolean('fee_fixed')->nullable(false)->unsigned(false)->default(null)->comment(null)->change();
        });
    }
}
