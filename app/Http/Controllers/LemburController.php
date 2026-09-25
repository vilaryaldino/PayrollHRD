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

        return redirect()->route('lembur.register', ['last_id' => $lembur->id])
            ->with('success', "Data SPL Lembur untuk {$namaKaryawan} berhasil disimpan (Total IDR: Rp " . number_format($totalIdr, 0, ',', '.') . ")!");
    }

    public function destroy($id)
    {
        RegisterLembur::findOrFail($id)->delete();
        return back()->with('success', 'Data register lembur berhasil dihapus.');
    }
}
