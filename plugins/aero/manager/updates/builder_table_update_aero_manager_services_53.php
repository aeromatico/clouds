<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerServices53 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_services', function($table)
        {
            $table->smallInteger('features_theme')->default(1);
            $table->string('plans_title', 191)->default('null')->change();
            $table->string('plans_subtitle', 191)->default('null')->change();
            $table->text('plans_description')->default('null')->change();
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
            $table->dropColumn('features_theme');
            $table->string('plans_title', 191)->default('\'null\'')->change();
            $table->string('plans_subtitle', 191)->default('\'null\'')->change();
            $table->text('plans_description')->default('NULL')->change();
            $table->string('button1_text', 191)->default('\'null\'')->change();
            $table->string('button1_link', 191)->default('\'null\'')->change();
            $table->string('button2_text', 191)->default('\'null\'')->change();
            $table->string('button2_link', 191)->default('\'null\'')->change();
        });
    }
}
