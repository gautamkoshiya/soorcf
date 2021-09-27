<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiscountToTaxInvoicesTable extends Migration
{
    public function up()
    {
        Schema::table('tax_invoices', function (Blueprint $table) {
            $table->decimal('discount',10,2)->default('0');
        });
    }

    public function down()
    {
        Schema::table('tax_invoices', function (Blueprint $table) {
            $table->dropColumn('discount');
        });
    }
}
