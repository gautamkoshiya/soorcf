<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescToDepositsTable extends Migration
{
    public function up()
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->text('Description')->nullable();
        });
    }

    public function down()
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->dropColumn('Description');
        });
    }
}
