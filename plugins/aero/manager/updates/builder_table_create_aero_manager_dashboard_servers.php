<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAeroManagerDashboardServers extends Migration
{
    public function up()
    {
        Schema::create('aero_manager_dashboard_servers', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('user_id')->nullable()->default(1);
            $table->string('dc_user_id')->nullable();
            $table->string('family')->nullable();
            $table->string('datacenter')->nullable();
            $table->string('server_id')->nullable();
            $table->string('status')->nullable()->default('Pendiente');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->dateTime('finished_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aero_manager_dashboard_servers');
    }
}
