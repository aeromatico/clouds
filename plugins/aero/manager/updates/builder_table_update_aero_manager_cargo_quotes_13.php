<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerCargoQuotes13 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_cargo_quotes', function($table)
        {
            $table->decimal('fee_gateway', 10, 2)->nullable()->default(0.00);
            $table->boolean('chat_alert')->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_cargo_quotes', function($table)
        {
            $table->dropColumn('fee_gateway');
            $table->boolean('chat_alert')->nullable(false)->change();
        });
    }
}
