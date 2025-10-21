<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAeroManagerWalletBalance extends Migration
{
    public function up()
    {
        Schema::create('aero_manager_wallet_balance', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('user_id');
            $table->decimal('total_credits', 10, 0);
            $table->decimal('total_debits', 10, 0);
            $table->decimal('total', 10, 0);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aero_manager_wallet_balance');
    }
}
