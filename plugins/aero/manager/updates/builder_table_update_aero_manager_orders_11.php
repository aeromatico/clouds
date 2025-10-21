<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerOrders11 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_orders', function($table)
        {
            $table->string('type', 191)->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_orders', function($table)
        {
            $table->string('type', 191)->nullable(false)->change();
        });
    }
}
