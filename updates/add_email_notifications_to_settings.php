<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class AddEmailNotificationsToSettings extends Migration
{
    public function up()
    {
        // First, create the table if it doesn't exist
        if (!Schema::hasTable('aero_clouds_settings')) {
            Schema::create('aero_clouds_settings', function (Blueprint $table) {
                $table->id();
                $table->string('domain')->index();

                // SEO Fields
                $table->string('site_name')->nullable();
                $table->string('site_description', 500)->nullable();
                $table->string('meta_keywords', 500)->nullable();
                $table->string('meta_author')->nullable();
                $table->string('og_type', 50)->default('website');
                $table->string('twitter_card_type', 50)->default('summary_large_image');
                $table->string('google_analytics_id', 50)->nullable();
                $table->string('google_tag_manager_id', 50)->nullable();

                // PWA Fields
                $table->boolean('pwa_enabled')->default(true);
                $table->string('pwa_name')->nullable();
                $table->string('pwa_short_name', 50)->nullable();
                $table->string('pwa_description', 500)->nullable();
                $table->string('pwa_theme_color', 7)->default('#0ea5e9');
                $table->string('pwa_background_color', 7)->default('#ffffff');
                $table->boolean('pwa_show_install_prompt')->default(true);
                $table->integer('pwa_install_prompt_delay')->default(3000);

                // Email Notification Fields
                $table->boolean('email_notifications_enabled')->default(true);
                $table->text('admin_emails')->nullable();

                $table->timestamps();

                // Unique constraint: one settings record per domain
                $table->unique('domain');
            });
        } else {
            // If table exists, just add the new columns
            Schema::table('aero_clouds_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('aero_clouds_settings', 'email_notifications_enabled')) {
                    $table->boolean('email_notifications_enabled')->default(true)->after('pwa_install_prompt_delay');
                }
                if (!Schema::hasColumn('aero_clouds_settings', 'admin_emails')) {
                    $table->text('admin_emails')->nullable()->after('email_notifications_enabled');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('aero_clouds_settings')) {
            Schema::table('aero_clouds_settings', function (Blueprint $table) {
                $columns = ['email_notifications_enabled', 'admin_emails'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('aero_clouds_settings', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
}
