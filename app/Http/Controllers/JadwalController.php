<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class JadwalController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil Master Data
        $shifts = DB::table('m_shift')->orderBy('JAM_MULAI', 'ASC')->get();
        $shiftMap = [];
        foreach ($shifts as $s) {
            $shiftMap[$s->ID_JADWAL] = $s;
        }

        $masterDivisi = DB::table('m_divisi')->orderBy('NAMA_DIVISI', 'ASC')->get();

        // 2. Navigasi Rentang Kalender
        $selectedDate = $request->input('date', date('Y-m-d'));
        if (empty($selectedDate)) $selectedDate = date('Y-m-d');
        
        $filterDivisi = $request->input('divisi');
        $filterJenis = $request->input('jenis');

        $dt = Carbon::parse($selectedDate);
        $startOfWeek = $dt->copy()->startOfWeek(); // Senin

        $weekDays = [];
        $tempDt = $startOfWeek->copy();
        for ($i = 0; $i < 7; $i++) {
            $weekDays[] = [
                'tanggal' => $tempDt->format('Y-m-d'),
                'hari' => $tempDt->locale('id')->translatedFormat('l'),
                'tgl_angka' => $tempDt->translatedFormat('d M'),
                'is_today' => $tempDt->isToday()
            ];
            $tempDt->addDay();
        }

        $startDateStr = $weekDays[0]['tanggal'];
        $endDateStr = $weekDays[6]['tanggal'];

        $prevWeek = $startOfWeek->copy()->subDays(7)->format('Y-m-d');
        $nextWeek = $startOfWeek->copy()->addDays(7)->format('Y-m-d');

        // 3. Libur Nasional Map
        $liburRows = DB::table('m_libur_nasional')
            ->whereBetween('TANGGAL', [$startDateStr, $endDateStr])
            ->get();
        
        $liburMap = [];
        foreach ($liburRows as $lr) {
            $liburMap[$lr->TANGGAL] = $lr->KETERANGAN;
        }

        // 4. Pegawai List
        $queryPeg = DB::table('m_pegawai as p')
            ->leftJoin('m_divisi as d', 'p.ID_DIVISI', '=', 'd.ID_DIVISI')
            ->where('p.IS_AKTIF', 1)
            ->select('p.*', 'd.NAMA_DIVISI');
        
        if ($filterDivisi !== null && $filterDivisi !== '') {
            $queryPeg->where('p.ID_DIVISI', $filterDivisi);
        }
        if ($filterJenis !== null && $filterJenis !== '') {
            $queryPeg->where('p.JENIS_PEGAWAI', $filterJenis);
        }
        $pegawaiList = $queryPeg->orderBy('p.ID_PEGAWAI', 'ASC')->get();

        // 5. Jadwal Map
        $jadwalRows = DB::table('t_jadwal_kerja')
            ->whereBetween('TANGGAL', [$startDateStr, $endDateStr])
            ->get();
        
        $jadwalMap = [];
        foreach ($jadwalRows as $j) {
            $key = $j->ID_PEGAWAI . '_' . $j->TANGGAL;
            $jadwalMap[$key] = $j;
        }

        return view('jadwal.index', compact(
            'shifts', 'shiftMap', 'masterDivisi', 'selectedDate', 'filterDivisi', 
            'filterJenis', 'startOfWeek', 'weekDays', 'startDateStr', 'endDateStr', 
            'prevWeek', 'nextWeek', 'liburMap', 'pegawaiList', 'jadwalMap'
        ));
    }

    public function setJadwal(Request $request)
    {
        $idPegawai = $request->input('id_pegawai');
        $tanggal = $request->input('tanggal');
        $idShift = $request->input('id_jadwal') ?: null;
        $status = trim($request->input('status_kerja'));
        $ket = $request->input('keterangan') ?: null;

        if ($status === 'Hapus') {
            DB::table('t_jadwal_kerja')
                ->where('ID_PEGAWAI', $idPegawai)
                ->where('TANGGAL', $tanggal)
                ->delete();
        } else {
            if ($status !== 'Kerja') {
                $idShift = null;
            }

            DB::table('t_jadwal_kerja')->updateOrInsert(
                ['ID_PEGAWAI' => $idPegawai, 'TANGGAL' => $tanggal],
                [
                    'ID_JADWAL' => $idShift,
                    'STATUS_KERJA' => $status,
                    'KETERANGAN' => $ket,
                    'IS_OVERWRITE' => 1,
                    'ASSIGNED_BY' => 'admin_hrd',
                    'UPDATED_AT' => now()
                ]
            );
        }

        return redirect()->route('jadwal.index', ['date' => $request->input('week_ref')])
            ->with('success', 'Jadwal kerja pegawai berhasil disimpan ke MySQL.');
    }

    public function bulkSet(Request $request)
    {
        $idPegawai = $request->input('id_pegawai');
        $idShift = $request->input('id_jadwal') ?: null;
        $status = trim($request->input('status_kerja'));
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($status !== 'Kerja') {
            $idShift = null;
        }

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $period = \Carbon\CarbonPeriod::create($start, '1 day', $end);

        foreach ($period as $dt) {
            $tgl = $dt->format('Y-m-d');
            DB::table('t_jadwal_kerja')->updateOrInsert(
                ['ID_PEGAWAI' => $idPegawai, 'TANGGAL' => $tgl],
                [
                    'ID_JADWAL' => $idShift,
                    'STATUS_KERJA' => $status,
                    'KETERANGAN' => 'Penugasan Kolektif',
                    'IS_OVERWRITE' => 1,
                    'ASSIGNED_BY' => 'admin_hrd',
                    'UPDATED_AT' => now()
                ]
            );
        }

        return redirect()->route('jadwal.index', ['date' => $request->input('week_ref')])
            ->with('success', 'Penugasan shift kolektif berhasil disimpan ke MySQL.');
    }
}
