<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTransactionDescToAccountTransactionsTable extends Migration
{
    public function up()
    {
        Schema::table('account_transactions', function (Blueprint $table) {
            $table->text('TransactionDesc')->nullable();
        });
    }

    public function down()
    {
        Schema::table('account_transactions', function (Blueprint $table) {
            $table->dropColumn('TransactionDesc');
        });
    }
}
