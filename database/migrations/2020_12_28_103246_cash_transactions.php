<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CashTransactions extends Migration
{
    public function up()
    {
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('Reference')->nullable();
            $table->date('createdDate')->default(date('Y-m-d'));
            $table->string('Type')->nullable();
            $table->string('Details')->nullable();
            $table->decimal('Credit',10,2)->default('0');
            $table->decimal('Debit',10,2)->default('0');
            $table->decimal('Differentiate',10,2)->default('0');
            $table->softDeletes();
            $table->timestamps();
        });
    }


    public function down()
    {
        //
    }
}
