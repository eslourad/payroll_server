<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class deductions extends Model
{
    protected $fillable = [
        'sss', 'pag_ibig', 'phil_health', 'savings'
    ];
}
