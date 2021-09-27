<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemainingQtyToLpoDetailsTable extends Migration
{
    public function up()
    {
        Schema::table('lpo_details', function (Blueprint $table) {
            $table->decimal('RemainingQty','10','2')->default(0);
        });
    }

    public function down()
    {
        Schema::table('lpo_details', function (Blueprint $table) {
            $table->dropColumn('RemainingQty');
        });
    }
}
