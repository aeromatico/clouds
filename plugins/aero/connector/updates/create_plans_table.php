<?php namespace Aero\Connector\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreatePlansTable extends Migration
{
    public function up()
    {
        Schema::create('aero_connector_plans', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();

            // Relationship to service
            $table->integer('service_id')->unsigned();
            $table->foreign('service_id')->references('id')->on('aero_connector_services')->onDelete('cascade');

            // Basic information
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('features')->nullable();

            // Pricing
            $table->decimal('price', 10, 2);
            $table->decimal('setup_fee', 10, 2)->default(0);
            $table->string('billing_cycle')->default('monthly'); // monthly, quarterly, yearly, custom

            // Status and visibility
            $table->boolean('is_active')->default(true);
            $table->boolean('is_popular')->default(false);
            $table->integer('sort_order')->default(0);

            // Limits and resources
            $table->json('resource_limits')->nullable();
            $table->json('pricing_options')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('aero_connector_plans');
    }
}