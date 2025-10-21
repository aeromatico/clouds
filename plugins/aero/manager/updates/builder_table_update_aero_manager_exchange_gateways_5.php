<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerExchangeGateways5 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_exchange_gateways', function($table)
        {
            $table->boolean('public_on')->nullable()->default(1);
            $table->dropColumn('active');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_exchange_gateways', function($table)
        {
            $table->dropColumn('public_on');
            $table->boolean('active')->nullable();
        });
    }
}
