<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerCargoQuotes5 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_cargo_quotes', function($table)
        {
            $table->text('urls')->nullable()->change();
            $table->string('type_shipment', 191)->nullable()->change();
            $table->string('endtime', 191)->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_cargo_quotes', function($table)
        {
            $table->text('urls')->nullable(false)->change();
            $table->string('type_shipment', 191)->nullable(false)->change();
            $table->string('endtime', 191)->nullable(false)->change();
        });
    }
}
