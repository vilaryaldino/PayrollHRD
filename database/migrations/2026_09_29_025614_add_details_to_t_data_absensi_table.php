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
        Schema::table('t_data_absensi', function (Blueprint $table) {
            $table->string('departemen')->nullable()->after('lokasi_absen');
            $table->string('posisi')->nullable()->after('departemen');
            $table->string('sn_perangkat')->nullable()->after('posisi');
            $table->string('status')->nullable()->after('sn_perangkat');
            $table->string('keterangan')->nullable()->after('status');
            $table->string('method')->nullable()->after('keterangan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_data_absensi', function (Blueprint $table) {
            $table->dropColumn([
                'departemen',
                'posisi',
                'sn_perangkat',
                'status',
                'keterangan',
                'method'
            ]);
        });
    }
};
