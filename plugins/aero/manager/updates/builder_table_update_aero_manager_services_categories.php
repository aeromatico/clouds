<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerServicesCategories extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_services_categories', function($table)
        {
            $table->increments('id')->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_services_categories', function($table)
        {
            $table->integer('id')->change();
        });
    }
}
