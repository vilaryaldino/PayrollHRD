<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OvertimeOrderParticipant extends Model
{
    protected $fillable = [
        'overtime_order_id',
        'employee_id',
        'meal_allowance',
        'notes',
    ];

    public function order()
    {
        return $this->belongsTo(OvertimeOrder::class, 'overtime_order_id');
    }

    public function employee()
    {
        // Adjust if m_pegawai uses a different model
        return $this->belongsTo(\stdClass::class, 'employee_id', 'ID_PEGAWAI');
    }
}
