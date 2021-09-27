<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportFileTypesTable extends Migration
{
    public function up()
    {
        Schema::create('report_file_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('Name')->nullable();
            $table->unsignedBigInteger('user_id')->default('0')->index();
            $table->unsignedBigInteger('company_id')->default('0')->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('report_file_types');
    }
}
