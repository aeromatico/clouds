<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAeroManagerCargoQuotes extends Migration
{
    public function up()
    {
        Schema::create('aero_manager_cargo_quotes', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->smallInteger('user_id');
            $table->text('urls');
            $table->string('type_shipment');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aero_manager_cargo_quotes');
    }
}
