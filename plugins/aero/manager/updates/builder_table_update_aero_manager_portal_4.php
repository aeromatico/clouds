<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerPortal4 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_portal', function($table)
        {
            $table->string('header_title');
            $table->string('header_subtitle');
            $table->text('header_description');
            $table->string('header_button1_text');
            $table->string('header_button1_link');
            $table->string('header_button2_text');
            $table->string('header_button2_link');
            $table->renameColumn('special_features', 'features_special');
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('deleted_at');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_portal', function($table)
        {
            $table->dropColumn('header_title');
            $table->dropColumn('header_subtitle');
            $table->dropColumn('header_description');
            $table->dropColumn('header_button1_text');
            $table->dropColumn('header_button1_link');
            $table->dropColumn('header_button2_text');
            $table->dropColumn('header_button2_link');
            $table->renameColumn('features_special', 'special_features');
            $table->timestamp('created_at')->nullable()->default('NULL');
            $table->timestamp('updated_at')->nullable()->default('NULL');
            $table->timestamp('deleted_at')->nullable()->default('NULL');
        });
    }
}
