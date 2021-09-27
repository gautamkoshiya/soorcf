<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLpoDetailsTable extends Migration
{
    public function up()
    {
        Schema::create('lpo_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('lpo_id')->default(0);
            $table->unsignedBigInteger('user_id')->default(0);
            $table->unsignedBigInteger('company_id')->default(0);
            $table->unsignedBigInteger('product_id')->default(0);
            $table->text('Description')->nullable();
            $table->unsignedBigInteger('unit_id')->default(0);
            $table->decimal('Quantity',10,2)->default('0');
            $table->decimal('Price',10,2)->default('0');
            $table->decimal('rowTotal',10,2)->default('0');
            $table->decimal('VAT',10,2)->default('0');
            $table->decimal('rowVatAmount',10,2)->default('0');
            $table->decimal('rowSubTotal',10,2)->default('0');
            $table->date('createdDate')->default(date('Y-m-d'));
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lpo_details');
    }
}