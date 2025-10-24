<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * AddMissingColumnsToPaymentGatewaysTable Migration
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
        Schema::table('aero_clouds_payment_gateways', function(Blueprint $table) {
            // Rename gateway_code to slug
            $table->renameColumn('gateway_code', 'slug');

            // Add missing columns
            $table->string('type')->default('manual')->index()->after('slug');
            $table->text('description')->nullable()->after('type');
            $table->boolean('is_default')->default(false)->index()->after('is_active');
            $table->integer('sort_order')->default(0)->after('is_default');

            // Rename config to configuration
            $table->renameColumn('config', 'configuration');

            // Add new columns
            $table->json('supported_currencies')->nullable()->after('configuration');
            $table->string('transaction_fee_type')->nullable()->after('supported_currencies');
            $table->decimal('transaction_fee_amount', 10, 2)->nullable()->after('transaction_fee_type');
            $table->decimal('transaction_fee_percentage', 5, 2)->nullable()->after('transaction_fee_amount');
            $table->decimal('min_amount', 10, 2)->nullable()->after('transaction_fee_percentage');
            $table->decimal('max_amount', 10, 2)->nullable()->after('min_amount');
            $table->string('logo')->nullable()->after('max_amount');
            $table->text('instructions')->nullable()->after('logo');
        });
    }

    /**
     * down reverses the migration
     */
    public function down()
    {
        Schema::table('aero_clouds_payment_gateways', function(Blueprint $table) {
            // Drop added columns
            $table->dropColumn([
                'type',
                'description',
                'is_default',
                'sort_order',
                'supported_currencies',
                'transaction_fee_type',
                'transaction_fee_amount',
                'transaction_fee_percentage',
                'min_amount',
                'max_amount',
                'logo',
                'instructions'
            ]);

            // Rename columns back
            $table->renameColumn('slug', 'gateway_code');
            $table->renameColumn('configuration', 'config');
        });
    }
};
