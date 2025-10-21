<?php namespace Aero\Connector\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateServicesTable extends Migration
{
    public function up()
    {
        Schema::create('aero_connector_services', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();

            // Basic information
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();

            // Status and visibility
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(true);
            $table->integer('sort_order')->default(0);

            // Categorization
            $table->string('category')->nullable();
            $table->string('type')->default('hosting'); // hosting, domain, ssl, etc.

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('aero_connector_services');
    }
}