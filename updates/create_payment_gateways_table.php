<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreatePaymentGatewaysTable extends Migration
{
    public function up()
    {
        Schema::create('aero_clouds_payment_gateways', function($table)
        {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->index(); // stripe, paypal, crypto, bank_transfer, qr_code, manual, other
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_default')->default(false)->index();
            $table->integer('sort_order')->default(0);

            // Configuration (JSON field for gateway-specific settings)
            // Examples: API keys, webhook URLs, merchant IDs, etc.
            $table->json('configuration')->nullable();

            // Supported currencies (array of currency codes)
            $table->json('supported_currencies')->nullable();

            // Transaction fees
            $table->string('transaction_fee_type')->nullable(); // fixed, percentage, both
            $table->decimal('transaction_fee_amount', 10, 2)->nullable();
            $table->decimal('transaction_fee_percentage', 5, 2)->nullable();

            // Amount limits
            $table->decimal('min_amount', 10, 2)->nullable();
            $table->decimal('max_amount', 10, 2)->nullable();

            // Logo/icon
            $table->string('logo')->nullable();

            // Instructions for manual payment methods
            $table->text('instructions')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('aero_clouds_payment_gateways');
    }
}
