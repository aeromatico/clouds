<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerPortal42 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_portal', function($table)
        {
            $table->string('drawer_class')->nullable();
            $table->string('domain', 191)->nullable()->change();
            $table->text('customers_project')->nullable()->change();
            $table->text('faqs')->nullable()->change();
            $table->text('actionboxes')->nullable()->change();
            $table->smallInteger('header_theme')->nullable()->change();
            $table->text('features_sections')->nullable()->change();
            $table->string('header_class', 191)->nullable()->change();
            $table->smallInteger('home_theme')->nullable()->change();
            $table->text('home_class')->nullable()->change();
            $table->smallInteger('footer_theme')->nullable()->change();
            $table->text('announcements')->nullable()->change();
            $table->string('header_navbar_class', 191)->nullable()->change();
            $table->string('accent', 30)->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_portal', function($table)
        {
            $table->dropColumn('drawer_class');
            $table->string('domain', 191)->nullable(false)->change();
            $table->text('customers_project')->nullable(false)->change();
            $table->text('faqs')->nullable(false)->change();
            $table->text('actionboxes')->nullable(false)->change();
            $table->smallInteger('header_theme')->nullable(false)->change();
            $table->text('features_sections')->nullable(false)->change();
            $table->string('header_class', 191)->nullable(false)->change();
            $table->smallInteger('home_theme')->nullable(false)->change();
            $table->text('home_class')->nullable(false)->change();
            $table->smallInteger('footer_theme')->nullable(false)->change();
            $table->text('announcements')->nullable(false)->change();
            $table->string('header_navbar_class', 191)->nullable(false)->change();
            $table->string('accent', 30)->nullable(false)->change();
        });
    }
}
