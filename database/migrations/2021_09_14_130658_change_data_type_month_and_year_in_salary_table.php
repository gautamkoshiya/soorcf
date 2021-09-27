<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDataTypeMonthAndYearInSalaryTable extends Migration
{
    public function up()
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->integer('Month')->default(0)->change();
            $table->integer('Year')->default(0)->change();
        });
    }

    public function down()
    {
        //
    }
}
