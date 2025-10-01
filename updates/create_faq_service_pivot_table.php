<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateFaqServicePivotTable extends Migration
{
    public function up()
    {
        Schema::create('aero_clouds_faq_service', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('faq_id');
            $table->unsignedBigInteger('service_id');
            $table->timestamps();

            $table->foreign('faq_id')->references('id')->on('aero_clouds_faqs')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('aero_clouds_services')->onDelete('cascade');

            $table->unique(['faq_id', 'service_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('aero_clouds_faq_service');
    }
}
