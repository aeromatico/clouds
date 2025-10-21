<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerContents extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_contents', function($table)
        {
            $table->text('content_html');
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_contents', function($table)
        {
            $table->dropColumn('content_html');
            $table->timestamp('created_at')->nullable()->default('NULL');
            $table->timestamp('updated_at')->nullable()->default('NULL');
        });
    }
}
