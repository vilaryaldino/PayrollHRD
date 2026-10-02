<?php

namespace App\Http\Controllers;

use App\Models\DataAbsensi;
use App\Imports\AbsensiImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('t_data_absensi')
            ->leftJoin('m_pegawai', 't_data_absensi.nama_pegawai', '=', 'm_pegawai.NM_PEGAWAI')
            ->select('t_data_absensi.*', 'm_pegawai.ID_PEGAWAI_MESIN as id_mesin_pegawai');

        // 1. Filter Rentang Tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal', [$request->start_date, $request->end_date]);
        }

        // 2. Pencarian Nama / PIN Pegawai
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('t_data_absensi.nama_pegawai', 'like', "%{$search}%")
                  ->orWhere('t_data_absensi.id_pegawai', 'like', "%{$search}%")
                  ->orWhere('m_pegawai.ID_PEGAWAI_MESIN', 'like', "%{$search}%");
            });
        }

        $query->orderBy('tanggal', 'desc')->orderBy('jam_kehadiran', 'asc');
        
        // Paginasi bawaan Laravel
        $perPage = $request->get('per_page', 25);
        $absensis = $query->paginate($perPage)->withQueryString();

        // Data Pegawai untuk modal manual
        $pegawais = DB::table('m_pegawai')->where('IS_AKTIF', 1)->whereNotNull('ID_PEGAWAI_MESIN')->orderBy('NM_PEGAWAI', 'ASC')->get();

        return view('absensi.index', compact('absensis', 'pegawais'));
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xls,xlsx|max:5120', // Max 5MB
        ]);

        try {
            Excel::import(new AbsensiImport, $request->file('file_excel'));
            return back()->with('success', 'Data absensi berhasil di-import & di-mapping.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
        }
    }

    public function storeManual(Request $request)
    {
        $request->validate([
            'id_pegawai'     => 'required',
            'tanggal'        => 'required|date',
            'jam_kehadiran'  => 'nullable|date_format:H:i',
            'jam_kepulangan' => 'nullable|date_format:H:i',
        ]);

        try {
            // Cek Nama Manual
            $master = DB::table('M_PEGAWAI')->where('ID_PEGAWAI_MESIN', $request->id_pegawai)->first();
            $namaPegawai = $master ? $master->NM_PEGAWAI : 'Pegawai Tidak Dikenal / Belum Terdaftar';
            $idPegawai = $master ? $master->ID_PEGAWAI : null;
        } catch (\Exception $e) {
            $namaPegawai = 'Pegawai Tidak Dikenal / Belum Terdaftar';
            $idPegawai = null;
        }

        DataAbsensi::updateOrCreate(
            ['id_pegawai_mesin' => $request->id_pegawai, 'tanggal' => $request->tanggal],
            [
                'id_pegawai'     => $idPegawai,
                'nama_pegawai'   => $namaPegawai,
                'jam_kehadiran'  => $request->jam_kehadiran,
                'jam_kepulangan' => $request->jam_kepulangan,
                'lokasi_absen'   => $request->lokasi_absen ?? 'Kantor Pusat',
            ]
        );

        return back()->with('success', 'Data absensi manual berhasil ditambahkan.');
    }

    public function destroy($id)
    {
        DataAbsensi::findOrFail($id)->delete();
        return back()->with('success', 'Data absensi berhasil dihapus.');
    }

    public function periode(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));

        $pegawais = DB::table('m_pegawai')->where('IS_AKTIF', 1)->orderBy('NM_PEGAWAI', 'ASC')->get();

        $absensis = DB::table('t_data_absensi')
            ->select('id_pegawai')
            ->selectRaw('COUNT(jam_kehadiran) as total_hari_kerja')
            ->selectRaw('SUM(CASE WHEN DAYOFWEEK(tanggal) IN (1, 7) THEN 1 ELSE 0 END) as total_hari_libur')
            ->selectRaw("SUM(CASE WHEN jam_kepulangan >= '19:00:00' THEN 1 ELSE 0 END) as total_uml")
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->whereNotNull('jam_kehadiran')
            ->groupBy('id_pegawai')
            ->get()->keyBy('id_pegawai');

        $lemburRaw = DB::table('t_register_lembur')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get();
            
        $lemburData = [];
        $rate_la = 29200;
        $rate_lb = 34400;
        $rate_sm = 34797;
        
        foreach ($lemburRaw as $lembur) {
            $laQty = 0;
            $lbQty = 0;
            $catatan = $lembur->catatan ?? '';
            
            if (preg_match('/LA:\s*([0-9\.]+)/i', $catatan, $m)) $laQty = (float)$m[1];
            if (preg_match('/LB:\s*([0-9\.]+)/i', $catatan, $m)) $lbQty = (float)$m[1];
            
            if ($laQty == 0 && $lbQty == 0 && (float)$lembur->durasi_lembur > 0) {
                $durasi = (float)$lembur->durasi_lembur;
                if (stripos($lembur->jenis_spl, 'Lembur B') !== false) {
                    $lbQty = $durasi;
                } elseif (stripos($lembur->jenis_spl, 'Lembur A') !== false) {
                    $laQty = $durasi;
                } elseif (stripos($lembur->jenis_spl, 'Luar Kota') === false) {
                    if ($durasi <= 1) {
                        $laQty = $durasi;
                    } else {
                        $laQty = 1;
                        $lbQty = $durasi - 1;
                    }
                }
            }
            
            $empId = $lembur->id_pegawai;
            if (!isset($lemburData[$empId])) {
                $lemburData[$empId] = (object)['jam_la' => 0, 'jam_lb' => 0, 'amt_la' => 0, 'amt_lb' => 0];
            }
            $lemburData[$empId]->jam_la += $laQty;
            $lemburData[$empId]->jam_lb += $lbQty;
            $lemburData[$empId]->amt_la += ($laQty * $rate_la);
            $lemburData[$empId]->amt_lb += ($lbQty * $rate_lb);
        }

        $dinasLuar = DB::table('t_dinas_luar')
            ->select('id_pegawai')
            ->selectRaw("SUM(CASE WHEN jenis_dinas = 'LK' THEN jumlah_hari ELSE 0 END) as total_lk")
            ->selectRaw("SUM(CASE WHEN jenis_dinas = 'LP' THEN jumlah_hari ELSE 0 END) as total_lp")
            ->selectRaw("SUM(gaji_luar) as total_gaji_luar")
            ->selectRaw("SUM(CASE WHEN jenis_dinas = 'LK' THEN total_uang_makan ELSE 0 END) as total_um_lk")
            ->selectRaw("SUM(CASE WHEN jenis_dinas = 'LP' THEN total_uang_makan ELSE 0 END) as total_um_lp")
            ->selectRaw("SUM(uang_makan_lembur) as total_um_lembur")
            ->whereMonth('tanggal_mulai', $bulan)
            ->whereYear('tanggal_mulai', $tahun)
            ->groupBy('id_pegawai')
            ->get()->keyBy('id_pegawai');
        
        $rate_um = 15000;

        $laporan = [];
        $no = 1;
        foreach ($pegawais as $pegawai) {
            $abs = $absensis->get($pegawai->ID_PEGAWAI);
            $dl = $dinasLuar->get($pegawai->ID_PEGAWAI);
            $lbr = isset($lemburData[$pegawai->ID_PEGAWAI]) ? $lemburData[$pegawai->ID_PEGAWAI] : null;

            if (!$abs && !$dl && !$lbr) continue;

            $total_hari_kerja = $abs ? $abs->total_hari_kerja : 0;
            $hkl = $abs ? $abs->total_hari_libur : 0;
            $hk = max(0, $total_hari_kerja - $hkl);

            $lk = $dl ? (int)$dl->total_lk : 0;
            $lp = $dl ? (int)$dl->total_lp : 0;

            $jam_la = $lbr ? (int)$lbr->jam_la : 0;
            $jam_lb = $lbr ? (int)$lbr->jam_lb : 0;
            $jam_sm = 0; 

            $amt_la = $lbr ? (float)$lbr->amt_la : 0;
            $amt_lb = $lbr ? (float)$lbr->amt_lb : 0;
            $amt_sm = 0;

            // Gaji LK / LP dari input manual
            $gaji_lk_lp = $dl ? (float)$dl->total_gaji_luar : 0; 
            
            $um_lk = $dl ? (float)$dl->total_um_lk : 0;
            $um_lp = $dl ? (float)$dl->total_um_lp : 0;
            $um_lembur_manual = $dl ? (float)$dl->total_um_lembur : 0;

            $uang_makan = $total_hari_kerja * $rate_um;
            $uang_makan_lembur = $um_lembur_manual; // Gunakan inputan manual untuk uang makan lembur

            $total_idr = $amt_la + $amt_lb + $amt_sm + $gaji_lk_lp + $um_lk + $um_lp + $uang_makan + $uang_makan_lembur;

            $laporan[] = (object)[
                'no' => $no++,
                'nama' => $pegawai->NM_PEGAWAI,
                'hk' => $hk,
                'hkl' => $hkl,
                'lk' => $lk,
                'lp' => $lp,
                'jam_la' => $jam_la,
                'jam_lb' => $jam_lb,
                'jam_sm' => $jam_sm,
                'amt_la' => $amt_la,
                'amt_lb' => $amt_lb,
                'amt_sm' => $amt_sm,
                'gaji_lk_lp' => $gaji_lk_lp,
                'um_lk' => $um_lk,
                'um_lp' => $um_lp,
                'uang_makan' => $uang_makan,
                'uang_makan_lembur' => $uang_makan_lembur,
                'total_idr' => $total_idr
            ];
        }

        return view('absensi.periode', compact('laporan', 'bulan', 'tahun', 'rate_la', 'rate_lb', 'rate_sm'));
    }

    public function storeDinasLuar(Request $request)
    {
        $request->validate([
            'id_pegawai' => 'required|exists:M_PEGAWAI,ID_PEGAWAI',
            'jenis_dinas' => 'required|in:LK,LP',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'jumlah_hari' => 'required|integer|min:1',
            'gaji_luar' => 'nullable|numeric',
            'tarif_um' => 'nullable|numeric',
            'total_uang_makan' => 'nullable|numeric',
            'uang_makan_lembur' => 'nullable|numeric',
            'catatan' => 'nullable|string|max:255',
        ]);

        DB::table('t_dinas_luar')->insert([
            'id_pegawai' => $request->id_pegawai,
            'jenis_dinas' => $request->jenis_dinas,
            'gaji_luar' => $request->gaji_luar ?? 0,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'jumlah_hari' => $request->jumlah_hari,
            'tarif_um' => $request->tarif_um ?? 0,
            'total_uang_makan' => $request->total_uang_makan ?? 0,
            'uang_makan_lembur' => $request->uang_makan_lembur ?? 0,
            'catatan' => $request->catatan,
        ]);

        return back()->with('success', 'Data Dinas Luar berhasil ditambahkan.');
    }
}
