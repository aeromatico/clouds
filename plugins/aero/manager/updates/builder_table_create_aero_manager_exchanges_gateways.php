<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAeroManagerExchangesGateways extends Migration
{
    public function up()
    {
        Schema::create('aero_manager_exchanges_gateways', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('name');
            $table->boolean('fee_fixed');
            $table->decimal('fee_percentage', 10, 0);
            $table->boolean('active');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aero_manager_exchanges_gateways');
    }
}
