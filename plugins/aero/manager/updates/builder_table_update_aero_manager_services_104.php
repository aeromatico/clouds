<?php namespace Aero\Manager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAeroManagerServices104 extends Migration
{
    public function up()
    {
        Schema::table('aero_manager_services', function($table)
        {
            $table->boolean('docs_on')->nullable();
            $table->string('name', 191)->nullable()->change();
            $table->string('slug', 191)->nullable()->change();
            $table->text('short_description')->nullable()->change();
            $table->text('description')->nullable()->change();
            $table->boolean('public')->nullable()->change();
            $table->text('menu_description')->nullable()->change();
            $table->smallInteger('order')->nullable()->change();
            $table->boolean('gallery_on')->nullable()->change();
            $table->boolean('domain_search_on')->nullable()->change();
            $table->boolean('templates_on')->nullable()->change();
            $table->text('addons')->nullable()->change();
            $table->string('features_title', 191)->nullable()->change();
            $table->string('features_subtitle', 191)->nullable()->change();
            $table->text('features_description')->nullable()->change();
            $table->string('faqs_title', 191)->nullable()->change();
            $table->string('faqs_subtitle', 191)->nullable()->change();
            $table->text('faqs_description')->nullable()->change();
            $table->string('pricing_title', 191)->nullable()->change();
            $table->string('pricing_subtitle', 191)->nullable()->change();
            $table->text('pricing_descripion')->nullable()->change();
            $table->boolean('features_on')->nullable()->change();
            $table->boolean('pricing_on')->nullable()->change();
            $table->boolean('addons_on')->nullable()->change();
            $table->boolean('faqs_on')->nullable()->change();
            $table->boolean('plans_on')->nullable()->change();
            $table->string('gallery_title', 191)->nullable()->change();
            $table->string('gallery_subtitle', 191)->nullable()->change();
            $table->text('gallery_description')->nullable()->change();
            $table->boolean('features_special_on')->nullable()->change();
            $table->string('features_special_title', 191)->nullable()->change();
            $table->string('features_special_subtitle', 191)->nullable()->change();
            $table->text('features_special_description')->nullable()->change();
            $table->boolean('comparison_on')->nullable()->change();
            $table->smallInteger('features_theme')->nullable()->change();
            $table->boolean('enterprise_on')->nullable()->change();
            $table->boolean('landscape_plans_on')->nullable()->change();
            $table->text('comparison')->nullable()->change();
            $table->string('comparison_title', 191)->nullable()->change();
            $table->string('comparison_subtitle', 191)->nullable()->change();
            $table->string('domain', 191)->nullable()->change();
            $table->text('presentation')->nullable()->change();
            $table->string('type', 191)->nullable()->change();
            $table->string('class', 191)->nullable()->change();
            $table->text('video_embed')->nullable()->change();
            $table->text('software')->nullable()->change();
            $table->smallInteger('gallery_theme')->nullable()->change();
            $table->text('presentation_mobile')->nullable()->change();
            $table->text('reviews')->nullable()->change();
            $table->text('gallery_complex')->nullable()->change();
            $table->boolean('dealers_on')->nullable()->change();
            $table->smallInteger('features_special_theme')->nullable()->change();
            $table->smallInteger('header_theme')->nullable()->change();
            $table->boolean('trial_on')->nullable()->change();
            $table->smallInteger('pricing_theme')->nullable()->change();
            $table->string('class_top', 191)->nullable()->change();
            $table->boolean('dark_on')->nullable()->change();
            $table->text('features_sections')->nullable()->change();
            $table->string('theme', 10)->nullable()->change();
            $table->text('appareance_services')->nullable()->change();
            $table->smallInteger('footer_theme')->nullable()->change();
            $table->smallInteger('docs_category')->nullable()->change();
            $table->smallInteger('faqs_themes')->nullable()->change();
            $table->string('appareance_accent', 191)->nullable()->change();
            $table->text('ia_train')->nullable()->change();
            $table->text('icon')->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('aero_manager_services', function($table)
        {
            $table->dropColumn('docs_on');
            $table->string('name', 191)->nullable(false)->change();
            $table->string('slug', 191)->nullable(false)->change();
            $table->text('short_description')->nullable(false)->change();
            $table->text('description')->nullable(false)->change();
            $table->boolean('public')->nullable(false)->change();
            $table->text('menu_description')->nullable(false)->change();
            $table->smallInteger('order')->nullable(false)->change();
            $table->boolean('gallery_on')->nullable(false)->change();
            $table->boolean('domain_search_on')->nullable(false)->change();
            $table->boolean('templates_on')->nullable(false)->change();
            $table->text('addons')->nullable(false)->change();
            $table->string('features_title', 191)->nullable(false)->change();
            $table->string('features_subtitle', 191)->nullable(false)->change();
            $table->text('features_description')->nullable(false)->change();
            $table->string('faqs_title', 191)->nullable(false)->change();
            $table->string('faqs_subtitle', 191)->nullable(false)->change();
            $table->text('faqs_description')->nullable(false)->change();
            $table->string('pricing_title', 191)->nullable(false)->change();
            $table->string('pricing_subtitle', 191)->nullable(false)->change();
            $table->text('pricing_descripion')->nullable(false)->change();
            $table->boolean('features_on')->nullable(false)->change();
            $table->boolean('pricing_on')->nullable(false)->change();
            $table->boolean('addons_on')->nullable(false)->change();
            $table->boolean('faqs_on')->nullable(false)->change();
            $table->boolean('plans_on')->nullable(false)->change();
            $table->string('gallery_title', 191)->nullable(false)->change();
            $table->string('gallery_subtitle', 191)->nullable(false)->change();
            $table->text('gallery_description')->nullable(false)->change();
            $table->boolean('features_special_on')->nullable(false)->change();
            $table->string('features_special_title', 191)->nullable(false)->change();
            $table->string('features_special_subtitle', 191)->nullable(false)->change();
            $table->text('features_special_description')->nullable(false)->change();
            $table->boolean('comparison_on')->nullable(false)->change();
            $table->smallInteger('features_theme')->nullable(false)->change();
            $table->boolean('enterprise_on')->nullable(false)->change();
            $table->boolean('landscape_plans_on')->nullable(false)->change();
            $table->text('comparison')->nullable(false)->change();
            $table->string('comparison_title', 191)->nullable(false)->change();
            $table->string('comparison_subtitle', 191)->nullable(false)->change();
            $table->string('domain', 191)->nullable(false)->change();
            $table->text('presentation')->nullable(false)->change();
            $table->string('type', 191)->nullable(false)->change();
            $table->string('class', 191)->nullable(false)->change();
            $table->text('video_embed')->nullable(false)->change();
            $table->text('software')->nullable(false)->change();
            $table->smallInteger('gallery_theme')->nullable(false)->change();
            $table->text('presentation_mobile')->nullable(false)->change();
            $table->text('reviews')->nullable(false)->change();
            $table->text('gallery_complex')->nullable(false)->change();
            $table->boolean('dealers_on')->nullable(false)->change();
            $table->smallInteger('features_special_theme')->nullable(false)->change();
            $table->smallInteger('header_theme')->nullable(false)->change();
            $table->boolean('trial_on')->nullable(false)->change();
            $table->smallInteger('pricing_theme')->nullable(false)->change();
            $table->string('class_top', 191)->nullable(false)->change();
            $table->boolean('dark_on')->nullable(false)->change();
            $table->text('features_sections')->nullable(false)->change();
            $table->string('theme', 10)->nullable(false)->change();
            $table->text('appareance_services')->nullable(false)->change();
            $table->smallInteger('footer_theme')->nullable(false)->change();
            $table->smallInteger('docs_category')->nullable(false)->change();
            $table->smallInteger('faqs_themes')->nullable(false)->change();
            $table->string('appareance_accent', 191)->nullable(false)->change();
            $table->text('ia_train')->nullable(false)->change();
            $table->text('icon')->nullable(false)->change();
        });
    }
}
