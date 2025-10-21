<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerServices38 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_services', function($table)
        {
            $table->string('features_special_title');
            $table->string('features_special_subtitle');
            $table->text('features_special_description');
            $table->string('button1_text', 191)->default('null')->change();
            $table->string('button1_link', 191)->default('null')->change();
            $table->string('button2_text', 191)->default('null')->change();
            $table->string('button2_link', 191)->default('null')->change();
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('deleted_at');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_services', function($table)
        {
            $table->dropColumn('features_special_title');
            $table->dropColumn('features_special_subtitle');
            $table->dropColumn('features_special_description');
            $table->string('button1_text', 191)->default('\'null\'')->change();
            $table->string('button1_link', 191)->default('\'null\'')->change();
            $table->string('button2_text', 191)->default('\'null\'')->change();
            $table->string('button2_link', 191)->default('\'null\'')->change();
            $table->timestamp('created_at')->nullable()->default('NULL');
            $table->timestamp('updated_at')->nullable()->default('NULL');
            $table->timestamp('deleted_at')->nullable()->default('NULL');
        });
    }
}
