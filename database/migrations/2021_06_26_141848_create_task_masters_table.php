<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskMastersTable extends Migration
{
    public function up()
    {
        Schema::create('task_masters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('Name')->nullable();
            $table->unsignedBigInteger('user_id')->default('0')->index();
            $table->unsignedBigInteger('company_id')->default('0')->index();
            $table->unsignedBigInteger('frequency_id')->default('0')->index();
            $table->unsignedBigInteger('assigned_to')->default(0)->index();
            $table->text('Description')->nullable();
            $table->date('StartDate')->default(date('Y-m-d'));
            $table->date('EndDate')->default(date('Y-m-d'));
            $table->time('CompletionTime')->default('23:23:59');
            $table->boolean('isActive')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('task_masters');
    }
}
