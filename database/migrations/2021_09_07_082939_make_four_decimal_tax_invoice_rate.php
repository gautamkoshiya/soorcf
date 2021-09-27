<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeFourDecimalTaxInvoiceRate extends Migration
{
    public function up()
    {
        Schema::table('tax_invoice_details', function (Blueprint $table) {
            $table->decimal('Price',10,4)->default('0')->change();
        });
    }

    public function down()
    {
        //
    }
}
