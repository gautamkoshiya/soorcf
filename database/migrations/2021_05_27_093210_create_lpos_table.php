<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLposTable extends Migration
{
    public function up()
    {
        Schema::create('lpos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('LPONumber')->nullable();
            $table->unsignedBigInteger('user_id')->default('0')->index();
            $table->unsignedBigInteger('company_id')->default('0')->index();
            $table->unsignedBigInteger('project_id')->default('0')->index();
            $table->unsignedBigInteger('customer_id')->default(0)->index();
            $table->date('FromDate')->default(date('Y-m-d'));
            $table->date('DueDate')->default(date('Y-m-d'));
            $table->string('TermsAndCondition')->nullable();
            $table->string('CustomerNote')->nullable();
            $table->boolean('IsNeedStampOrSignature')->default(true);
            $table->decimal('subTotal',10,2)->default('0');
            $table->decimal('totalVat',10,2)->default('0');
            $table->decimal('grandTotal',10,2)->default('0');
            $table->text('Description')->nullable();
            $table->date('createdDate')->default(date('Y-m-d'));
            $table->boolean('isActive')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lpos');
    }
}
