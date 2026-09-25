<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OvertimeRate extends Model
{
    protected $fillable = [
        'name',
        'type',
        'amount',
    ];
}
