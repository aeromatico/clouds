<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAeroManagerOrders extends Migration
{
    public function up()
    {
        Schema::create('aero_manager_orders', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->integer('user_id');
            $table->integer('service_id');
            $table->string('type');
            $table->string('payment_titular_name');
            $table->string('payment_titular_email');
            $table->string('payment_note');
            $table->string('domain');
            $table->string('status');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aero_manager_orders');
    }
}
