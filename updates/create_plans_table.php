<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreatePlansTable extends Migration
{
    public function up()
    {
        Schema::create('aero_clouds_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('BOB');
            $table->enum('billing_cycle', [
                'monthly',
                'quarterly',
                'semi_annually',
                'annually',
                'biennially',
                'triennially'
            ])->default('monthly');
            $table->decimal('setup_fee', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->json('features')->nullable();
            $table->json('limits')->nullable();
            $table->timestamps();

            $table->foreign('service_id')->references('id')->on('aero_clouds_services')->onDelete('cascade');
            $table->index(['service_id', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('aero_clouds_plans');
    }
}