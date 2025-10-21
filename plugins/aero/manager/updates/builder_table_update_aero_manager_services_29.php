<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerServices29 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_services', function($table)
        {
            $table->string('faqs_title');
            $table->string('faqs_subtitle');
            $table->text('faqs_description');
            $table->string('pricing_title');
            $table->string('pricing_subtitle');
            $table->text('pricing_descripion');
            $table->string('plans_title');
            $table->string('plans_subtitle');
            $table->text('plans_description');
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('deleted_at');
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_services', function($table)
        {
            $table->dropColumn('faqs_title');
            $table->dropColumn('faqs_subtitle');
            $table->dropColumn('faqs_description');
            $table->dropColumn('pricing_title');
            $table->dropColumn('pricing_subtitle');
            $table->dropColumn('pricing_descripion');
            $table->dropColumn('plans_title');
            $table->dropColumn('plans_subtitle');
            $table->dropColumn('plans_description');
            $table->timestamp('created_at')->nullable()->default('NULL');
            $table->timestamp('updated_at')->nullable()->default('NULL');
            $table->timestamp('deleted_at')->nullable()->default('NULL');
        });
    }
}
