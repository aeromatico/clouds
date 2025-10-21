<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerExchangesQuotes4 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_exchanges_quotes', function($table)
        {
            $table->smallInteger('from')->nullable(false)->unsigned(false)->default(null)->comment(null)->change();
            $table->smallInteger('to')->nullable(false)->unsigned(false)->default(null)->comment(null)->change();
            $table->dropColumn('fee_ext');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_exchanges_quotes', function($table)
        {
            $table->string('from', 191)->nullable(false)->unsigned(false)->default(null)->comment(null)->change();
            $table->string('to', 191)->nullable(false)->unsigned(false)->default(null)->comment(null)->change();
            $table->decimal('fee_ext', 10, 0);
        });
    }
}
