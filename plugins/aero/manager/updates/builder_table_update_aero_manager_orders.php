<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerOrders extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_orders', function($table)
        {
            $table->renameColumn('payment_note', 'payment_titular_note');
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('deleted_at');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_orders', function($table)
        {
            $table->renameColumn('payment_titular_note', 'payment_note');
            $table->timestamp('created_at')->nullable()->default('NULL');
            $table->timestamp('updated_at')->nullable()->default('NULL');
            $table->timestamp('deleted_at')->nullable()->default('NULL');
        });
    }
}
