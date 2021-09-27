<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFileManagersTable extends Migration
{
    public function up()
    {
        Schema::create('file_managers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('FileCode')->nullable();
            $table->unsignedBigInteger('user_id')->default('0')->index();
            $table->unsignedBigInteger('company_id')->default('0')->index();
            $table->unsignedBigInteger('report_type_id')->default(0);
            $table->text('Description')->nullable();
            $table->text('UpdateDescription')->nullable();
            $table->text('supplierNote')->nullable();
            $table->date('reportDate')->default(date('Y-m-d'));
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('file_managers');
    }
}
