<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateAddonServicePivotTable extends Migration
{
    public function up()
    {
        Schema::create('aero_clouds_addon_service', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('addon_id');
            $table->unsignedBigInteger('service_id');
            $table->timestamps();

            $table->foreign('addon_id')->references('id')->on('aero_clouds_addons')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('aero_clouds_services')->onDelete('cascade');

            $table->unique(['addon_id', 'service_id']);
            $table->index('addon_id');
            $table->index('service_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('aero_clouds_addon_service');
    }
}
