<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingColumnsToEmployeeTable extends Migration
{
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->char('photo', 200)->nullable();
            $table->unsignedBigInteger('nationality_id')->default('0')->index();
            $table->char('email', 200)->nullable();
            $table->date('passport_issue_date')->nullable();
            $table->date('passport_expire_date')->nullable();
            $table->char('passport_doc', 200)->nullable();
            $table->char('visa_reference_number', 200)->nullable();
            $table->date('visa_issue_date')->nullable();
            $table->date('visa_expire_date')->nullable();
            $table->char('visa_doc', 200)->nullable();
            $table->char('insurance_reference_number', 200)->nullable();
            $table->date('insurance_issue_date')->nullable();
            $table->date('insurance_expire_date')->nullable();
            $table->char('insurance_doc', 200)->nullable();
            $table->char('driving_licence_reference_number', 200)->nullable();
            $table->date('driving_licence_issue_date')->nullable();
            $table->date('driving_licence_expire_date')->nullable();
            $table->char('driving_licence_doc', 200)->nullable();
            $table->date('emi_id_issue_date')->nullable();
            $table->date('emi_id_expire_date')->nullable();
            $table->char('emi_id_doc', 200)->nullable();
            $table->char('other_reference_number', 200)->nullable();
            $table->date('other_issue_date')->nullable();
            $table->date('other_expire_date')->nullable();
            $table->char('other_doc', 200)->nullable();
            $table->char('labour_code', 20)->nullable();
        });
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('email');
            $table->dropColumn('passport_issue_date');
            $table->dropColumn('passport_expire_date');
            $table->dropColumn('passport_doc');
            $table->dropColumn('visa_reference_number');
            $table->dropColumn('visa_issue_date');
            $table->dropColumn('visa_expire_date');
            $table->dropColumn('visa_doc');
            $table->dropColumn('insurance_reference_number');
            $table->dropColumn('insurance_issue_date');
            $table->dropColumn('insurance_expire_date');
            $table->dropColumn('insurance_doc');
            $table->dropColumn('driving_licence_reference_number');
            $table->dropColumn('driving_licence_issue_date');
            $table->dropColumn('driving_licence_expire_date');
            $table->dropColumn('driving_licence_doc');
            $table->dropColumn('emi_id_issue_date');
            $table->dropColumn('emi_id_expire_date');
            $table->dropColumn('emi_id_doc');
            $table->dropColumn('other_reference_number');
            $table->dropColumn('other_issue_date');
            $table->dropColumn('other_expire_date');
            $table->dropColumn('other_doc');
            $table->dropColumn('labour_code');
        });
    }
}
