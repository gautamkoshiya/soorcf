<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskFrequenciesTable extends Migration
{
    public function up()
    {
        Schema::create('task_frequencies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('Name');
            $table->unsignedBigInteger('user_id')->default(0);
            $table->unsignedBigInteger('company_id')->default(0);
            $table->timestamp('createdDate')->useCurrent();
            $table->boolean('isActive')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('task_frequencies');
    }
}
