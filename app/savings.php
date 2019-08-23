<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class savings extends Model
{
	protected $fillable = [
        'ca_balance', 'total_savings', 'deduction_rate', 'loan_sss', 'loan_sss_deduction', 'loan_pag_ibig', 'loan_pi_deduction', 'agent_id'
    ];
}
