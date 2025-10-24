<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateDomainExtensionsTable extends Migration
{
    public function up()
    {
        Schema::create('aero_clouds_domain_extensions', function($table)
        {
            $table->id();

            // Relationships
            $table->unsignedBigInteger('provider_id')->index();

            // Extension Information
            $table->string('tld', 50)->index(); // .com, .net, .bo, etc.
            $table->string('name'); // Display name
            $table->string('category')->nullable()->index(); // generic, country, sponsored, etc.

            // Pricing (in local currency - Bs)
            $table->decimal('registration_price', 10, 2); // Price for first year registration
            $table->decimal('renewal_price', 10, 2); // Yearly renewal price
            $table->decimal('transfer_price', 10, 2)->nullable(); // Transfer price
            $table->decimal('redemption_price', 10, 2)->nullable(); // Redemption price (after expiry)

            // Registration Requirements
            $table->integer('min_years')->default(1); // Minimum registration years
            $table->integer('max_years')->default(10); // Maximum registration years

            // Availability
            $table->boolean('is_available')->default(true)->index();
            $table->boolean('is_featured')->default(false)->index(); // Featured/Popular TLDs

            // Extra Attributes (for special TLDs that need additional info)
            $table->boolean('requires_extra_attributes')->default(false);
            $table->text('extra_attributes')->nullable(); // JSON field for required attributes

            // WHOIS Privacy
            $table->boolean('whois_privacy_available')->default(true);
            $table->decimal('whois_privacy_price', 10, 2)->nullable(); // Annual WHOIS privacy price

            // Notes
            $table->text('notes')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('provider_id')
                ->references('id')
                ->on('aero_clouds_domain_providers')
                ->onDelete('cascade');

            // Unique constraint
            $table->unique(['provider_id', 'tld']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('aero_clouds_domain_extensions');
    }
}
