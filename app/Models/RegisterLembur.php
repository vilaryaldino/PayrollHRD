<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegisterLembur extends Model
{
    protected $table = 't_register_lembur';

    protected $fillable = [
        'id_pegawai',
        'nama_pegawai',
        'tanggal',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'jenis_spl',
        'catatan',
        'durasi_lembur',
        'uang_makan'
    ];
}
