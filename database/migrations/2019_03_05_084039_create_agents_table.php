<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('dob');
            $table->string('employment_date');
            $table->string('email');
            $table->string('title');
            $table->string('mobile_number');
            $table->string('current_address');
            $table->string('permanent_address');
            $table->string('emergency_person');
            $table->string('emergency_number');
            $table->string('high_school');
            $table->tinyInteger('is_hs_grad');
            $table->string('hs_grad_year')->nullable();
            $table->string('college')->nullable();
            $table->tinyInteger('is_college_grad')->nullable();
            $table->string('college_grad_year')->nullable();
            $table->string('college_course')->nullable();
            $table->string('technical_school')->nullable();
            $table->tinyInteger('is_ts_grad')->nullable();
            $table->string('ts_program')->nullable();
            $table->string('ts_year')->nullable();
            $table->string('image_file_name');
            $table->string('image_file_size');
            $table->string('image_date_uploaded');
            $table->string('psa_file_name');
            $table->string('psa_file_size');
            $table->string('psa_date_uploaded');
            $table->string('nbi_file_name');
            $table->string('nbi_file_size');
            $table->string('nbi_date_uploaded');
            $table->string('sss');
            $table->string('sss_file');
            $table->string('pag_ibig');
            $table->string('pag_ibig_file');
            $table->string('phil_health');
            $table->string('phil_health_file');
            $table->string('bank_name');
            $table->string('account_name');
            $table->string('account_number');
            $table->integer('branch_id');
            $table->tinyInteger('is_active');
            $table->string('company_name');
            $table->string('position');
            $table->smallInteger('from');
            $table->smallInteger('to');
            $table->string('company_name_two')->nullable();
            $table->string('position_two')->nullable();
            $table->smallInteger('from_two')->nullable();
            $table->smallInteger('to_two')->nullable();
            $table->string('company_name_three')->nullable();
            $table->string('position_three')->nullable();
            $table->smallInteger('from_three')->nullable();
            $table->smallInteger('to_three')->nullable();
            $table->string('allowance');
            $table->string('isTL')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agents');
    }
}
