<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCashadvancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cashadvances', function (Blueprint $table) {
            $table->increments('id');
            $table->string('agent_id');
            $table->string('cash_advance');
            $table->string('sss_loan');
            $table->string('pag_ibig_loan');
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
        Schema::dropIfExists('cashadvances');
    }
}
