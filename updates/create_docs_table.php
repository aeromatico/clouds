<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateDocsTable extends Migration
{
    public function up()
    {
        Schema::create('aero_clouds_docs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content');
            $table->text('excerpt')->nullable();
            $table->string('category')->nullable();
            $table->json('tags')->nullable();
            $table->string('author')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('service_id')->references('id')->on('aero_clouds_services')->onDelete('set null');
            $table->index(['service_id', 'category']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('aero_clouds_docs');
    }
}