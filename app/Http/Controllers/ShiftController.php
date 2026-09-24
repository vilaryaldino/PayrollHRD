<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = DB::table('m_shift')->orderBy('JAM_MULAI', 'ASC')->get();
        return view('shift.index', compact('shifts'));
    }

    public function store(Request $request)
    {
        DB::table('m_shift')->insert([
            'SHIFT_CODE' => strtoupper(trim($request->input('shift_code'))),
            'NAMA_SHIFT' => trim($request->input('nama_shift')),
            'JAM_MULAI' => $request->input('jam_mulai'),
            'JAM_SELESAI' => $request->input('jam_selesai'),
            'JAM_AWAL' => $request->input('jam_awal'),
            'JAM_AKHIR' => $request->input('jam_akhir'),
            'IS_OVERNIGHT' => $request->has('is_overnight') ? 1 : 0,
            'WARNA_LABEL' => $request->input('warna', '#3B82F6')
        ]);

        return redirect()->route('shift.index')->with('success', 'Konfigurasi shift berhasil ditambahkan ke database.');
    }

    public function update(Request $request, $id)
    {
        DB::table('m_shift')
            ->where('ID_JADWAL', $id)
            ->update([
                'SHIFT_CODE' => strtoupper(trim($request->input('shift_code'))),
                'NAMA_SHIFT' => trim($request->input('nama_shift')),
                'JAM_MULAI' => $request->input('jam_mulai'),
                'JAM_SELESAI' => $request->input('jam_selesai'),
                'JAM_AWAL' => $request->input('jam_awal'),
                'JAM_AKHIR' => $request->input('jam_akhir'),
                'IS_OVERNIGHT' => $request->has('is_overnight') ? 1 : 0,
                'WARNA_LABEL' => $request->input('warna', '#3B82F6')
            ]);

        return redirect()->route('shift.index')->with('success', 'Konfigurasi shift berhasil diperbarui di database.');
    }

    public function destroy($id)
    {
        DB::table('m_shift')->where('ID_JADWAL', $id)->delete();
        return redirect()->route('shift.index')->with('success', 'Konfigurasi shift berhasil dihapus dari database.');
    }
}
