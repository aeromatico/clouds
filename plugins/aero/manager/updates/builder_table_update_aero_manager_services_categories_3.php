<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerServicesCategories3 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_services_categories', function($table)
        {
            $table->string('domain', 191)->default(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_services_categories', function($table)
        {
            $table->string('domain', 191)->default('boliviahost.com')->change();
        });
    }
}
