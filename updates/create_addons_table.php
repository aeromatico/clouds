<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateAddonsTable extends Migration
{
    public function up()
    {
        Schema::create('aero_clouds_addons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('pricing', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['slug', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('aero_clouds_addons');
    }
}
