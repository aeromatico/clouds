<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerServices52 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_services', function($table)
        {
            $table->string('plans_title', 191)->default('null')->change();
            $table->string('plans_subtitle', 191)->default('null')->change();
            $table->text('plans_description')->default('null')->change();
            $table->string('button1_text', 191)->default('null')->change();
            $table->string('button1_link', 191)->default('null')->change();
            $table->string('button2_text', 191)->default('null')->change();
            $table->string('button2_link', 191)->default('null')->change();
            $table->dropColumn('features_theme');
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('deleted_at');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_services', function($table)
        {
            $table->string('plans_title', 191)->default('\'null\'')->change();
            $table->string('plans_subtitle', 191)->default('\'null\'')->change();
            $table->text('plans_description')->default('NULL')->change();
            $table->string('button1_text', 191)->default('\'null\'')->change();
            $table->string('button1_link', 191)->default('\'null\'')->change();
            $table->string('button2_text', 191)->default('\'null\'')->change();
            $table->string('button2_link', 191)->default('\'null\'')->change();
            $table->smallInteger('features_theme');
            $table->timestamp('created_at')->nullable()->default('NULL');
            $table->timestamp('updated_at')->nullable()->default('NULL');
            $table->timestamp('deleted_at')->nullable()->default('NULL');
        });
    }
}
