<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('agent_id');
            $table->string('branch_id');
            $table->string('tl_id');
            $table->string('is_tl');
            $table->string('payment_period_id');
            $table->string('work_days');
            $table->string('ot_hours');
            $table->string('rest_days');
            $table->string('regular_holiday');
            $table->string('special_holiday');
            $table->string('adjustments');
            $table->string('ot_approval')->nullable();
            $table->string('dtr');
            $table->string('paid_by');
            $table->string('rate');70
            $table->string('allowance');
            $table->string('ot_rates');
            $table->string('regular_holiday_rate');
            $table->string('special_holiday_rate');
            $table->string('rest_day_rate');
            $table->string('sss');
            $table->string('pag_ibig');
            $table->string('phil_health');
            $table->string('savings');
            $table->string('ca_deduction');
            $table->string('total_paid');
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
        Schema::dropIfExists('payments');
    }
}
