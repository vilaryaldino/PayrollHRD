<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('t_data_absensi')) {
            Schema::create('t_data_absensi', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('id_pegawai_mesin', 50)->comment('PIN / ID dari Mesin Fingerspot');
                $table->unsignedInteger('id_pegawai')->nullable();
                $table->string('nama_pegawai', 150);
                $table->date('tanggal');
                $table->time('jam_kehadiran')->nullable();
                $table->time('jam_kepulangan')->nullable();
                $table->string('lokasi_absen', 100)->nullable()->default('Kantor Pusat');
                $table->string('departemen', 255)->nullable();
                $table->string('posisi', 255)->nullable();
                $table->string('sn_perangkat', 255)->nullable();
                $table->string('status', 255)->nullable();
                $table->string('keterangan', 255)->nullable();
                $table->string('method', 255)->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

                // Indexes
                $table->index('tanggal', 'idx_absen_tanggal');
                $table->index('id_pegawai_mesin', 'idx_absen_mesin');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('t_data_absensi');
    }
};