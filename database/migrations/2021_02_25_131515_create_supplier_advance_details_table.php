<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplierAdvanceDetailsTable extends Migration
{
    public function up()
    {
        Schema::create('supplier_advance_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('amountPaid','10','2')->default(0);
            $table->unsignedBigInteger('supplier_advances_id')->default(0);
            $table->unsignedBigInteger('user_id')->default(0);
            $table->unsignedBigInteger('company_id')->default(0);
            $table->unsignedBigInteger('purchase_id')->default(0)->index();
            $table->text('Description')->nullable();
            $table->text('updateDescription')->nullable();
            $table->date('advanceReceiveDetailDate')->default(date('Y-m-d'));
            $table->date('createdDate')->default(date('Y-m-d'));
            $table->boolean('isActive')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('supplier_advance_details');
    }
}
