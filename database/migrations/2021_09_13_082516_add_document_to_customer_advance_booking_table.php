<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDocumentToCustomerAdvanceBookingTable extends Migration
{
    public function up()
    {
        Schema::table('customer_advance_bookings', function (Blueprint $table) {
            $table->char('document', 200)->nullable();
        });
    }

    public function down()
    {
        Schema::table('customer_advance_bookings', function (Blueprint $table) {
            $table->dropColumn('document');
        });
    }
}
