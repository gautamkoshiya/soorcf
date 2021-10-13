<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseDetailsTable extends Migration
{
    public function up()
    {
        Schema::create('purchase_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('purchase_id')->default(0);
            $table->unsignedBigInteger('user_id')->default(0);
            $table->unsignedBigInteger('company_id')->default(0);
            $table->unsignedBigInteger('product_id')->default(0);
            $table->unsignedBigInteger('unit_id')->default(0);
            $table->date('createdDate')->default(date('Y-m-d'));
            $table->string('PadNumber')->nullable();
            $table->text('Description')->nullable();
            $table->decimal('Quantity',10,2)->default('0');
            $table->decimal('Price',10,2)->default('0');
            $table->decimal('rowTotal',10,2)->default('0');
            $table->decimal('Cgst',10,2)->default('0');
            $table->decimal('rowCgstAmount',10,2)->default('0');
            $table->decimal('Sgst',10,2)->default('0');
            $table->decimal('rowSgstAmount',10,2)->default('0');
            $table->decimal('Igst',10,2)->default('0');
            $table->decimal('rowIgstAmount',10,2)->default('0');
            $table->decimal('rowSubTotal',10,2)->default('0');
            $table->boolean('isActive')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_details');
    }
}
