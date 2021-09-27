<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvestorTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('investor_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('transaction_type')->default(0);
            $table->decimal('totalAmount','10','2')->default(0);
            $table->unsignedBigInteger('investor_id')->default(0)->index();
            $table->unsignedBigInteger('user_id')->default(0)->index();
            $table->unsignedBigInteger('company_id')->default(0)->index();
            $table->unsignedBigInteger('bank_id')->default(0)->index();
            $table->string('accountNumber')->nullable();
            $table->date('transferDate')->default(date('Y-m-d'));
            $table->string('payment_type')->nullable();
            $table->string('referenceNumber')->nullable();
            $table->string('PersonName')->nullable();
            $table->string('receiptNumber')->nullable();
            $table->text('Description')->nullable();
            $table->text('updateDescription')->nullable();
            $table->date('paymentReceiveDate')->default(date('Y-m-d'));
            $table->date('createdDate')->default(date('Y-m-d'));
            $table->boolean('isActive')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('investor_transactions');
    }
}
