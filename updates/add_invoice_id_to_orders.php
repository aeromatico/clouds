<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class AddInvoiceIdToOrders extends Migration
{
    public function up()
    {
        Schema::table('aero_clouds_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('invoice_id')->nullable()->after('user_id');
            $table->foreign('invoice_id')->references('id')->on('aero_clouds_invoices')->onDelete('set null');
            $table->index('invoice_id');
        });
    }

    public function down()
    {
        Schema::table('aero_clouds_orders', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropColumn('invoice_id');
        });
    }
}
