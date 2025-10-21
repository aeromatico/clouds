<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAeroManagerSettings extends Migration
{
    public function up()
    {
        Schema::create('aero_manager_settings', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('id')->unsigned();
            $table->string('domain');
            $table->string('name');
            $table->text('menus');
            $table->text('description');
            $table->text('metas');
            $table->text('google_analytics');
            $table->text('facebook_pixel');
            $table->primary(['id']);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aero_manager_settings');
    }
}
