<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalPegawai = DB::table('m_pegawai')->count();
        $totalAktif = DB::table('m_pegawai')->where('IS_AKTIF', 1)->count();
        $totalShift = DB::table('m_shift')->count();
        $totalLibur = DB::table('m_libur_nasional')->whereYear('TANGGAL', date('Y'))->count();
        $jadwalHariIni = DB::table('t_jadwal_kerja')
            ->whereDate('TANGGAL', date('Y-m-d'))
            ->where('STATUS_KERJA', 'Kerja')
            ->count();

        $nextLibur = DB::table('m_libur_nasional')
            ->select('KETERANGAN', 'TANGGAL')
            ->whereDate('TANGGAL', '>=', date('Y-m-d'))
            ->orderBy('TANGGAL', 'ASC')
            ->first();

        return view('dashboard', compact(
            'totalPegawai',
            'totalAktif',
            'totalShift',
            'totalLibur',
            'jadwalHariIni',
            'nextLibur'
        ));
    }
}
