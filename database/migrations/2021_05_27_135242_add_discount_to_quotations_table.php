<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiscountToQuotationsTable extends Migration
{
    public function up()
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->decimal('discount',10,2)->default('0');
        });
    }

    public function down()
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn('discount');
        });
    }
}
