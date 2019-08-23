<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class cashadvance extends Model
{
    protected $fillable = [
        'agent_id', 'cash_advance', 'sss_loan', 'pag_ibig_loan'
    ];
}
