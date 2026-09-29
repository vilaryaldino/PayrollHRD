<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('m_shift')) {
            Schema::create('m_shift', function (Blueprint $table) {
                $table->increments('ID_JADWAL');
                $table->string('SHIFT_CODE', 20)->unique('UQ_SHIFT_CODE');
                $table->string('NAMA_SHIFT', 50);
                $table->time('JAM_MULAI')->comment('Waktu shift dimulai');
                $table->time('JAM_SELESAI')->comment('Waktu shift berakhir');
                $table->time('JAM_AWAL')->comment('Batas awal scan fingerprint/absen masuk');
                $table->time('JAM_AKHIR')->comment('Batas akhir toleransi scan absen pulang');
                $table->boolean('IS_OVERNIGHT')->default(false)->comment('1 jika shift melewati pergantian hari');
                $table->string('WARNA_LABEL', 20)->default('#3B82F6')->comment('Kode warna hex untuk visualisasi kalender');
                $table->boolean('IS_STAFF_DEFAULT')->default(false)->comment('1=Shift default untuk pegawai Staff');
                $table->timestamp('CREATED_AT')->useCurrent();
                $table->timestamp('UPDATED_AT')->useCurrent()->useCurrentOnUpdate();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('m_shift');
    }
};