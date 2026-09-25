<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SplController extends Controller
{
    /**
     * Menyimpan data Surat Perintah Lembur (SPL)
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'overtime_type' => 'required|in:LEMBUR_A,LEMBUR_B,LUAR_KOTA',
            'location' => 'nullable|string',
            'description' => 'required|string',
            'participants' => 'required|array',
            'participants.*.employee_id' => 'required|integer',
            'participants.*.meal_allowance' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            // Generate SPL Number (Example format: SPL/UBP/YYYY/MM/XXXX)
            $year = date('Y', strtotime($request->date));
            $month = date('m', strtotime($request->date));
            $count = \App\Models\OvertimeOrder::whereYear('date', $year)->whereMonth('date', $month)->count() + 1;
            $splNumber = sprintf("SPL/UBP/%s/%s/%04d", $year, $month, $count);

            $spl = \App\Models\OvertimeOrder::create([
                'spl_number' => $splNumber,
                'date' => $request->date,
                'overtime_type' => $request->overtime_type,
                'location' => $request->location ?? 'KANTOR',
                'description' => $request->description,
                'status' => 'DRAFT', // Default status
                'created_by' => auth()->id() ?? 1, // Mock user ID for now
            ]);

            foreach ($request->participants as $participant) {
                \App\Models\OvertimeOrderParticipant::create([
                    'overtime_order_id' => $spl->id,
                    'employee_id' => $participant['employee_id'],
                    'meal_allowance' => $participant['meal_allowance'] ?? false,
                    'notes' => $participant['notes'] ?? null,
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'SPL berhasil dibuat', 'data' => $spl], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membuat SPL', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Tampilkan halaman form input SPL
     */
    public function create()
    {
        return view('spl.create');
    }

    /**
     * Proses kalkulasi dari form input dan tampilkan rekap
     */
    public function calculate(Request $request)
    {
        $data = [
            'periode' => $request->periode ?? '01 - 31 Agustus 2026',
            'karyawan' => [
                'no' => $request->no_karyawan ?? 'KRY-001',
                'nama' => $request->nama_karyawan ?? 'John Doe'
            ],
            'lokasi' => $request->lokasi ?? 'KANTOR',
            'hari_kerja' => ['jam' => $request->hari_kerja_qty ?? 19],
            'lembur_a' => [
                'qty' => $request->lembur_a_qty ?? 2,
                'rate' => $request->lembur_a_rate ?? 29200,
                'amount' => ($request->lembur_a_qty ?? 2) * ($request->lembur_a_rate ?? 29200)
            ],
            'lembur_b' => [
                'qty' => $request->lembur_b_qty ?? 4,
                'rate' => $request->lembur_b_rate ?? 34400,
                'amount' => ($request->lembur_b_qty ?? 4) * ($request->lembur_b_rate ?? 34400)
            ],
            'luar_kota' => [
                'qty' => $request->luar_kota_qty ?? 0,
                'rate' => $request->luar_kota_rate ?? 30000,
                'amount' => ($request->luar_kota_qty ?? 0) * ($request->luar_kota_rate ?? 30000)
            ],
            'uang_makan' => [
                'qty' => $request->uang_makan_qty ?? 19,
                'rate' => $request->uang_makan_rate ?? 15000,
                'amount' => ($request->uang_makan_qty ?? 19) * ($request->uang_makan_rate ?? 15000)
            ],
            'uang_makan_lembur' => [
                'qty' => $request->uang_makan_lembur_qty ?? 2,
                'rate' => $request->uang_makan_lembur_rate ?? 15000,
                'amount' => ($request->uang_makan_lembur_qty ?? 2) * ($request->uang_makan_lembur_rate ?? 15000)
            ],
        ];

        // Hitung total IDR
        $totalIdr = $data['lembur_a']['amount'] + 
                    $data['lembur_b']['amount'] + 
                    $data['luar_kota']['amount'] + 
                    $data['uang_makan']['amount'] + 
                    $data['uang_makan_lembur']['amount'];
                    
        $data['total_idr'] = $totalIdr;

        return view('laporan.spl-rekap', compact('data'));
    }

    /**
     * API Response JSON for Testing
     */
    public function apiRekapitulasi()
    {
        return response()->json([
            'lembur_a' => [
                'description' => '2 org @ 29.200 = 58.400',
                'amount' => 58400
            ],
            'lembur_b' => [
                'description' => '4 org @ 34.400 = 137.600',
                'amount' => 137600
            ],
            'uang_makan' => [
                'description' => '19 hari @ 15.000 = 285.000',
                'amount' => 285000
            ],
            'uang_makan_lembur' => [
                'description' => '2 org @ 15.000 = 30.000',
                'amount' => 30000
            ],
            'total' => 511000
        ]);
    }
}
