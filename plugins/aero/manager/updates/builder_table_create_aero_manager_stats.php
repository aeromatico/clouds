<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAeroManagerStats extends Migration
{
    public function up()
    {
        Schema::create('aero_manager_stats', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('url');
            $table->string('ip');
            $table->string('city');
            $table->string('region');
            $table->string('country');
            $table->string('geo');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aero_manager_stats');
    }
}
