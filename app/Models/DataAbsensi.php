<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataAbsensi extends Model
{
    protected $table = 't_data_absensi';
    
    // Mass assignment protection
    protected $fillable = [
        'id_mesin',
        'id_pegawai',
        'nama_pegawai',
        'tanggal',
        'jam_kehadiran',
        'jam_kepulangan',
        'lokasi_absen'
    ];
}
