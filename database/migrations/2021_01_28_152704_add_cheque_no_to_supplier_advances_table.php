<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChequeNoToSupplierAdvancesTable extends Migration
{
    public function up()
    {
        Schema::table('supplier_advances', function (Blueprint $table) {
            $table->text('ChequeNumber')->nullable();
        });
    }

    public function down()
    {
        Schema::table('supplier_advances', function (Blueprint $table) {
            $table->dropColumn('ChequeNumber');
        });
    }
}
