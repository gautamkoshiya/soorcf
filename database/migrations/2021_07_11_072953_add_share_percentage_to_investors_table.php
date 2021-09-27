<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSharePercentageToInvestorsTable extends Migration
{
    public function up()
    {
        Schema::table('investors', function (Blueprint $table) {
            $table->decimal('SharePercentage','10','2')->default(0);
        });
    }

    public function down()
    {
        Schema::table('investors', function (Blueprint $table) {
            $table->dropColumn('SharePercentage');
        });
    }
}
