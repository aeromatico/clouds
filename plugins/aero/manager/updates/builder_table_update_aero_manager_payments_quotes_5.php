<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerPaymentsQuotes5 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_payments_quotes', function($table)
        {
            $table->boolean('chat_alert')->default(0)->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_payments_quotes', function($table)
        {
            $table->boolean('chat_alert')->default(1)->change();
        });
    }
}
