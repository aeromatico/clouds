<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAeroManagerWallet extends Migration
{
    public function up()
    {
        Schema::create('aero_manager_wallet', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->smallInteger('user_id');
            $table->smallInteger('plan_id');
            $table->boolean('credit_load');
            $table->boolean('credit_spend');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aero_manager_wallet');
    }
}
