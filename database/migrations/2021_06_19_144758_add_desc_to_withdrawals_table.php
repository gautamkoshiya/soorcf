<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescToWithdrawalsTable extends Migration
{
    public function up()
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->text('Description')->nullable();
        });
    }

    public function down()
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropColumn('Description');
        });
    }
}
