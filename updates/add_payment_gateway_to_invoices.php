<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddPaymentGatewayToInvoices extends Migration
{
    public function up()
    {
        Schema::table('aero_clouds_invoices', function($table)
        {
            $table->unsignedBigInteger('payment_gateway_id')->nullable()->after('payment_method');
            $table->string('transaction_id')->nullable()->after('payment_gateway_id');
            $table->decimal('transaction_fee', 10, 2)->nullable()->after('transaction_id');

            $table->foreign('payment_gateway_id')
                ->references('id')
                ->on('aero_clouds_payment_gateways')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('aero_clouds_invoices', function($table)
        {
            $table->dropForeign(['payment_gateway_id']);
            $table->dropColumn(['payment_gateway_id', 'transaction_id', 'transaction_fee']);
        });
    }
}
