<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSavingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('savings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ca_balance');
            $table->string('total_savings');
            $table->string('deduction_rate');
            $table->string('loan_sss');
            $table->string('loan_sss_deduction');
            $table->string('loan_pag_ibig');
            $table->string('loan_pi_deduction');
            $table->string('agent_id');
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
        Schema::dropIfExists('savings');
    }
}
