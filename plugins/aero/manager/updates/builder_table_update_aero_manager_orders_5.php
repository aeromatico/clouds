<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerOrders5 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_orders', function($table)
        {
            $table->string('payment_titular_note', 191)->nullable()->change();
            $table->string('domain', 191)->nullable()->change();
            $table->string('status', 191)->default('paid')->change();
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('deleted_at');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_orders', function($table)
        {
            $table->string('payment_titular_note', 191)->nullable(false)->change();
            $table->string('domain', 191)->nullable(false)->change();
            $table->string('status', 191)->default(null)->change();
            $table->timestamp('created_at')->nullable()->default('NULL');
            $table->timestamp('updated_at')->nullable()->default('NULL');
            $table->timestamp('deleted_at')->nullable()->default('NULL');
        });
    }
}
