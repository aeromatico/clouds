<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerFeatures extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_features', function($table)
        {
            $table->string('title');
            $table->string('subtitle');
            $table->dropColumn('feature');
            $table->dropColumn('description');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_features', function($table)
        {
            $table->dropColumn('title');
            $table->dropColumn('subtitle');
            $table->string('feature', 191);
            $table->text('description');
        });
    }
}
