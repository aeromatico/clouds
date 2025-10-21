<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAeroManagerExchangesQuotes extends Migration
{
    public function up()
    {
        Schema::create('aero_manager_exchanges_quotes', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('from');
            $table->string('to');
            $table->decimal('fee', 10, 0);
            $table->decimal('fee_ext', 10, 0);
            $table->text('fee_ext_detail');
            $table->text('advice');
            $table->text('observations');
            $table->text('chat');
            $table->dateTime('endtime');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aero_manager_exchanges_quotes');
    }
}
