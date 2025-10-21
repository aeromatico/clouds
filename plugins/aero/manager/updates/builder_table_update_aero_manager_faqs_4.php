<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerFaqs4 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_faqs', function($table)
        {
            $table->text('buttons')->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_faqs', function($table)
        {
            $table->text('buttons')->nullable(false)->change();
        });
    }
}
