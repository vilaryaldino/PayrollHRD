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
        $absensis = $query->paginate(15)->withQueryString();

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
            ['id_mesin' => $request->id_pegawai, 'tanggal' => $request->tanggal],
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
}
