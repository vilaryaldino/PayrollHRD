<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('t_jadwal_kerja')) {
            Schema::create('t_jadwal_kerja', function (Blueprint $table) {
                $table->bigIncrements('ID_PENUGASAN');
                $table->unsignedInteger('ID_PEGAWAI');
                $table->date('TANGGAL');
                $table->unsignedInteger('ID_JADWAL')->nullable()->comment('Null jika status LIBUR / OFF');
                $table->enum('STATUS_KERJA', ['Kerja', 'Off', 'Cuti', 'Izin', 'Sakit', 'Libur Nasional'])->default('Kerja');
                $table->string('KETERANGAN', 255)->nullable()->comment('Catatan penugasan');
                $table->boolean('IS_OVERWRITE')->default(false)->comment('1 jika hasil penggantian manual');
                $table->string('ASSIGNED_BY', 50)->nullable()->comment('Username HRD/Supervisor');
                $table->timestamp('CREATED_AT')->useCurrent();
                $table->timestamp('UPDATED_AT')->useCurrent()->useCurrentOnUpdate();

                // Indexes & Foreign Keys
                $table->unique(['ID_PEGAWAI', 'TANGGAL'], 'UQ_PEGAWAI_TANGGAL');
                $table->index('TANGGAL', 'IDX_JADWAL_TANGGAL');
                $table->index('ID_JADWAL', 'IDX_JADWAL_SHIFT');

                $table->foreign('ID_PEGAWAI', 'FK_JADWAL_PEGAWAI')
                    ->references('ID_PEGAWAI')->on('m_pegawai')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

                $table->foreign('ID_JADWAL', 'FK_JADWAL_SHIFT')
                    ->references('ID_JADWAL')->on('m_shift')
                    ->onDelete('set null')
                    ->onUpdate('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('t_jadwal_kerja');
    }
};