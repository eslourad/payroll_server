<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class paymentperiod extends Model
{
    protected $fillable = [
        'starting_date', 'end_date', 'status'
    ];
}
