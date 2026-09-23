<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('t_data_absensi', function (Blueprint $table) {
            $table->id();
            $table->string('id_pegawai', 50); // PIN dari Mesin Fingerspot
            $table->string('nama_pegawai', 150); // Diambil dari master M_PEGAWAI
            $table->date('tanggal');
            $table->time('jam_kehadiran')->nullable();
            $table->time('jam_kepulangan')->nullable();
            $table->string('lokasi_absen', 100)->nullable();
            $table->timestamps();
            
            // Indexing sangat penting untuk mempercepat filter data berdasarkan rentang tanggal
            $table->index(['tanggal', 'id_pegawai']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('t_data_absensi');
    }
};
