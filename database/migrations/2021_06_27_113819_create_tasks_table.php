<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('master_id')->default(0)->index();
            $table->unsignedBigInteger('assigned_to')->default(0)->index();
            $table->date('Date')->default(date('Y-m-d'));
            $table->time('CompletionTime')->default('23:23:59');
            $table->boolean('status')->default(false);
            $table->text('Note')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tasks');
    }
}
