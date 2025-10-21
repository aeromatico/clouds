<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerServiceFeatures extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_service_features', function($table)
        {
            $table->smallInteger('services_id');
            $table->smallInteger('features_id');
            $table->dropColumn('service_id');
            $table->dropColumn('feature_id');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_service_features', function($table)
        {
            $table->dropColumn('services_id');
            $table->dropColumn('features_id');
            $table->smallInteger('service_id');
            $table->smallInteger('feature_id');
        });
    }
}
