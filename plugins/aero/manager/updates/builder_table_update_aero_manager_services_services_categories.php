<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerServicesServicesCategories extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_services_services_categories', function($table)
        {
            $table->renameColumn('categories_id', 'services_categories_id');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_services_services_categories', function($table)
        {
            $table->renameColumn('services_categories_id', 'categories_id');
        });
    }
}
