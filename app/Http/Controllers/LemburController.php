<?php

namespace App\Http\Controllers;

use App\Models\RegisterLembur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LemburController extends Controller
{
    public function register(Request $request)
    {
        $pegawaiList = DB::table('M_PEGAWAI')->where('IS_AKTIF', 1)->orderBy('NM_PEGAWAI')->get();

        $daftarLembur = RegisterLembur::select('t_register_lembur.*', 'M_PEGAWAI.NM_PEGAWAI as pegawai_master')
            ->leftJoin('M_PEGAWAI', 't_register_lembur.id_pegawai', '=', 'M_PEGAWAI.ID_PEGAWAI')
            ->where('t_register_lembur.kategori', 'Harian')
            ->orderBy('t_register_lembur.tanggal', 'desc')
            ->orderBy('t_register_lembur.id', 'desc')
            ->get();

        $targetId = $request->query('last_id');
        $displaySummary = null;

        if ($targetId) {
            $displaySummary = $daftarLembur->firstWhere('id', $targetId);
        }

        if (!$displaySummary && $daftarLembur->isNotEmpty()) {
            $displaySummary = $daftarLembur->first();
        }

        $summaryData = [
            'pegawai'       => $displaySummary ? ($displaySummary->nama_pegawai ?: ($displaySummary->pegawai_master ?? '—')) : '—',
            'tanggal'       => $displaySummary ? $displaySummary->tanggal : date('Y-m-d'),
            'jam_mulai'     => $displaySummary ? substr($displaySummary->jam_mulai, 0, 5) : '—',
            'jam_selesai'   => $displaySummary ? substr($displaySummary->jam_selesai, 0, 5) : '—',
            'hari'          => $displaySummary ? $displaySummary->hari : 'Hari Kerja',
            'jenis_spl'     => $displaySummary ? $displaySummary->jenis_spl : 'SPL Jam Lembur',
            'catatan'       => $displaySummary ? ($displaySummary->catatan ?? '') : '',
            'durasi_lembur' => $displaySummary ? (float)$displaySummary->durasi_lembur : 0.00,
            'uang_makan'    => $displaySummary ? (float)$displaySummary->uang_makan : 0.00,
        ];

        return view('lembur.register', compact('pegawaiList', 'daftarLembur', 'summaryData', 'targetId'));
    }

    public function registerOtomatis(Request $request)
    {
        $pegawaiList = DB::table('M_PEGAWAI')->where('IS_AKTIF', 1)->orderBy('NM_PEGAWAI')->get();

        $daftarLembur = RegisterLembur::select('t_register_lembur.*', 'M_PEGAWAI.NM_PEGAWAI as pegawai_master')
            ->leftJoin('M_PEGAWAI', 't_register_lembur.id_pegawai', '=', 'M_PEGAWAI.ID_PEGAWAI')
            ->where('t_register_lembur.kategori', 'Kantor')
            ->orderBy('t_register_lembur.tanggal', 'desc')
            ->orderBy('t_register_lembur.id', 'desc')
            ->get();

        $targetId = $request->query('last_id');
        $displaySummary = null;

        if ($targetId) {
            $displaySummary = $daftarLembur->firstWhere('id', $targetId);
        }

        if (!$displaySummary && $daftarLembur->isNotEmpty()) {
            $displaySummary = $daftarLembur->first();
        }

        $summaryData = [
            'pegawai'       => $displaySummary ? ($displaySummary->nama_pegawai ?: ($displaySummary->pegawai_master ?? '—')) : '—',
            'tanggal'       => $displaySummary ? $displaySummary->tanggal : date('Y-m-d'),
            'jam_mulai'     => $displaySummary ? substr($displaySummary->jam_mulai, 0, 5) : '—',
            'jam_selesai'   => $displaySummary ? substr($displaySummary->jam_selesai, 0, 5) : '—',
            'hari'          => $displaySummary ? $displaySummary->hari : 'Hari Kerja',
            'jenis_spl'     => $displaySummary ? $displaySummary->jenis_spl : 'SPL Jam Lembur',
            'catatan'       => $displaySummary ? ($displaySummary->catatan ?? '') : '',
            'durasi_lembur' => $displaySummary ? (float)$displaySummary->durasi_lembur : 0.00,
            'uang_makan'    => $displaySummary ? (float)$displaySummary->uang_makan : 0.00,
        ];

        return view('lembur.register_otomatis', compact('pegawaiList', 'daftarLembur', 'summaryData', 'targetId'));
    }

    public function fetchPresensi(Request $request)
    {
        $idPegawai = $request->input('id_pegawai');
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');

        $pegawai = DB::table('M_PEGAWAI')->where('ID_PEGAWAI', $idPegawai)->first();
        
        $absensi = collect();
        if ($pegawai) {
            $idMesin = $pegawai->ID_PEGAWAI_MESIN;
            
            $absensi = DB::table('t_data_absensi')
                ->where(function ($q) use ($idPegawai, $idMesin) {
                    $q->where('id_pegawai', $idMesin)
                      ->orWhere('id_pegawai_mesin', $idMesin)
                      ->orWhere('id_pegawai', $idPegawai);
                })
                ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
                ->orderBy('tanggal', 'asc')
                ->get();
        }

        $hariKerjaQty = 0;
        $totalLA = 0;
        $totalLB = 0;
        $uangMakanQty = 0;
        $uangMakanLemburQty = 0;

        foreach ($absensi as $row) {
            if ($row->jam_kehadiran) {
                $hariKerjaQty++;
                $uangMakanQty++;
            }

            if ($row->jam_kehadiran && $row->jam_kepulangan) {
                $kehadiranTime = strtotime($row->tanggal . ' ' . $row->jam_kehadiran);
                $kepulanganTime = strtotime($row->tanggal . ' ' . $row->jam_kepulangan);
                $jam17Time = strtotime($row->tanggal . ' 17:00:00');
                $jam18Time = strtotime($row->tanggal . ' 18:00:00');
                $jam19Time = strtotime($row->tanggal . ' 19:00:00');
                
                if ($kepulanganTime >= strtotime($row->tanggal . ' 17:01:00')) {
                    // Mutually exclusive: 1 Day of Lembur A OR 1 Day of Lembur B
                    if ($kepulanganTime <= $jam18Time) {
                        $totalLA += 1;
                    } else {
                        $totalLB += 1;
                    }
                }
                
                if ($kepulanganTime >= $jam19Time) {
                    $uangMakanLemburQty++;
                }
            }
        }

        return response()->json([
            'hari_kerja_qty' => $hariKerjaQty,
            'uang_makan_qty' => $uangMakanQty,
            'lembur_a_qty' => $totalLA,
            'lembur_b_qty' => $totalLB,
            'uang_makan_lembur_qty' => $uangMakanLemburQty,
            'data_absensi' => $absensi
        ]);
    }

    public function store(Request $request)
    {
        // 1. Ambil Identitas Karyawan
        $idPegawai = $request->input('id_pegawai');
        $namaKaryawan = $request->input('nama_karyawan') ?? $request->input('nama_pegawai');

        if ($idPegawai) {
            $pegawai = DB::table('M_PEGAWAI')->where('ID_PEGAWAI', $idPegawai)->first();
            if ($pegawai && empty($namaKaryawan)) {
                $namaKaryawan = $pegawai->NM_PEGAWAI;
            }
        } elseif ($namaKaryawan) {
            $pegawai = DB::table('M_PEGAWAI')->where('NM_PEGAWAI', $namaKaryawan)->first();
            if ($pegawai) {
                $idPegawai = $pegawai->ID_PEGAWAI;
            }
        }

        if (empty($namaKaryawan)) {
            return back()->with('error', 'Silakan pilih atau masukkan nama pegawai terlebih dahulu!')->withInput();
        }

        // 2. Ambil Input SPL & Hitung Nominal (Sesuai Logika create.blade)
        $periode = $request->input('periode', '01 - 31 Agustus 2026');
        $lokasi = $request->input('lokasi', 'KANTOR');
        $hariKerjaQty = (float)$request->input('hari_kerja_qty', 19);

        $lemburAQty = (float)$request->input('lembur_a_qty', 0);
        $lemburARate = (float)$request->input('lembur_a_rate', 29200);
        $lemburAAmount = $lemburAQty * $lemburARate;

        $lemburBQty = (float)$request->input('lembur_b_qty', 0);
        $lemburBRate = (float)$request->input('lembur_b_rate', 34400);
        $lemburBAmount = $lemburBQty * $lemburBRate;

        $luarKotaQty = (float)$request->input('luar_kota_qty', 0);
        $luarKotaRate = (float)$request->input('luar_kota_rate', 30000);
        $luarKotaAmount = $luarKotaQty * $luarKotaRate;

        $uangMakanQty = (float)$request->input('uang_makan_qty', 0);
        $uangMakanRate = (float)$request->input('uang_makan_rate', 15000);
        $uangMakanAmount = $uangMakanQty * $uangMakanRate;

        $uangMakanLemburQty = (float)$request->input('uang_makan_lembur_qty', 0);
        $uangMakanLemburRate = (float)$request->input('uang_makan_lembur_rate', 15000);
        $uangMakanLemburAmount = $uangMakanLemburQty * $uangMakanLemburRate;

        $totalUangMakan = $uangMakanAmount + $uangMakanLemburAmount;
        $totalDurasiLembur = $lemburAQty + $lemburBQty;
        $totalIdr = $lemburAAmount + $lemburBAmount + $luarKotaAmount + $totalUangMakan;

        // Validasi jika dikirimkan jam_mulai dan jam_selesai secara spesifik
        $jamMulai = $request->input('jam_mulai', '17:00');
        $jamSelesai = $request->input('jam_selesai', '20:00');
        $tanggal = $request->input('tanggal', date('Y-m-d'));
        $hari = $request->input('hari', 'Hari Kerja');
        $jenisSpl = $request->input('jenis_spl', 'SPL Jam Lembur (LA & LB)');

        if ($request->filled('jam_mulai') && $request->filled('jam_selesai')) {
            $mulaiSec = strtotime($tanggal . ' ' . $jamMulai);
            $selesaiSec = strtotime($tanggal . ' ' . $jamSelesai);
            if ($selesaiSec > $mulaiSec) {
                $jam17Sec = strtotime($tanggal . ' 17:00:00');
                $jamMulaiLemburSec = max($mulaiSec, $jam17Sec);
                if ($totalDurasiLembur <= 0 && $selesaiSec > $jamMulaiLemburSec) {
                    $totalDurasiLembur = round(($selesaiSec - $jamMulaiLemburSec) / 3600, 2);
                }
            }
        }

        $catatanInput = $request->input('catatan');
        $catatanDefault = "SPL: {$periode} | Lokasi: {$lokasi} | LA: {$lemburAQty}, LB: {$lemburBQty}, LK: {$luarKotaQty} | Total IDR: Rp " . number_format($totalIdr, 0, ',', '.');
        $catatan = !empty($catatanInput) ? $catatanInput : $catatanDefault;

        $lembur = RegisterLembur::create([
            'kategori' => $request->input('kategori', 'Harian'),
            'id_pegawai' => $idPegawai,
            'nama_pegawai' => $namaKaryawan,
            'tanggal' => $tanggal,
            'hari' => $hari,
            'jam_mulai' => $jamMulai,
            'jam_selesai' => $jamSelesai,
            'jenis_spl' => $jenisSpl,
            'catatan' => $catatan,
            'durasi_lembur' => $totalDurasiLembur,
            'uang_makan' => $totalUangMakan
        ]);

        $routeRedirect = $request->input('kategori') == 'Kantor' ? 'lembur.register_otomatis' : 'lembur.register';

        return redirect()->route($routeRedirect, ['last_id' => $lembur->id])
            ->with('success', "Data SPL Lembur untuk {$namaKaryawan} berhasil disimpan (Total IDR: Rp " . number_format($totalIdr, 0, ',', '.') . ")!");
    }

    public function cetakSlip($id)
    {
        $lembur = RegisterLembur::findOrFail($id);

        $pegawai = null;
        if ($lembur->id_pegawai) {
            $pegawai = DB::table('M_PEGAWAI')->where('ID_PEGAWAI', $lembur->id_pegawai)->first();
        }

        $noKaryawan = $pegawai && $pegawai->ID_PEGAWAI_MESIN 
            ? $pegawai->ID_PEGAWAI_MESIN 
            : ($lembur->id_pegawai ? ('KRY-' . str_pad($lembur->id_pegawai, 3, '0', STR_PAD_LEFT)) : ('SPL-' . str_pad($lembur->id, 3, '0', STR_PAD_LEFT)));
        
        $namaKaryawan = $lembur->nama_pegawai ?: ($pegawai->NM_PEGAWAI ?? 'Karyawan #' . $lembur->id);

        // Default values
        $periode = \Carbon\Carbon::parse($lembur->tanggal)->translatedFormat('d F Y');
        $lokasi = 'KANTOR';
        $laQty = 0;
        $lbQty = 0;
        $lkQty = 0;
        $hariKerjaQty = 1;
        $uangMakanQty = 0;
        $uangMakanLemburQty = 0;

        $catatan = $lembur->catatan ?? '';
        
        // Parse metadata if available in catatan: "SPL: {periode} | Lokasi: {lokasi} | LA: {la}, LB: {lb}, LK: {lk} | Total IDR: Rp {total}"
        if (preg_match('/SPL:\s*([^\|]+)/i', $catatan, $m)) {
            $periode = trim($m[1]);
        }
        if (preg_match('/Lokasi:\s*([^\|]+)/i', $catatan, $m)) {
            $lokasi = trim($m[1]);
        }
        if (preg_match('/LA:\s*([0-9\.]+)/i', $catatan, $m)) {
            $laQty = (float)$m[1];
        }
        if (preg_match('/LB:\s*([0-9\.]+)/i', $catatan, $m)) {
            $lbQty = (float)$m[1];
        }
        if (preg_match('/LK:\s*([0-9\.]+)/i', $catatan, $m)) {
            $lkQty = (float)$m[1];
        }

        // If not parsed from formatted catatan, deduce reasonably from row fields
        $rateLA = 29200;
        $rateLB = 34400;
        $rateLK = 30000;
        $rateUM = 15000;

        if ($laQty == 0 && $lbQty == 0 && $lkQty == 0 && (float)$lembur->durasi_lembur > 0) {
            $durasi = (float)$lembur->durasi_lembur;
            if (stripos($lembur->jenis_spl, 'Luar Kota') !== false) {
                $lkQty = $durasi;
            } elseif (stripos($lembur->jenis_spl, 'Lembur B') !== false) {
                $lbQty = $durasi;
            } elseif (stripos($lembur->jenis_spl, 'Lembur A') !== false) {
                $laQty = $durasi;
            } else {
                // Default hybrid: 1st hour LA, rest LB
                if ($durasi <= 1) {
                    $laQty = $durasi;
                } else {
                    $laQty = 1;
                    $lbQty = $durasi - 1;
                }
            }
        }

        $totalUangMakanNominal = (float)$lembur->uang_makan;
        if ($totalUangMakanNominal > 0) {
            // Check if multiple of 15,000
            $totalPorsi = round($totalUangMakanNominal / $rateUM);
            if ($totalPorsi >= 2) {
                $uangMakanQty = 1;
                $uangMakanLemburQty = $totalPorsi - 1;
            } else {
                $uangMakanQty = $totalPorsi;
                $uangMakanLemburQty = 0;
            }
        }

        $amtLA = $laQty * $rateLA;
        $amtLB = $lbQty * $rateLB;
        $amtLK = $lkQty * $rateLK;
        $amtUM = $uangMakanQty * $rateUM;
        $amtUML = $uangMakanLemburQty * $rateUM;
        $totalIdr = $amtLA + $amtLB + $amtLK + $amtUM + $amtUML;

        $data = [
            'periode' => $periode,
            'karyawan' => [
                'no' => $noKaryawan,
                'nama' => $namaKaryawan
            ],
            'lokasi' => $lokasi,
            'hari_kerja' => ['jam' => $hariKerjaQty],
            'lembur_a' => [
                'qty' => $laQty,
                'rate' => $rateLA,
                'amount' => $amtLA
            ],
            'lembur_b' => [
                'qty' => $lbQty,
                'rate' => $rateLB,
                'amount' => $amtLB
            ],
            'luar_kota' => [
                'qty' => $lkQty,
                'rate' => $rateLK,
                'amount' => $amtLK
            ],
            'uang_makan' => [
                'qty' => $uangMakanQty,
                'rate' => $rateUM,
                'amount' => $amtUM
            ],
            'uang_makan_lembur' => [
                'qty' => $uangMakanLemburQty,
                'rate' => $rateUM,
                'amount' => $amtUML
            ],
            'total_idr' => $totalIdr,
            'lembur_item' => $lembur
        ];

        return view('laporan.spl-rekap', compact('data'));
    }

    public function syncLemburOtomatis(Request $request)
    {
        $tanggalMulai = $request->input('tanggal_mulai', date('Y-m-01'));
        $tanggalSelesai = $request->input('tanggal_selesai', date('Y-m-t'));
        
        $absensi = DB::table('t_data_absensi')
            ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
            ->whereNotNull('jam_kehadiran')
            ->whereNotNull('jam_kepulangan')
            ->get();
            
        $syncCount = 0;
        
        foreach ($absensi as $row) {
            $kehadiranTime = strtotime($row->tanggal . ' ' . $row->jam_kehadiran);
            $kepulanganTime = strtotime($row->tanggal . ' ' . $row->jam_kepulangan);
            $jam17Time = strtotime($row->tanggal . ' 17:00:00');
            $jam18Time = strtotime($row->tanggal . ' 18:00:00');
            $jam19Time = strtotime($row->tanggal . ' 19:00:00');
            
            $dayOfWeek = date('N', strtotime($row->tanggal));
            $isWeekend = ($dayOfWeek >= 6);
            
            // HARI KERJA (Senin - Jumat)
            if (!$isWeekend) {
                if ($kepulanganTime > strtotime($row->tanggal . ' 17:00:00')) {
                    $jenisSpl = '';
                    $durasiLembur = 1; // 1 Hari Lembur
                    
                    // Kriteria Mutually Exclusive Lembur A / B (Per Hari)
                    if ($kepulanganTime <= $jam18Time) {
                        $jenisSpl = 'Lembur A';
                    } else {
                        $jenisSpl = 'Lembur B';
                    }
                    
                    $uangMakan = ($kepulanganTime >= $jam19Time) ? 15000 : 0;
                    
                    if ($jenisSpl != '') {
                        // Resolve id_pegawai from M_PEGAWAI
                        $peg = DB::table('M_PEGAWAI')->where('ID_PEGAWAI_MESIN', $row->id_pegawai)->first();
                        $realIdPegawai = $peg ? $peg->ID_PEGAWAI : $row->id_pegawai;
                        $namaKaryawan = $peg ? $peg->NM_PEGAWAI : $row->nama_pegawai;
                        
                        $hari = ['Senin','Selasa','Rabu','Kamis','Jumat'][$dayOfWeek - 1];
                        
                        RegisterLembur::updateOrCreate(
                            ['id_pegawai' => $realIdPegawai, 'tanggal' => $row->tanggal, 'kategori' => 'Kantor'],
                            [
                                'nama_pegawai' => $namaKaryawan,
                                'hari' => $hari,
                                'jam_mulai' => '17:00:00',
                                'jam_selesai' => $row->jam_kepulangan,
                                'jenis_spl' => $jenisSpl,
                                'catatan' => 'Auto Sync ' . $jenisSpl . ' (1 Hari)',
                                'durasi_lembur' => 1,
                                'uang_makan' => $uangMakan
                            ]
                        );
                        $syncCount++;
                    }
                }
            }
        }
        
        return back()->with('success', "Proses sinkronisasi berhasil. Total {$syncCount} data lembur (Mutually Exclusive) telah di-generate/di-update.");
    }

    public function destroy($id)
    {
        RegisterLembur::findOrFail($id)->delete();
        return back()->with('success', 'Data register lembur berhasil dihapus.');
    }
}
