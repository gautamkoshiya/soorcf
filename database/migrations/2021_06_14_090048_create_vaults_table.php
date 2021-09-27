<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVaultsTable extends Migration
{
    public function up()
    {
        Schema::create('vaults', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('transaction_type')->default(0);
            $table->decimal('totalAmount','10','2')->default(0);
            $table->unsignedBigInteger('user_id')->default(0)->index();
            $table->unsignedBigInteger('company_id')->default(0)->index();
            $table->date('transferDate')->default(date('Y-m-d'));
            $table->text('Description')->nullable();
            $table->date('createdDate')->default(date('Y-m-d'));
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vaults');
    }
}
