<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerPaymentsQuotes8 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_payments_quotes', function($table)
        {
            $table->boolean('chat_on')->default(1)->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_payments_quotes', function($table)
        {
            $table->boolean('chat_on')->default(null)->change();
        });
    }
}
