<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateFaqsTable extends Migration
{
    public function up()
    {
        Schema::create('aero_clouds_faqs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->text('question');
            $table->longText('answer');
            $table->string('category')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('service_id')->references('id')->on('aero_clouds_services')->onDelete('set null');
            $table->index(['service_id', 'category']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('aero_clouds_faqs');
    }
}