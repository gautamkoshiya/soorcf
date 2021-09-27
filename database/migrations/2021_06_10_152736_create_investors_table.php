<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvestorsTable extends Migration
{
    public function up()
    {
        Schema::create('investors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('Name');
            $table->string('Mobile')->nullable();
            $table->text('Description')->nullable();
            $table->unsignedBigInteger('user_id')->default(0);
            $table->unsignedBigInteger('company_id')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('investors');
    }
}
