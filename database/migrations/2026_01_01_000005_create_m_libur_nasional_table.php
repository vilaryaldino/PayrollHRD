<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('m_libur_nasional', function (Blueprint $table) {
            $table->increments('ID_LIBUR');
            $table->date('TANGGAL')->unique('UQ_LIBUR_TANGGAL');
            $table->string('KETERANGAN', 255)->comment('Deskripsi nama hari libur / cuti bersama');
            $table->enum('JENIS_LIBUR', ['Nasional', 'Cuti Bersama', 'Khusus Perusahaan'])->default('Nasional');
            $table->boolean('IS_DIBAYAR')->default(true)->comment('1 jika diperhitungkan dalam hari kerja berbayar');
            $table->timestamp('CREATED_AT')->useCurrent();
            $table->timestamp('UPDATED_AT')->useCurrent()->useCurrentOnUpdate();

            $table->index(['TANGGAL', 'JENIS_LIBUR'], 'IDX_LIBUR_PERIODE');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('m_libur_nasional');
    }
};