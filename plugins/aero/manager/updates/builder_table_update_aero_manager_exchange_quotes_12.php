<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerExchangeQuotes12 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_exchange_quotes', function($table)
        {
            $table->text('note')->nullable()->change();
            $table->boolean('gross_on')->nullable()->change();
            $table->decimal('amount', 10, 2)->nullable()->change();
            $table->decimal('endtime', 3, 1)->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_exchange_quotes', function($table)
        {
            $table->text('note')->nullable(false)->change();
            $table->boolean('gross_on')->nullable(false)->change();
            $table->decimal('amount', 10, 2)->nullable(false)->change();
            $table->decimal('endtime', 3, 1)->nullable(false)->change();
        });
    }
}
