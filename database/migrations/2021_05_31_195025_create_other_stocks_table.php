<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOtherStocksTable extends Migration
{
    public function up()
    {
        Schema::create('other_stocks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->default('0')->index();
            $table->unsignedBigInteger('company_id')->default('0')->index();
            $table->decimal('in',10,2)->default('0');
            $table->decimal('out',10,2)->default('0');
            $table->decimal('differance',10,2)->default('0');
            $table->text('Description')->nullable();
            $table->date('createdDate')->default(date('Y-m-d'));
            $table->boolean('isActive')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('other_stocks');
    }
}
