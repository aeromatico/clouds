<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAeroManagerPaymentsQuotes extends Migration
{
    public function up()
    {
        Schema::create('aero_manager_payments_quotes', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->smallInteger('user_id')->nullable();
            $table->text('url')->nullable();
            $table->text('gateways')->nullable();
            $table->string('identifier')->nullable();
            $table->smallInteger('endtime')->nullable();
            $table->string('status')->nullable();
            $table->text('chat')->nullable();
            $table->boolean('chat_on')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aero_manager_payments_quotes');
    }
}
