<?php namespace Aero\ApiHub\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * CreateApisTable Migration
 */
class CreateApisTable extends Migration
{
    public function up()
    {
        Schema::create('aero_apihub_apis', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('category', 100)->nullable()->index();
            $table->string('rapidapi_id')->nullable()->index();
            $table->string('rapidapi_version_id')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamp('synced_at')->nullable()->index();
            $table->timestamps();

            // Indexes for performance
            $table->index(['category', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('aero_apihub_apis');
    }
}
