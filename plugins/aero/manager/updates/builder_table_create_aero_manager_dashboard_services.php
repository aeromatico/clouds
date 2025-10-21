<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAeroManagerDashboardServices extends Migration
{
    public function up()
    {
        Schema::create('aero_manager_dashboard_services', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->text('panel')->nullable();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('type')->nullable()->default('account');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aero_manager_dashboard_services');
    }
}
