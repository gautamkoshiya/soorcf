<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdvanceBookingShortageColumnToSaleDetailsTable extends Migration
{
    public function up()
    {
        Schema::table('sale_details', function (Blueprint $table) {
            $table->text('booking_shortage')->nullable();
        });
    }

    public function down()
    {
        Schema::table('sale_details', function (Blueprint $table) {
            $table->dropColumn('booking_shortage');
        });
    }
}
