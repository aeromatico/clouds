<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class AddServiceIdBackToPlans extends Migration
{
    public function up()
    {
        Schema::table('aero_clouds_plans', function (Blueprint $table) {
            $table->unsignedBigInteger('service_id')->nullable()->after('id');
            $table->foreign('service_id')->references('id')->on('aero_clouds_services')->onDelete('set null');
            $table->index('service_id');
        });
    }

    public function down()
    {
        Schema::table('aero_clouds_plans', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropIndex(['service_id']);
            $table->dropColumn('service_id');
        });
    }
}