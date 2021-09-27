<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemBalPaidBalToTaxInvoicesTable extends Migration
{
    public function up()
    {
        Schema::table('tax_invoices', function (Blueprint $table) {
            $table->decimal('RemainingBalance','10','2')->default(0);
        });
    }

    public function down()
    {
        Schema::table('tax_invoices', function (Blueprint $table) {
            $table->dropColumn('RemainingBalance');
        });
    }
}
