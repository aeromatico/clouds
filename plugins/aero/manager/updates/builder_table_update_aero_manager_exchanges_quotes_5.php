<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerExchangesQuotes5 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_exchanges_quotes', function($table)
        {
            $table->smallInteger('from_id');
            $table->smallInteger('to_id');
            $table->dropColumn('from');
            $table->dropColumn('to');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_exchanges_quotes', function($table)
        {
            $table->dropColumn('from_id');
            $table->dropColumn('to_id');
            $table->smallInteger('from');
            $table->smallInteger('to');
        });
    }
}
