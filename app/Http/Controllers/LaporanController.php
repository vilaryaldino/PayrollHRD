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

        // Query untuk menghitung total kehadiran dan uang makan lembur
        $laporan = DataAbsensi::select('id_pegawai', 'nama_pegawai')
            ->selectRaw('COUNT(jam_kehadiran) as total_hari_hadir')
            ->selectRaw("SUM(CASE WHEN jam_kepulangan >= '19:00:00' THEN 1 ELSE 0 END) as total_um_lembur")
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->whereNotNull('jam_kehadiran') // Syarat dihitung hadir
            ->groupBy('id_pegawai', 'nama_pegawai')
            ->get();

        // Hitung nominal uang makan
        $laporan->map(function ($item) {
            $item->nominal_um = $item->total_hari_hadir * self::TARIF_UANG_MAKAN;
            $item->nominal_uml = $item->total_um_lembur * self::TARIF_UANG_MAKAN;
            $item->total_uang_makan = $item->nominal_um + $item->nominal_uml;
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
            $dur = (float)$row->durasi_lembur . " Hari";
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

    public function rekapitulasi(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));

        $laporan = DataAbsensi::select('id_pegawai', 'nama_pegawai')
            ->selectRaw('COUNT(jam_kehadiran) as total_hari_kerja')
            ->selectRaw("SUM(CASE WHEN jam_kepulangan >= '17:01:00' AND jam_kepulangan <= '18:00:00' THEN 1 ELSE 0 END) as total_la")
            ->selectRaw("SUM(CASE WHEN jam_kepulangan > '18:00:00' THEN 1 ELSE 0 END) as total_lb")
            ->selectRaw("SUM(CASE WHEN jam_kepulangan >= '19:00:00' THEN 1 ELSE 0 END) as total_uml")
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->whereNotNull('jam_kehadiran')
            ->groupBy('id_pegawai', 'nama_pegawai')
            ->get();

        $laporan->map(function ($item) {
            $item->amt_la = $item->total_la * 29200;
            $item->amt_lb = $item->total_lb * 34400;
            $item->amt_um = $item->total_hari_kerja * 15000;
            $item->amt_uml = $item->total_uml * 15000;
            $item->total_idr = $item->amt_la + $item->amt_lb + $item->amt_um + $item->amt_uml;
            return $item;
        });

        return view('laporan.rekapitulasi', compact('laporan', 'bulan', 'tahun'));
    }

    public function cetakRekap(Request $request, $id_pegawai)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));

        $pegawai = DB::table('M_PEGAWAI')->where('ID_PEGAWAI', $id_pegawai)->first();
        if (!$pegawai) abort(404);

        $absen = DataAbsensi::selectRaw('COUNT(jam_kehadiran) as total_hari_kerja')
            ->selectRaw("SUM(CASE WHEN jam_kepulangan >= '17:01:00' AND jam_kepulangan <= '18:00:00' THEN 1 ELSE 0 END) as total_la")
            ->selectRaw("SUM(CASE WHEN jam_kepulangan > '18:00:00' THEN 1 ELSE 0 END) as total_lb")
            ->selectRaw("SUM(CASE WHEN jam_kepulangan >= '19:00:00' THEN 1 ELSE 0 END) as total_uml")
            ->where('id_pegawai', $id_pegawai)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->whereNotNull('jam_kehadiran')
            ->first();

        $total_hari_kerja = $absen->total_hari_kerja ?? 0;
        $total_la = $absen->total_la ?? 0;
        $total_lb = $absen->total_lb ?? 0;
        $total_uml = $absen->total_uml ?? 0;

        $startDate = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

        $data = [
            'periode' => "$startDate s/d $endDate",
            'karyawan' => [
                'no' => $pegawai->ID_PEGAWAI,
                'nama' => $pegawai->NM_PEGAWAI
            ],
            'lokasi' => 'KANTOR',
            'hari_kerja' => ['jam' => $total_hari_kerja],
            'lembur_a' => [
                'qty' => $total_la,
                'rate' => 29200,
                'amount' => $total_la * 29200
            ],
            'lembur_b' => [
                'qty' => $total_lb,
                'rate' => 34400,
                'amount' => $total_lb * 34400
            ],
            'luar_kota' => [
                'qty' => 0,
                'rate' => 30000,
                'amount' => 0
            ],
            'uang_makan' => [
                'qty' => $total_hari_kerja,
                'rate' => 15000,
                'amount' => $total_hari_kerja * 15000
            ],
            'uang_makan_lembur' => [
                'qty' => $total_uml,
                'rate' => 15000,
                'amount' => $total_uml * 15000
            ],
        ];

        $totalIdr = $data['lembur_a']['amount'] + 
                    $data['lembur_b']['amount'] + 
                    $data['luar_kota']['amount'] + 
                    $data['uang_makan']['amount'] + 
                    $data['uang_makan_lembur']['amount'];
                    
        $data['total_idr'] = $totalIdr;

        return view('laporan.spl-rekap', compact('data'));
    }
}
