<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAeroManagerPlans extends Migration
{
    public function up()
    {
        Schema::create('aero_manager_plans', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->double('price', 10, 0);
            $table->string('price_period');
            $table->string('price_currency');
            $table->text('parameters');
            $table->string('slug');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aero_manager_plans');
    }
}
