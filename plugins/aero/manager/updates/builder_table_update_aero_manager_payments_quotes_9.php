<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerPaymentsQuotes9 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_payments_quotes', function($table)
        {
            $table->decimal('endtime', 3, 1)->nullable()->unsigned(false)->default(0.5)->comment(null)->change();
            $table->decimal('fee', 10, 2)->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_payments_quotes', function($table)
        {
            $table->smallInteger('endtime')->nullable()->unsigned(false)->default(null)->comment(null)->change();
            $table->decimal('fee', 10, 0)->change();
        });
    }
}
