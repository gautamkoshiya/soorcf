<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerAdvanceBookingDetailsTable extends Migration
{
    public function up()
    {
        Schema::create('customer_advance_booking_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('Quantity','10','2')->default(0);
            $table->unsignedBigInteger('user_id')->default(0)->index();
            $table->unsignedBigInteger('company_id')->default(0)->index();
            $table->unsignedBigInteger('customer_id')->default(0)->index();
            $table->unsignedBigInteger('booking_id')->default(0)->index();
            $table->unsignedBigInteger('sale_id')->default(0)->index();
            $table->date('BookingDate')->default(date('Y-m-d'));
            $table->text('PadNumber')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_advance_booking_details');
    }
}
