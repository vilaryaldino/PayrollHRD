<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LiburController extends Controller
{
    public function index(Request $request)
    {
        $selectedYear = $request->input('year', date('Y'));

        $query = DB::table('m_libur_nasional');
        
        if ($selectedYear !== 'all') {
            $query->whereYear('TANGGAL', $selectedYear);
        }
        
        $dbLibur = $query->orderBy('TANGGAL', 'ASC')->get();
        
        // Tandai data dari database
        $dbLibur->transform(function ($item) {
            $item->IS_API = false;
            return $item;
        });

        $apiLibur = collect();
        // Jika 'all', kita ambil minimal tahun berjalan dari API
        $yearsToFetch = $selectedYear === 'all' ? [date('Y')] : [$selectedYear];
        
        foreach ($yearsToFetch as $year) {
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(5)->get("https://date.nager.at/api/v3/PublicHolidays/{$year}/ID");
                if ($response->successful()) {
                    $holidays = $response->json();
                    foreach ($holidays as $holiday) {
                        $apiLibur->push((object)[
                            'ID_LIBUR' => 'api_' . uniqid(),
                            'KETERANGAN' => $holiday['localName'] ?? $holiday['name'],
                            'TANGGAL' => $holiday['date'],
                            'JENIS_LIBUR' => 'Nasional', // Default API adalah hari libur nasional
                            'IS_DIBAYAR' => 1,
                            'IS_API' => true
                        ]);
                    }
                }
            } catch (\Exception $e) {
                // Abaikan jika API gagal (fallback ke DB saja)
            }
        }

        // Hindari duplikasi tanggal, prioritaskan custom dari database
        $dbDates = $dbLibur->pluck('TANGGAL')->toArray();
        $filteredApiLibur = $apiLibur->filter(function($item) use ($dbDates) {
            return !in_array($item->TANGGAL, $dbDates);
        });

        // Gabungkan dan urutkan berdasarkan tanggal
        $liburList = $dbLibur->concat($filteredApiLibur)->sortBy('TANGGAL')->values();

        // Hitung statistik dari data gabungan (realtime)
        $totalLibur = $liburList->count();
        $totalNasional = $liburList->where('JENIS_LIBUR', 'Nasional')->count();
        $totalCuti = $liburList->where('JENIS_LIBUR', 'Cuti Bersama')->count();
        $totalUpcoming = $liburList->where('TANGGAL', '>=', date('Y-m-d'))->count();

        // List tahun untuk filter dropdown
        $tahunList = DB::table('m_libur_nasional')
            ->selectRaw('DISTINCT YEAR(TANGGAL) AS thn')
            ->orderBy('thn', 'ASC')
            ->pluck('thn')
            ->toArray();

        // Pastikan tahun ini dan tahun yang dipilih ada di list
        $currentY = (int)date('Y');
        if (!in_array($currentY, $tahunList)) $tahunList[] = $currentY;
        if ($selectedYear !== 'all' && !in_array((int)$selectedYear, $tahunList)) $tahunList[] = (int)$selectedYear;
        sort($tahunList);

        return view('libur.index', compact(
            'liburList', 
            'selectedYear', 
            'tahunList', 
            'totalLibur', 
            'totalNasional', 
            'totalCuti', 
            'totalUpcoming'
        ));
    }

    public function store(Request $request)
    {
        DB::table('m_libur_nasional')->insert([
            'KETERANGAN' => trim($request->input('nama_libur')),
            'TANGGAL' => $request->input('tanggal'),
            'JENIS_LIBUR' => $request->input('jenis'),
            'IS_DIBAYAR' => $request->input('is_dibayar', 1)
        ]);

        return redirect()->route('libur.index')->with('success', 'Data hari libur berhasil ditambahkan ke database.');
    }

    public function update(Request $request, $id)
    {
        DB::table('m_libur_nasional')
            ->where('ID_LIBUR', $id)
            ->update([
                'KETERANGAN' => trim($request->input('nama_libur')),
                'TANGGAL' => $request->input('tanggal'),
                'JENIS_LIBUR' => $request->input('jenis'),
                'IS_DIBAYAR' => $request->input('is_dibayar', 0)
            ]);

        return redirect()->route('libur.index')->with('success', 'Data hari libur berhasil diperbarui di database.');
    }

    public function destroy($id)
    {
        DB::table('m_libur_nasional')->where('ID_LIBUR', $id)->delete();
        return redirect()->route('libur.index')->with('success', 'Data hari libur berhasil dihapus dari database.');
    }
}
