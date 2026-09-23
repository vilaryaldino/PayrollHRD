<?php

namespace App\Imports;

use App\Models\DataAbsensi;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class AbsensiImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        // 1. Ambil data master pegawai (id_mesin => nama_pegawai) ke memori 
        // agar kita tidak melakukan Query N+1 di dalam looping.
        // Asumsi tabel master bernama M_PEGAWAI, kolom 'finger_id' dan 'NM_PEGAWAI'.
        // Catatan: Jika struktur database Anda berbeda, sesuaikan nama kolom di sini.
        try {
            $masterPegawai = DB::table('M_PEGAWAI')->pluck('NM_PEGAWAI', 'finger_id')->toArray();
        } catch (\Exception $e) {
            // Jika tabel M_PEGAWAI belum ada, fallback ke array kosong
            $masterPegawai = [];
        }

        foreach ($rows as $row) {
            // Hindari row kosong
            if (empty($row['pin_mesin']) || empty($row['tanggal'])) {
                continue;
            }

            $pinMesin = (string) $row['pin_mesin'];
            
            // 2. Logika Mapping
            $namaPegawai = $masterPegawai[$pinMesin] ?? 'Pegawai Tidak Dikenal / Belum Terdaftar';
            
            // 3. Konversi format tanggal Excel ke Y-m-d (Jika di excel berformat date)
            // Jika format di excel adalah text (misal: '2023-10-15'), cukup gunakan: $tanggal = $row['tanggal'];
            $tanggal = is_numeric($row['tanggal']) 
                ? Date::excelToDateTimeObject($row['tanggal'])->format('Y-m-d') 
                : date('Y-m-d', strtotime($row['tanggal']));

            $jamIn = !empty($row['jam_in']) ? $this->formatTime($row['jam_in']) : null;
            $jamOut = !empty($row['jam_out']) ? $this->formatTime($row['jam_out']) : null;

            // 4. Insert atau Update (menghindari data duplicate di hari yang sama)
            DataAbsensi::updateOrCreate(
                [
                    'id_pegawai' => $pinMesin,
                    'tanggal'    => $tanggal,
                ],
                [
                    'nama_pegawai'   => $namaPegawai,
                    'jam_kehadiran'  => $jamIn,
                    'jam_kepulangan' => $jamOut,
                    'lokasi_absen'   => $row['lokasi'] ?? 'Kantor Pusat',
                ]
            );
        }
    }

    // Helper function untuk format jam
    private function formatTime($timeVal)
    {
        if (is_numeric($timeVal)) {
            return Date::excelToDateTimeObject($timeVal)->format('H:i:s');
        }
        return date('H:i:s', strtotime($timeVal));
    }
}
