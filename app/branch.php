<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class branch extends Model
{
    protected $fillable = [
        'branch_name', 'rate', 'allowance', 'ot_rates', 'regular_holidays', 'special_holidays', 'rest_days', 'team_lead'
    ];
}
