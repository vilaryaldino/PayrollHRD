<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('m_pegawai')) {
            Schema::create('m_pegawai', function (Blueprint $table) {
                $table->increments('ID_PEGAWAI');
                $table->string('ID_PEGAWAI_MESIN', 50)->nullable()->comment('ID User pada Mesin Fingerprint');
                $table->string('NM_PEGAWAI', 150);
                $table->unsignedInteger('ID_DIVISI');
                $table->enum('JENIS_PEGAWAI', ['Staff', 'Harian', 'Kontrak', 'Magang'])->default('Harian');
                $table->boolean('IS_AKTIF')->default(true)->comment('1 = Aktif, 0 = Non-Aktif / Resign');
                $table->text('ALAMAT')->nullable();
                $table->string('NO_TELP_HP', 25)->nullable();
                $table->unsignedInteger('ID_KELOMPOK')->nullable();
                $table->timestamp('CREATED_AT')->useCurrent();
                $table->timestamp('UPDATED_AT')->useCurrent()->useCurrentOnUpdate();

                // Indexes
                $table->index('ID_PEGAWAI_MESIN', 'IDX_PEGAWAI_MESIN');
                $table->index('ID_DIVISI', 'IDX_PEGAWAI_DIVISI');
                $table->index('ID_KELOMPOK', 'IDX_PEGAWAI_KELOMPOK');
                $table->index('IS_AKTIF', 'IDX_PEGAWAI_STATUS');

                // Foreign Keys
                $table->foreign('ID_DIVISI', 'FK_PEGAWAI_DIVISI')
                    ->references('ID_DIVISI')->on('m_divisi')
                    ->onUpdate('cascade');

                $table->foreign('ID_KELOMPOK', 'FK_PEGAWAI_KELOMPOK')
                    ->references('ID_KELOMPOK')->on('m_kelompok')
                    ->onDelete('set null')
                    ->onUpdate('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('m_pegawai');
    }
};