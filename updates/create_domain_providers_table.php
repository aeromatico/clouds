<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateDomainProvidersTable extends Migration
{
    public function up()
    {
        Schema::create('aero_clouds_domain_providers', function($table)
        {
            $table->id();

            // Basic Information
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('provider_type')->index(); // namecheap, godaddy, cloudflare, nic_bo, custom

            // API Configuration
            $table->string('api_url')->nullable();
            $table->text('api_key')->nullable(); // Encrypted
            $table->text('api_secret')->nullable(); // Encrypted
            $table->string('api_username')->nullable();
            $table->text('api_password')->nullable(); // Encrypted

            // Settings
            $table->boolean('sandbox_mode')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('priority')->default(0); // Higher priority = preferred provider

            // Additional Configuration (JSON)
            $table->text('settings')->nullable(); // JSON field for provider-specific settings

            // Notes
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('aero_clouds_domain_providers');
    }
}
