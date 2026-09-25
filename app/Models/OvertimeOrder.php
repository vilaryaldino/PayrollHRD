<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OvertimeOrder extends Model
{
    use \Illuminate\Database\Eloquent\Concerns\HasUuids;

    protected $fillable = [
        'spl_number',
        'date',
        'overtime_type',
        'description',
        'location',
        'status',
        'created_by',
        'approved_by',
    ];

    public function participants()
    {
        return $this->hasMany(OvertimeOrderParticipant::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
