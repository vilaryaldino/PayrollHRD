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
        $request->validate([
            'id_pegawai' => 'required',
            'tanggal' => 'required|date',
            'hari' => 'required',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i',
            'jenis_spl' => 'required'
        ]);

        $idPegawai = $request->id_pegawai;
        $namaPegawai = '';
        $pegawai = DB::table('M_PEGAWAI')->where('ID_PEGAWAI', $idPegawai)->first();
        if ($pegawai) {
            $namaPegawai = $pegawai->NM_PEGAWAI;
        }

        if (empty($namaPegawai)) {
            return back()->with('error', 'Silakan pilih pegawai terlebih dahulu!')->withInput();
        }

        $mulaiSec = strtotime($request->tanggal . ' ' . $request->jam_mulai);
        $selesaiSec = strtotime($request->tanggal . ' ' . $request->jam_selesai);

        if ($selesaiSec <= $mulaiSec) {
            return back()->with('error', 'Jam selesai tidak boleh lebih kecil atau sama dengan jam mulai!')->withInput();
        }

        $jam17Sec = strtotime($request->tanggal . ' 17:00:00');
        $jam20Sec = strtotime($request->tanggal . ' 20:00:00');

        $jamMulaiLemburSec = max($mulaiSec, $jam17Sec);
        $durasiLembur = 0.00;

        if ($selesaiSec > $jamMulaiLemburSec) {
            $durasiDetik = $selesaiSec - $jamMulaiLemburSec;
            $durasiLembur = round($durasiDetik / 3600, 2);
        }

        $uangMakan = ($selesaiSec > $jam20Sec) ? 15000.00 : 0.00;

        $lembur = RegisterLembur::create([
            'id_pegawai' => $idPegawai,
            'nama_pegawai' => $namaPegawai,
            'tanggal' => $request->tanggal,
            'hari' => $request->hari,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'jenis_spl' => $request->jenis_spl,
            'catatan' => $request->catatan,
            'durasi_lembur' => $durasiLembur,
            'uang_makan' => $uangMakan
        ]);

        $uangMakanFormatted = number_format($uangMakan, 0, ',', '.');
        return redirect()->route('lembur.register', ['last_id' => $lembur->id])
            ->with('success', "Data register lembur untuk $namaPegawai berhasil disimpan ke database (Uang Makan: Rp $uangMakanFormatted)!");
    }

    public function destroy($id)
    {
        RegisterLembur::findOrFail($id)->delete();
        return back()->with('success', 'Data register lembur berhasil dihapus.');
    }
}
