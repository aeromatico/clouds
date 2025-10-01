<?php namespace Aero\Clouds\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateInvoicesTable extends Migration
{
    public function up()
    {
        Schema::create('aero_clouds_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('invoice_number')->unique();
            $table->timestamp('invoice_date');
            $table->timestamp('due_date');
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled', 'refunded'])->default('draft');
            $table->json('items');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->text('notes')->nullable();
            $table->enum('payment_method', ['cash', 'credit_card', 'debit_card', 'bank_transfer', 'paypal', 'stripe', 'other'])->nullable();
            $table->timestamp('payment_date')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('user_id');
            $table->index('status');
            $table->index('invoice_date');
            $table->index('due_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('aero_clouds_invoices');
    }
}
