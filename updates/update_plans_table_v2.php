<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class UpdatePlansTableV2 extends Migration
{
    public function up()
    {
        Schema::table('aero_clouds_plans', function (Blueprint $table) {
            // Remove old pricing columns and service relationship
            $table->dropForeign(['service_id']);
            $table->dropIndex(['service_id', 'is_active']);
            $table->dropColumn([
                'service_id',
                'price',
                'currency',
                'billing_cycle',
                'setup_fee'
            ]);

            // Add new columns
            $table->boolean('promo')->default(false)->after('is_featured');
            $table->boolean('free_domain')->default(false)->after('promo');
            $table->boolean('ssh')->default(false)->after('free_domain');
            $table->boolean('ssl')->default(false)->after('ssh');
            $table->boolean('dedicated_ip')->default(false)->after('ssl');
            $table->json('pricing')->nullable()->after('dedicated_ip');
        });
    }

    public function down()
    {
        Schema::table('aero_clouds_plans', function (Blueprint $table) {
            // Remove new columns
            $table->dropColumn([
                'promo',
                'free_domain',
                'ssh',
                'ssl',
                'dedicated_ip',
                'pricing'
            ]);

            // Add back old columns
            $table->unsignedBigInteger('service_id')->after('id');
            $table->decimal('price', 10, 2)->after('description');
            $table->string('currency', 3)->default('BOB')->after('price');
            $table->enum('billing_cycle', [
                'monthly',
                'quarterly',
                'semi_annually',
                'annually',
                'biennially',
                'triennially'
            ])->default('monthly')->after('currency');
            $table->decimal('setup_fee', 10, 2)->nullable()->after('billing_cycle');

            // Restore foreign key
            $table->foreign('service_id')->references('id')->on('aero_clouds_services')->onDelete('cascade');
            $table->index(['service_id', 'is_active']);
        });
    }
}