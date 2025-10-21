<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAeroManagerDashboardCards extends Migration
{
    public function up()
    {
        Schema::create('aero_manager_dashboard_cards', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->smallInteger('user_id');
            $table->string('card_holder');
            $table->string('card_number', 16);
            $table->date('card_expiration');
            $table->string('card_cvv', 3);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aero_manager_dashboard_cards');
    }
}
