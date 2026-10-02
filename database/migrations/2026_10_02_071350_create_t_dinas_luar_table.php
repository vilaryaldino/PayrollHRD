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
    // Jika tabel t_dinas_luar sudah ada di DB, lewati proses create
    if (Schema::hasTable('t_dinas_luar')) {
        return;
    }

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

        $table->foreign('id_pegawai')
              ->references('ID_PEGAWAI')
              ->on('m_pegawai')
              ->onDelete('cascade');
    });
}
};