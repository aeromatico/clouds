<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateFeaturesTable extends Migration
{
    public function up()
    {
        Schema::create('aero_clouds_features', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_highlighted')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('service_id')->references('id')->on('aero_clouds_services')->onDelete('set null');
            $table->index('service_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('aero_clouds_features');
    }
}