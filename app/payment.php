<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class payment extends Model
{
    protected $fillable = [
        'agent_id', 'branch_id', 'tl_id', 'is_tl', 'payment_period_id', 'work_days', 'ot_hours', 'rest_days', 'regular_holiday', 'special_holiday', 'ot_approval', 'dtr', 'paid_by', 'rate', 'allowance', 'ot_rates', 'regular_holiday_rate', 'special_holiday_rate', 'rest_day_rate', 'sss', 'pag_ibig', 'phil_health', 'savings', 'ca_deduction', 'sss_loan_deduction', 'pi_loan_deduction', 'total_paid', 'adjustments', 'remarks'
    ];
}
