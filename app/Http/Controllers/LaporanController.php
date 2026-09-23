<?php

namespace App\Http\Controllers;

use App\Models\DataAbsensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LaporanController extends Controller
{
    // Konstanta Tarif
    private const TARIF_UANG_MAKAN = 15000;
    // (Tarif lembur dihapus karena mengikuti aturan di t_register_lembur)
    private const JAM_PULANG_NORMAL = '17:00:00';

    public function uangMakan(Request $request)
    {
        // Default filter: Bulan berjalan
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));

        // Query untuk menghitung total kehadiran (Group by ID Pegawai & Nama)
        $laporan = DataAbsensi::select('id_pegawai', 'nama_pegawai')
            ->selectRaw('COUNT(*) as total_hari_hadir')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->whereNotNull('jam_kehadiran') // Syarat dihitung hadir
            ->groupBy('id_pegawai', 'nama_pegawai')
            ->get();

        // Hitung nominal uang makan
        $laporan->map(function ($item) {
            $item->total_uang_makan = $item->total_hari_hadir * self::TARIF_UANG_MAKAN;
            return $item;
        });

        return view('laporan.uang-makan', compact('laporan', 'bulan', 'tahun'));
    }

    public function lembur(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));

        /* 
         * LOGIKA BARU: Mengambil data langsung dari tabel t_register_lembur
         * Asumsi nama kolom di t_register_lembur:
         * - id_pegawai
         * - tanggal
         * - jumlah_jam
         * - nominal_bayar
         * Silakan sesuaikan nama kolom DB::raw di bawah ini dengan struktur tabel Anda.
         */
        $laporanData = DB::table('t_register_lembur')
            ->join('m_pegawai', 't_register_lembur.id_pegawai', '=', 'm_pegawai.ID_PEGAWAI')
            ->select(
                'm_pegawai.ID_PEGAWAI_MESIN as id_pegawai', 
                'm_pegawai.NM_PEGAWAI as nama_pegawai',
                // Sesuaikan 'jumlah_jam' dan 'nominal_bayar' dengan kolom asli Anda
                DB::raw('SUM(t_register_lembur.jumlah_jam) as total_jam_lembur'),
                DB::raw('SUM(t_register_lembur.nominal_bayar) as total_uang_lembur')
            )
            ->whereMonth('t_register_lembur.tanggal', $bulan)
            ->whereYear('t_register_lembur.tanggal', $tahun)
            ->groupBy('m_pegawai.ID_PEGAWAI_MESIN', 'm_pegawai.NM_PEGAWAI')
            ->get();

        $laporan = $laporanData->filter(function($item) {
            return $item->total_jam_lembur > 0;
        })->values();


        return view('laporan.lembur', compact('laporan', 'bulan', 'tahun'));
    }
}
