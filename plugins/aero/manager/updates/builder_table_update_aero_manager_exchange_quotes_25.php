<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerExchangeQuotes25 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_exchange_quotes', function($table)
        {
            $table->decimal('fee_gateway', 10, 0)->nullable();
            $table->dropColumn('fee_ext');
            $table->dropColumn('fees');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_exchange_quotes', function($table)
        {
            $table->dropColumn('fee_gateway');
            $table->smallInteger('fee_ext')->nullable();
            $table->text('fees')->nullable();
        });
    }
}
