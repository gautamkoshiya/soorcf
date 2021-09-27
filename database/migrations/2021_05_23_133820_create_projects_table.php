<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('Name')->nullable();
            $table->string('Address')->nullable();
            $table->string('Contact')->nullable();
            $table->string('Email')->nullable();
            $table->string('TRN')->nullable();
            $table->string('FAX')->nullable();
            $table->string('manager_name')->nullable();
            $table->date('registration_date')->default(date('Y-m-d'));
            $table->string('renewal_date')->nullable();
            $table->string('logo')->nullable();
            $table->string('signature')->nullable();
            $table->boolean('isActive')->default(true);
            $table->unsignedBigInteger('user_id')->default('0')->index();
            $table->unsignedBigInteger('company_id')->default('0')->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('projects');
    }
}
