<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerExchangeGateways extends Migration
{
    public function up()
    {
        Schema::rename('aero_manager_exchanges_gateways', 'aero_manager_exchange_gateways');
    }
    
    public function down()
    {
        Schema::rename('aero_manager_exchange_gateways', 'aero_manager_exchanges_gateways');
    }
}
