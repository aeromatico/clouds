<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerPaymentsQuotes extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_payments_quotes', function($table)
        {
            $table->decimal('fee', 10, 0)->nullable();
            $table->smallInteger('fee_ext')->nullable();
            $table->text('fee_ext_detail')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_payments_quotes', function($table)
        {
            $table->dropColumn('fee');
            $table->dropColumn('fee_ext');
            $table->dropColumn('fee_ext_detail');
        });
    }
}
