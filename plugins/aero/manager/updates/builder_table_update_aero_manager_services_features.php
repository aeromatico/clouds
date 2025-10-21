<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerServicesFeatures extends Migration
{
    public function up()
    {
        Schema::rename('aero_manager_service_features', 'aero_manager_services_features');
    }
    
    public function down()
    {
        Schema::rename('aero_manager_services_features', 'aero_manager_service_features');
    }
}
