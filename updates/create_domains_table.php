<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateDomainsTable extends Migration
{
    public function up()
    {
        Schema::create('aero_clouds_domains', function($table)
        {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('domain_name');
            $table->unsignedBigInteger('extension_id');
            $table->unsignedBigInteger('provider_id');
            $table->date('registration_date');
            $table->date('expiration_date');
            $table->boolean('auto_renew')->default(true);
            $table->enum('status', ['pending', 'active', 'expired', 'suspended', 'cancelled', 'transferred'])->default('pending');
            $table->text('nameservers')->nullable(); // JSON array
            $table->text('dns_records')->nullable(); // JSON array
            $table->boolean('is_locked')->default(true);
            $table->string('epp_code')->nullable();
            $table->boolean('whois_privacy')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('order_id');
            $table->index('extension_id');
            $table->index('provider_id');
            $table->index('status');
            $table->index('expiration_date');

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('aero_clouds_orders')->onDelete('set null');
            $table->foreign('extension_id')->references('id')->on('aero_clouds_domain_extensions')->onDelete('restrict');
            $table->foreign('provider_id')->references('id')->on('aero_clouds_domain_providers')->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::dropIfExists('aero_clouds_domains');
    }
}
