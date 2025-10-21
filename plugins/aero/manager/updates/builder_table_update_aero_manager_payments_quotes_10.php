<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerPaymentsQuotes10 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_payments_quotes', function($table)
        {
            $table->string('invoice')->nullable();
            $table->text('note')->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_payments_quotes', function($table)
        {
            $table->dropColumn('invoice');
            $table->text('note')->nullable(false)->change();
        });
    }
}
