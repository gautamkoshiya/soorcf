<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaxInvoicePaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('tax_invoice_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tax_invoice_id')->default(0)->index();
            $table->date('PaymentDate')->default(date('Y-m-d'));
            $table->decimal('PaymentAmount','10','2')->default(0);
            $table->text('payment_type')->nullable();
            $table->text('Description')->nullable();
            $table->unsignedBigInteger('user_id')->default(0)->index();
            $table->unsignedBigInteger('company_id')->default(0)->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tax_invoice_payments');
    }
}
