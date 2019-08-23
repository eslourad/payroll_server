<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class announcements extends Model
{
    protected $fillable = [
        'title', 'body'
    ];
}
