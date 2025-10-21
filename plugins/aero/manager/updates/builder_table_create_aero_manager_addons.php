<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAeroManagerAddons extends Migration
{
    public function up()
    {
        Schema::create('aero_manager_addons', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('plan_id');
            $table->string('name');
            $table->text('description');
            $table->text('pricing');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aero_manager_addons');
    }
}
