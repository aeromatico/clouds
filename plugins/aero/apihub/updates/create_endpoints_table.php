<?php namespace Aero\ApiHub\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * CreateEndpointsTable Migration
 */
class CreateEndpointsTable extends Migration
{
    public function up()
    {
        Schema::create('aero_apihub_endpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_id')
                ->constrained('aero_apihub_apis')
                ->onDelete('cascade');
            $table->string('name');
            $table->string('method', 10)->index();
            $table->string('route', 500);
            $table->text('description')->nullable();
            $table->json('parameters')->nullable();
            $table->json('headers')->nullable();
            $table->json('response_example')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['api_id', 'method']);
            $table->index(['method', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('aero_apihub_endpoints');
    }
}
