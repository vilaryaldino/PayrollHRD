<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_dinas_luar', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('id_pegawai');
            $table->enum('jenis_dinas', ['LK', 'LP'])->comment('LK = Luar Kota, LP = Luar Pulau');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->integer('jumlah_hari');
            $table->decimal('tarif_um', 10, 2)->default(0.00)->comment('Nominal Uang Makan per hari');
            $table->decimal('total_uang_makan', 12, 2)->default(0.00);
            $table->string('catatan', 255)->nullable();
            $table->timestamps();

            // Ignore foreign key issue if m_pegawai doesn't match type perfectly, just add index
            $table->index('id_pegawai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_dinas_luar');
    }
};
