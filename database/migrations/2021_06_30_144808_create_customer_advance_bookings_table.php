<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerAdvanceBookingsTable extends Migration
{
    public function up()
    {
        Schema::create('customer_advance_bookings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('code')->nullable();
            $table->decimal('totalQuantity','10','2')->default(0);
            $table->decimal('consumedQuantity','10','2')->default(0);
            $table->decimal('remainingQuantity','10','2')->default(0);
            $table->decimal('Rate','10','2')->default(0);
            $table->unsignedBigInteger('user_id')->default(0)->index();
            $table->unsignedBigInteger('company_id')->default(0)->index();
            $table->unsignedBigInteger('customer_id')->default(0)->index();
            $table->date('BookingDate')->default(date('Y-m-d'));
            $table->text('Description')->nullable();
            $table->boolean('status')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_advance_bookings');
    }
}
