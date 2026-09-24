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

        $laporan = DB::table('t_register_lembur')
            ->select(
                'id_pegawai',
                'nama_pegawai',
                DB::raw('COUNT(id) as total_sesi'),
                DB::raw('SUM(durasi_lembur) as total_durasi'),
                DB::raw('SUM(uang_makan) as total_uang_makan'),
                DB::raw('MIN(tanggal) as tgl_pertama'),
                DB::raw('MAX(tanggal) as tgl_terakhir')
            )
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->groupBy('id_pegawai', 'nama_pegawai')
            ->orderByDesc('total_durasi')
            ->get();

        $grandDurasi = $laporan->sum('total_durasi');
        $grandUangMakan = $laporan->sum('total_uang_makan');
        $grandSesi = $laporan->sum('total_sesi');

        return view('laporan.lembur', compact('laporan', 'bulan', 'tahun', 'grandDurasi', 'grandUangMakan', 'grandSesi'));
    }

    public function lemburDetail(Request $request)
    {
        $idPegawai = $request->id_pegawai;
        $bulan = $request->bulan;
        $tahun = $request->tahun;

        $details = DB::table('t_register_lembur')
            ->select('tanggal', 'hari', 'jenis_spl', 'jam_mulai', 'jam_selesai', 'durasi_lembur', 'uang_makan', 'catatan')
            ->where('id_pegawai', $idPegawai)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal', 'asc')
            ->orderBy('jam_mulai', 'asc')
            ->get();

        $result = $details->map(function ($row) {
            $mnt = (int)round((float)$row->durasi_lembur * 60);
            $jj = intdiv($mnt, 60);
            $mm = $mnt % 60;
            $dur = ($jj > 0 && $mm > 0) ? "{$jj}j {$mm}m" : ($jj > 0 ? "{$jj} jam" : "{$mm} menit");
            return [
                'tanggal' => Carbon::parse($row->tanggal)->format('d/m/Y'),
                'hari' => $row->hari,
                'jenis_spl' => $row->jenis_spl,
                'jam_mulai' => substr($row->jam_mulai, 0, 5),
                'jam_selesai' => substr($row->jam_selesai, 0, 5),
                'durasi' => $dur,
                'uang_makan' => (float)$row->uang_makan,
                'catatan' => $row->catatan ?: '-'
            ];
        });

        return response()->json($result);
    }
}
