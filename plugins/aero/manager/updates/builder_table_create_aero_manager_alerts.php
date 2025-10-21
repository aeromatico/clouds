<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAeroManagerAlerts extends Migration
{
    public function up()
    {
        Schema::create('aero_manager_alerts', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->integer('client_id');
            $table->string('domain');
            $table->smallInteger('order_id');
            $table->smallInteger('package_id');
            $table->decimal('amount', 10, 0);
            $table->string('status');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aero_manager_alerts');
    }
}
