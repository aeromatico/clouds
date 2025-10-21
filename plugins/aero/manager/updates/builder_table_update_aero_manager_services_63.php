<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerServices63 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_services', function($table)
        {
            $table->string('comparison_title', 191);
            $table->string('comparison_subtitle', 191);
            $table->renameColumn('comparative_on', 'comparison_on');
            $table->dropColumn('comparative_title');
            $table->dropColumn('comparative_subtitle');
            $table->dropColumn('comparative');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_services', function($table)
        {
            $table->dropColumn('comparison_title');
            $table->dropColumn('comparison_subtitle');
            $table->renameColumn('comparison_on', 'comparative_on');
            $table->string('comparative_title', 191);
            $table->string('comparative_subtitle', 191);
            $table->text('comparative');
        });
    }
}
