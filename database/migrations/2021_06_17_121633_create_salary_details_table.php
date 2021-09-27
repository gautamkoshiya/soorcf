<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalaryDetailsTable extends Migration
{
    public function up()
    {
        Schema::create('salary_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->default('0')->index();
            $table->unsignedBigInteger('company_id')->default('0')->index();
            $table->unsignedBigInteger('salary_id')->default('0')->index();
            $table->unsignedBigInteger('employee_id')->default('0')->index();
            $table->unsignedBigInteger('designation_id')->default('0')->index();
            $table->text('transaction_type')->nullable();
            $table->decimal('BasicAmount',10,2)->default('0');
            $table->decimal('Month',2)->default('0');
            $table->decimal('Year',4)->default('0');
            $table->text('Description')->nullable();
            $table->text('ReferenceNo')->nullable();
            $table->date('createdDate')->default(date('Y-m-d'));
            $table->boolean('isActive')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('salary_details');
    }
}
