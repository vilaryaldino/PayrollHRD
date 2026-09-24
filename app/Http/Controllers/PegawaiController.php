<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PegawaiController extends Controller
{
    public function index(Request $request)
    {
        // Get Filters
        $filterDivisi = $request->input('filter_divisi');
        $filterStatus = $request->input('filter_status');
        $searchQuery = $request->input('search');

        // Master Data
        $masterDivisi = DB::table('m_divisi')->orderBy('NAMA_DIVISI', 'ASC')->get();
        $masterKelompok = DB::table('m_kelompok')->orderBy('NAMA_KELOMPOK', 'ASC')->get();

        // Main Query
        $query = DB::table('m_pegawai as p')
            ->leftJoin('m_divisi as d', 'p.ID_DIVISI', '=', 'd.ID_DIVISI')
            ->leftJoin('m_kelompok as k', 'p.ID_KELOMPOK', '=', 'k.ID_KELOMPOK')
            ->select('p.*', 'd.NAMA_DIVISI', 'k.NAMA_KELOMPOK');

        if ($filterDivisi !== null && $filterDivisi !== '') {
            $query->where('p.ID_DIVISI', $filterDivisi);
        }

        if ($filterStatus !== null && $filterStatus !== '') {
            $query->where('p.IS_AKTIF', $filterStatus);
        }

        if ($searchQuery) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('p.NM_PEGAWAI', 'LIKE', '%' . $searchQuery . '%')
                  ->orWhere('p.ID_PEGAWAI_MESIN', 'LIKE', '%' . $searchQuery . '%')
                  ->orWhere('p.ALAMAT', 'LIKE', '%' . $searchQuery . '%');
            });
        }

        $pegawaiList = $query->orderBy('p.ID_PEGAWAI', 'ASC')->get();

        // Statistics
        $totalPegawai = DB::table('m_pegawai')->count();
        $totalAktif = DB::table('m_pegawai')->where('IS_AKTIF', 1)->count();
        $totalNonAktif = DB::table('m_pegawai')->where('IS_AKTIF', 0)->count();
        $totalStaff = DB::table('m_pegawai')->where('JENIS_PEGAWAI', 'Staff')->count();
        $totalHarian = DB::table('m_pegawai')->where('JENIS_PEGAWAI', 'Harian')->count();

        $avatarColors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#14B8A6'];

        return view('pegawai.index', compact(
            'pegawaiList',
            'masterDivisi',
            'masterKelompok',
            'totalPegawai',
            'totalAktif',
            'totalNonAktif',
            'totalStaff',
            'totalHarian',
            'avatarColors',
            'searchQuery',
            'filterDivisi',
            'filterStatus'
        ));
    }

    public function store(Request $request)
    {
        $idMesin = $request->input('id_pegawai_mesin') ?: null;
        $nama = $request->input('nama');
        $idDivisi = $request->input('id_divisi');
        $idKel = $request->input('id_kelompok') ?: null;
        $jenis = $request->input('jenis');
        $isAktif = $request->input('is_aktif', 1);
        $noTelp = $request->input('no_telp') ?: null;
        $alamat = $request->input('alamat') ?: null;

        if ($idMesin) {
            $pegAktif = DB::table('m_pegawai')
                ->where('ID_PEGAWAI_MESIN', $idMesin)
                ->where('IS_AKTIF', 1)
                ->first();

            if ($pegAktif) {
                return redirect()->route('pegawai.index')->with('error', "ID Mesin Absen '$idMesin' sedang aktif digunakan oleh pegawai: {$pegAktif->NM_PEGAWAI}. Hanya ID Mesin dari pegawai yang sudah Resign/Non-Aktif yang dapat dialihkan!");
            }
        }

        DB::table('m_pegawai')->insert([
            'ID_PEGAWAI_MESIN' => $idMesin,
            'NM_PEGAWAI' => $nama,
            'ID_DIVISI' => $idDivisi,
            'ID_KELOMPOK' => $idKel,
            'JENIS_PEGAWAI' => $jenis,
            'IS_AKTIF' => $isAktif,
            'NO_TELP_HP' => $noTelp,
            'ALAMAT' => $alamat
        ]);

        return redirect()->route('pegawai.index')->with('success', 'Data pegawai berhasil ditambahkan ke database.');
    }

    public function update(Request $request, $id)
    {
        $idMesin = $request->input('id_pegawai_mesin') ?: null;
        $nama = $request->input('nama');
        $idDivisi = $request->input('id_divisi');
        $idKel = $request->input('id_kelompok') ?: null;
        $jenis = $request->input('jenis');
        $isAktif = $request->input('is_aktif', 0);
        $noTelp = $request->input('no_telp') ?: null;
        $alamat = $request->input('alamat') ?: null;

        if ($idMesin && $isAktif == 1) {
            $pegAktif = DB::table('m_pegawai')
                ->where('ID_PEGAWAI_MESIN', $idMesin)
                ->where('IS_AKTIF', 1)
                ->where('ID_PEGAWAI', '!=', $id)
                ->first();

            if ($pegAktif) {
                return redirect()->route('pegawai.index')->with('error', "ID Mesin Absen '$idMesin' sedang aktif digunakan oleh pegawai: {$pegAktif->NM_PEGAWAI}. Tidak dapat menetapkan ID Mesin yang masih aktif ke pegawai lain!");
            }
        }

        DB::table('m_pegawai')
            ->where('ID_PEGAWAI', $id)
            ->update([
                'ID_PEGAWAI_MESIN' => $idMesin,
                'NM_PEGAWAI' => $nama,
                'ID_DIVISI' => $idDivisi,
                'ID_KELOMPOK' => $idKel,
                'JENIS_PEGAWAI' => $jenis,
                'IS_AKTIF' => $isAktif,
                'NO_TELP_HP' => $noTelp,
                'ALAMAT' => $alamat
            ]);

        return redirect()->route('pegawai.index')->with('success', 'Data pegawai berhasil diperbarui di database.');
    }

    public function destroy($id)
    {
        DB::table('m_pegawai')->where('ID_PEGAWAI', $id)->delete();
        return redirect()->route('pegawai.index')->with('success', 'Data pegawai berhasil dihapus dari database.');
    }
}
