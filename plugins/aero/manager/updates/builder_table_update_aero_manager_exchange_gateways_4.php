<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerExchangeGateways4 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_exchange_gateways', function($table)
        {
            $table->string('slug')->nullable();
            $table->string('name', 191)->nullable()->change();
            $table->decimal('fee_fixed', 10, 2)->nullable()->change();
            $table->decimal('fee_percentage', 10, 2)->nullable()->change();
            $table->boolean('active')->nullable()->change();
            $table->boolean('from_on')->nullable()->change();
            $table->boolean('to_on')->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_exchange_gateways', function($table)
        {
            $table->dropColumn('slug');
            $table->string('name', 191)->nullable(false)->change();
            $table->decimal('fee_fixed', 10, 2)->nullable(false)->change();
            $table->decimal('fee_percentage', 10, 2)->nullable(false)->change();
            $table->boolean('active')->nullable(false)->change();
            $table->boolean('from_on')->nullable(false)->change();
            $table->boolean('to_on')->nullable(false)->change();
        });
    }
}
