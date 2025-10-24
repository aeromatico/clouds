<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddIconHtmlToAddons extends Migration
{
    public function up()
    {
        // Drop icon_html if it exists from previous migration
        if (Schema::hasColumn('aero_clouds_addons', 'icon_html')) {
            Schema::table('aero_clouds_addons', function($table) {
                $table->dropColumn('icon_html');
            });
        }

        // Create or modify icon column to be text (to accept HTML)
        if (!Schema::hasColumn('aero_clouds_addons', 'icon')) {
            Schema::table('aero_clouds_addons', function($table) {
                $table->text('icon')->nullable()->after('sort_order');
            });
        } else {
            // Change existing icon column from string to text
            \DB::statement('ALTER TABLE aero_clouds_addons MODIFY COLUMN icon TEXT NULL');
        }
    }

    public function down()
    {
        // Revert icon back to string type
        if (Schema::hasColumn('aero_clouds_addons', 'icon')) {
            \DB::statement('ALTER TABLE aero_clouds_addons MODIFY COLUMN icon VARCHAR(255) NULL');
        }
    }
}
