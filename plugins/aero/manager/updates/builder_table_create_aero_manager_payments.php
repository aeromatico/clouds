<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAeroManagerPayments extends Migration
{
    public function up()
    {
        Schema::create('aero_manager_payments', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('user_id');
            $table->integer('order_id');
            $table->string('payment_titular_name');
            $table->string('payment_titular_email');
            $table->string('payment_titular_note');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aero_manager_payments');
    }
}
