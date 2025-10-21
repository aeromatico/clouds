<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerOrders7 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_orders', function($table)
        {
            $table->string('panel')->nullable();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('status')->nullable()->default('Pendiente')->change();
            $table->dropColumn('plan_id');
            $table->dropColumn('payment_titular_name');
            $table->dropColumn('payment_titular_email');
            $table->dropColumn('payment_titular_note');
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('deleted_at');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_orders', function($table)
        {
            $table->dropColumn('panel');
            $table->dropColumn('username');
            $table->dropColumn('password');
            $table->string('status', 191)->nullable(false)->default('paid')->change();
            $table->integer('plan_id');
            $table->string('payment_titular_name', 191);
            $table->string('payment_titular_email', 191);
            $table->string('payment_titular_note', 191)->nullable()->default('null');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }
}
