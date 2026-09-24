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
        
        $liburList = $query->orderBy('TANGGAL', 'ASC')->get();

        $tahunList = DB::table('m_libur_nasional')
            ->selectRaw('DISTINCT YEAR(TANGGAL) AS thn')
            ->orderBy('thn', 'ASC')
            ->pluck('thn')
            ->toArray();

        if (empty($tahunList)) {
            $tahunList = [date('Y')];
        }

        $totalLibur = DB::table('m_libur_nasional')->count();
        $totalNasional = DB::table('m_libur_nasional')->where('JENIS_LIBUR', 'Nasional')->count();
        $totalCuti = DB::table('m_libur_nasional')->where('JENIS_LIBUR', 'Cuti Bersama')->count();
        $totalUpcoming = DB::table('m_libur_nasional')->where('TANGGAL', '>=', date('Y-m-d'))->count();

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
