<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * UpdateCloudsTableColumnNames Migration
 *
 * @link https://docs.octobercms.com/4.x/extend/database/structure.html
 */
return new class extends Migration
{
    /**
     * up builds the migration
     */
    public function up()
    {
        Schema::table('aero_clouds_clouds', function(Blueprint $table) {
            // Rename columns to match the model
            $table->renameColumn('server_name', 'name');
            $table->renameColumn('panel_username', 'panel_user');
            $table->renameColumn('renewal_date', 'last_renewal_date');
        });

        Schema::table('aero_clouds_clouds', function(Blueprint $table) {
            // Add missing columns
            $table->timestamp('suspension_date')->nullable()->after('expiration_date');
            $table->timestamp('termination_date')->nullable()->after('suspension_date');
            $table->text('suspension_reason')->nullable()->after('notes');
            $table->text('termination_reason')->nullable()->after('suspension_reason');
        });
    }

    /**
     * down reverses the migration
     */
    public function down()
    {
        Schema::table('aero_clouds_clouds', function(Blueprint $table) {
            // Drop added columns
            $table->dropColumn(['suspension_date', 'termination_date', 'suspension_reason', 'termination_reason']);
        });

        Schema::table('aero_clouds_clouds', function(Blueprint $table) {
            // Rename columns back
            $table->renameColumn('name', 'server_name');
            $table->renameColumn('panel_user', 'panel_username');
            $table->renameColumn('last_renewal_date', 'renewal_date');
        });
    }
};
