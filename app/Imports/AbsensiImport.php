<?php

namespace App\Imports;

use App\Models\DataAbsensi;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;

class AbsensiImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        // 1. Ambil data master pegawai (id_mesin => nama_pegawai & id_pegawai) ke memori 
        // agar kita tidak melakukan Query N+1 di dalam looping.
        try {
            $masterQuery = DB::table('M_PEGAWAI')->get(['ID_PEGAWAI_MESIN', 'ID_PEGAWAI', 'NM_PEGAWAI']);
            $masterPegawai = [];
            foreach ($masterQuery as $mp) {
                $masterPegawai[$mp->ID_PEGAWAI_MESIN] = [
                    'id' => $mp->ID_PEGAWAI,
                    'nama' => $mp->NM_PEGAWAI
                ];
            }
        } catch (\Exception $e) {
            $masterPegawai = [];
        }

        foreach ($rows as $row) {
            // Cek apakah file excel menggunakan format baru (Waktu Absensi)
            if (isset($row['waktu_absensi'])) {
                if (empty($row['id']) || empty($row['waktu_absensi'])) {
                    continue;
                }

                $pinMesin = (string) $row['id'];
                $dbPegawai = $masterPegawai[$pinMesin] ?? null;
                $namaPegawai = !empty($row['nama']) ? $row['nama'] : ($dbPegawai['nama'] ?? 'Pegawai Tidak Dikenal / Belum Terdaftar');
                $idPegawaiSystem = $dbPegawai['id'] ?? null;
                
                $datetimeVal = $row['waktu_absensi'];
                try {
                    if (is_numeric($datetimeVal)) {
                        $datetime = Date::excelToDateTimeObject($datetimeVal);
                    } else {
                        $datetime = Carbon::parse($datetimeVal);
                    }
                    $tanggal = $datetime->format('Y-m-d');
                    $jam = $datetime->format('H:i:s');
                } catch (\Exception $e) {
                    continue; // Skip jika format tanggal tidak valid
                }

                $lokasi = !empty($row['kantor']) ? $row['kantor'] : (!empty($row['nama_perangkat']) ? $row['nama_perangkat'] : 'Kantor Pusat');

                // Cari data absensi pada tanggal tersebut
                $absensi = DataAbsensi::where('id_pegawai_mesin', $pinMesin)
                                      ->where('tanggal', $tanggal)
                                      ->first();

                if ($absensi) {
                    // Update jam_kehadiran / jam_kepulangan berdasarkan urutan waktu
                    if (empty($absensi->jam_kehadiran)) {
                        $absensi->jam_kehadiran = $jam;
                    } else {
                        if ($jam < $absensi->jam_kehadiran) {
                            if (empty($absensi->jam_kepulangan) || $absensi->jam_kehadiran > $absensi->jam_kepulangan) {
                                $absensi->jam_kepulangan = $absensi->jam_kehadiran;
                            }
                            $absensi->jam_kehadiran = $jam;
                        } elseif ($jam > $absensi->jam_kehadiran) {
                            if (empty($absensi->jam_kepulangan) || $jam > $absensi->jam_kepulangan) {
                                $absensi->jam_kepulangan = $jam;
                            }
                        }
                    }
                    // Pertahankan nama pegawai dari master jika ada update
                    $absensi->nama_pegawai = $namaPegawai;
                    $absensi->id_pegawai = $idPegawaiSystem;
                    $absensi->save();
                } else {
                    DataAbsensi::create([
                        'id_pegawai_mesin' => $pinMesin,
                        'id_pegawai'       => $idPegawaiSystem,
                        'nama_pegawai'     => $namaPegawai,
                        'tanggal'          => $tanggal,
                        'jam_kehadiran'    => $jam,
                        'jam_kepulangan'   => null,
                        'lokasi_absen'     => $lokasi,
                    ]);
                }

            } else {
                // Format lama
                if (empty($row['pin_mesin']) || empty($row['tanggal'])) {
                    continue;
                }

                $pinMesin = (string) $row['pin_mesin'];
                $dbPegawai = $masterPegawai[$pinMesin] ?? null;
                $namaPegawai = $dbPegawai['nama'] ?? 'Pegawai Tidak Dikenal / Belum Terdaftar';
                $idPegawaiSystem = $dbPegawai['id'] ?? null;
                
                $tanggal = is_numeric($row['tanggal']) 
                    ? Date::excelToDateTimeObject($row['tanggal'])->format('Y-m-d') 
                    : date('Y-m-d', strtotime($row['tanggal']));

                $jamIn = !empty($row['jam_in']) ? $this->formatTime($row['jam_in']) : null;
                $jamOut = !empty($row['jam_out']) ? $this->formatTime($row['jam_out']) : null;

                DataAbsensi::updateOrCreate(
                    [
                        'id_pegawai_mesin' => $pinMesin,
                        'tanggal'          => $tanggal,
                    ],
                    [
                        'id_pegawai'     => $idPegawaiSystem,
                        'nama_pegawai'   => $namaPegawai,
                        'jam_kehadiran'  => $jamIn,
                        'jam_kepulangan' => $jamOut,
                        'lokasi_absen'   => $row['lokasi'] ?? 'Kantor Pusat',
                    ]
                );
            }
        }
    }

    private function formatTime($timeVal)
    {
        if (is_numeric($timeVal)) {
            return Date::excelToDateTimeObject($timeVal)->format('H:i:s');
        }
        return date('H:i:s', strtotime($timeVal));
    }
}
