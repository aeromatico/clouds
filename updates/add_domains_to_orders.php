<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddDomainsToOrders extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('aero_clouds_orders', 'domains')) {
            Schema::table('aero_clouds_orders', function($table)
            {
                $table->text('domains')->nullable()->after('items');
            });
        }
    }

    public function down()
    {
        Schema::table('aero_clouds_orders', function($table)
        {
            $table->dropColumn('domains');
        });
    }
}
