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
            if (!Schema::hasColumn('t_data_absensi', 'departemen')) {
                $table->string('departemen')->nullable()->after('lokasi_absen');
            }
            if (!Schema::hasColumn('t_data_absensi', 'posisi')) {
                $table->string('posisi')->nullable()->after('departemen');
            }
            if (!Schema::hasColumn('t_data_absensi', 'sn_perangkat')) {
                $table->string('sn_perangkat')->nullable()->after('posisi');
            }
            if (!Schema::hasColumn('t_data_absensi', 'status')) {
                $table->string('status')->nullable()->after('sn_perangkat');
            }
            if (!Schema::hasColumn('t_data_absensi', 'keterangan')) {
                $table->string('keterangan')->nullable()->after('status');
            }
            if (!Schema::hasColumn('t_data_absensi', 'method')) {
                $table->string('method')->nullable()->after('keterangan');
            }
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
