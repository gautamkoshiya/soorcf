<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemStockEntryToSaleDetailsTable extends Migration
{
    public function up()
    {
        Schema::table('sale_details', function (Blueprint $table) {
            $table->boolean('rem_stock_entry')->default(0);
        });
    }

    public function down()
    {
        Schema::table('sale_details', function (Blueprint $table) {
            $table->dropColumn('rem_stock_entry');
        });
    }
}
