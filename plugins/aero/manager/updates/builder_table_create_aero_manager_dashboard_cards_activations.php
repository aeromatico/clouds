<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAeroManagerDashboardCardsActivations extends Migration
{
    public function up()
    {
        Schema::create('aero_manager_dashboard_cards_activations', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->smallInteger('card_id')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('aero_manager_dashboard_cards_activations');
    }
}
