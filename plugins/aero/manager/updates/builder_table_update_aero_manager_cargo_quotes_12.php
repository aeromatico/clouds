<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerCargoQuotes12 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_cargo_quotes', function($table)
        {
            $table->boolean('chat_alert')->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_cargo_quotes', function($table)
        {
            $table->dropColumn('chat_alert');
        });
    }
}
