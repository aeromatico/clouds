<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class RemoveServiceIdFromFeaturesFaqsDocs extends Migration
{
    public function up()
    {
        // Remove service_id from features table
        if (Schema::hasColumn('aero_clouds_features', 'service_id')) {
            Schema::table('aero_clouds_features', function (Blueprint $table) {
                $table->dropForeign(['service_id']);
                $table->dropColumn('service_id');
            });
        }

        // Remove service_id from faqs table
        if (Schema::hasColumn('aero_clouds_faqs', 'service_id')) {
            Schema::table('aero_clouds_faqs', function (Blueprint $table) {
                $table->dropForeign(['service_id']);
                $table->dropColumn('service_id');
            });
        }

        // Remove service_id from docs table
        if (Schema::hasColumn('aero_clouds_docs', 'service_id')) {
            Schema::table('aero_clouds_docs', function (Blueprint $table) {
                $table->dropForeign(['service_id']);
                $table->dropColumn('service_id');
            });
        }
    }

    public function down()
    {
        // Add service_id back to features table
        if (!Schema::hasColumn('aero_clouds_features', 'service_id')) {
            Schema::table('aero_clouds_features', function (Blueprint $table) {
                $table->unsignedBigInteger('service_id')->nullable()->after('id');
                $table->foreign('service_id')->references('id')->on('aero_clouds_services')->onDelete('set null');
            });
        }

        // Add service_id back to faqs table
        if (!Schema::hasColumn('aero_clouds_faqs', 'service_id')) {
            Schema::table('aero_clouds_faqs', function (Blueprint $table) {
                $table->unsignedBigInteger('service_id')->nullable()->after('id');
                $table->foreign('service_id')->references('id')->on('aero_clouds_services')->onDelete('set null');
            });
        }

        // Add service_id back to docs table
        if (!Schema::hasColumn('aero_clouds_docs', 'service_id')) {
            Schema::table('aero_clouds_docs', function (Blueprint $table) {
                $table->unsignedBigInteger('service_id')->nullable()->after('id');
                $table->foreign('service_id')->references('id')->on('aero_clouds_services')->onDelete('set null');
            });
        }
    }
}
