<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryNotesTable extends Migration
{
    public function up()
    {
        Schema::create('delivery_notes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('DoNumber')->nullable();
            $table->unsignedBigInteger('user_id')->default('0')->index();
            $table->unsignedBigInteger('company_id')->default('0')->index();
            $table->unsignedBigInteger('project_id')->default('0')->index();
            $table->unsignedBigInteger('customer_id')->default(0)->index();
            $table->unsignedBigInteger('product_id')->default(0)->index();
            $table->unsignedBigInteger('unit_id')->default(0)->index();
            $table->decimal('Quantity',10,2)->default('0');
            $table->string('OrderReference')->nullable();
            $table->text('Description')->nullable();
            $table->date('createdDate')->default(date('Y-m-d'));
            $table->boolean('isActive')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_notes');
    }
}
