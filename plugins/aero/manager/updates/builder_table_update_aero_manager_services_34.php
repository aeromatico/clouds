<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerServices34 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_services', function($table)
        {
            $table->string('gallery_title');
            $table->string('gallery_subtitle');
            $table->text('gallery_description');
            $table->string('button1_text', 191)->default('null')->change();
            $table->string('button1_link', 191)->default('null')->change();
            $table->string('button2_text', 191)->default('null')->change();
            $table->string('button2_link', 191)->default('null')->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_services', function($table)
        {
            $table->dropColumn('gallery_title');
            $table->dropColumn('gallery_subtitle');
            $table->dropColumn('gallery_description');
            $table->string('button1_text', 191)->default('\'null\'')->change();
            $table->string('button1_link', 191)->default('\'null\'')->change();
            $table->string('button2_text', 191)->default('\'null\'')->change();
            $table->string('button2_link', 191)->default('\'null\'')->change();
        });
    }
}
