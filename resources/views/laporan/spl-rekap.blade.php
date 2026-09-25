<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Rekapitulasi Lembur - PT. Usaha Bakti Perkasa</title>
    <style>
        /* CSS Print Setup & Reset */
        @page {
            size: A4 portrait;
            margin: 10mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            margin: 0;
            padding: 0;
            background-color: #fff;
            color: #000;
        }

        /* Container */
        .slip-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #000; /* border around the whole slip */
            padding: 10px;
            box-sizing: border-box;
        }

        /* Header Section */
        .header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header-left {
            display: flex;
            align-items: center;
        }
        .header-logo {
            width: 70px;
            height: 70px;
            border: 2px solid #2CA02C; /* Green hexagon border color */
            border-radius: 10px; /* approximation of the logo */
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 24px;
            color: #2CA02C;
            margin-right: 15px;
        }
        .header-company-info {
            display: flex;
            flex-direction: column;
        }
        .company-name {
            font-size: 14pt;
            font-weight: bold;
            background-color: #A0DA5E; /* Light green background */
            padding: 2px 10px;
            display: inline-block;
        }
        .company-subtitle {
            font-size: 10pt;
            margin: 3px 0;
        }
        .company-address {
            font-size: 10pt;
        }
        .header-right {
            width: 350px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 2px 5px;
            font-size: 10pt;
            vertical-align: top;
        }
        .info-table td.label {
            width: 100px;
        }
        
        /* Title */
        .report-title {
            font-size: 12pt;
            font-weight: bold;
            margin: 10px 0;
        }

        /* Main Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th, .data-table td {
            border: 1px dashed #000;
            padding: 4px 8px;
            font-size: 10pt;
        }
        .data-table th {
            text-align: center;
            font-weight: bold;
        }
        .col-group {
            width: 25px;
            text-align: center;
            font-weight: bold;
            border-right: none;
        }
        .col-item {
            width: 250px;
            border-left: none;
        }
        .col-qty {
            width: 80px;
            text-align: right;
        }
        .col-rate {
            width: 120px;
            text-align: right;
        }
        .col-amount {
            width: 150px;
            text-align: right;
        }

        /* Rows specific styling */
        .group-title {
            font-weight: bold;
        }
        .row-total {
            font-weight: bold;
        }
        
        /* Footer */
        .footer {
            margin-top: 15px;
            font-size: 10pt;
        }
        .footer p {
            margin: 3px 0;
        }

        /* Utilities */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        
        @media print {
            .btn-print { display: none; }
            .slip-container { border: none; padding: 0; }
        }
    </style>
</head>
<body>
    <button class="btn-print" onclick="window.print()" style="margin: 20px; padding: 10px 20px; cursor: pointer;">Print Slip</button>
    
    <div class="slip-container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <div class="header-logo">
                    UBP
                </div>
                <div class="header-company-info">
                    <div><span class="company-name">PT. Usaha Bakti Perkasa</span></div>
                    <div class="company-subtitle">GENERAL CONTRACTOR & STEEL FABRICATOR</div>
                    <div class="company-address">Jl. Lingkar Timur No. 1 Kemiri Sidoarjo</div>
                </div>
            </div>
            <div class="header-right">
                <table class="info-table">
                    <tr>
                        <td class="label">Periode</td>
                        <td>:</td>
                        <td>{{ $data['periode'] ?? '01 - 31 Agustus 2026' }}</td>
                    </tr>
                    <tr>
                        <td class="label">No. Karyawan</td>
                        <td>:</td>
                        <td>{{ $data['karyawan']['no'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Nama</td>
                        <td>:</td>
                        <td>{{ $data['karyawan']['nama'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Lokasi</td>
                        <td>:</td>
                        <td>{{ $data['lokasi'] ?? 'KANTOR' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Title -->
        <div class="report-title">
            REKAPITULASI LEMBUR
        </div>

        <!-- Main Data Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th colspan="2">Kategori / Item</th>
                    <th class="col-qty">J A M</th>
                    <th class="col-rate">Nominal</th>
                    <th class="col-amount">IDR</th>
                </tr>
            </thead>
            <tbody>
                <!-- Group A: HARI KERJA -->
                <tr>
                    <td class="col-group">A</td>
                    <td class="col-item group-title">HARI KERJA</td>
                    <td class="text-right">{{ $data['hari_kerja']['jam'] ?? 0 }}</td>
                    <td></td>
                    <td class="text-right">0</td>
                </tr>
                <tr>
                    <td class="col-group"></td>
                    <td class="col-item">Lembur A (LA)</td>
                    <td class="text-right">{{ $data['lembur_a']['qty'] ?? 0 }}</td>
                    <td class="text-right">{{ number_format($data['lembur_a']['rate'] ?? 29200, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($data['lembur_a']['amount'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="col-group"></td>
                    <td class="col-item">Lembur B (LB)</td>
                    <td class="text-right">{{ $data['lembur_b']['qty'] ?? 0 }}</td>
                    <td class="text-right">{{ number_format($data['lembur_b']['rate'] ?? 34400, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($data['lembur_b']['amount'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr><td colspan="5" style="border:none; border-left:1px dashed #000; border-right:1px dashed #000; padding: 2px;"></td></tr>
                <tr>
                    <td class="col-group"></td>
                    <td class="col-item">Luar Kota</td>
                    <td class="text-right">{{ $data['luar_kota']['qty'] ?? 0 }}</td>
                    <td class="text-right">{{ number_format($data['luar_kota']['rate'] ?? 30000, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($data['luar_kota']['amount'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="col-group"></td>
                    <td class="col-item">Uang Makan/</td>
                    <td class="text-right">{{ $data['uang_makan']['qty'] ?? 0 }}</td>
                    <td class="text-right">{{ number_format($data['uang_makan']['rate'] ?? 15000, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($data['uang_makan']['amount'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="col-group"></td>
                    <td class="col-item">uang makan lembur</td>
                    <td class="text-right">{{ $data['uang_makan_lembur']['qty'] ?? 0 }}</td>
                    <td class="text-right">{{ number_format($data['uang_makan_lembur']['rate'] ?? 15000, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($data['uang_makan_lembur']['amount'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                
                <!-- Group B: TUNJANGAN -->
                <tr>
                    <td class="col-group" style="border-top: 1px solid #000;">B</td>
                    <td class="col-item group-title" style="border-top: 1px solid #000;">TUNJANGAN</td>
                    <td style="border-top: 1px solid #000;"></td>
                    <td style="border-top: 1px solid #000;"></td>
                    <td class="text-right" style="border-top: 1px solid #000;">0</td>
                </tr>
                <tr>
                    <td class="col-group"></td>
                    <td class="col-item">Project Manager</td>
                    <td></td>
                    <td></td>
                    <td class="text-right">0</td>
                </tr>
                <tr>
                    <td class="col-group"></td>
                    <td class="col-item">SKA</td>
                    <td></td>
                    <td></td>
                    <td class="text-right">0</td>
                </tr>
                <tr>
                    <td class="col-group"></td>
                    <td class="col-item">Jabatan</td>
                    <td></td>
                    <td></td>
                    <td class="text-right">0</td>
                </tr>

                <!-- Group C: POTONGAN -->
                <tr>
                    <td class="col-group" style="border-top: 1px solid #000;">C</td>
                    <td class="col-item group-title" style="border-top: 1px solid #000;">POTONGAN</td>
                    <td style="border-top: 1px solid #000;"></td>
                    <td style="border-top: 1px solid #000;"></td>
                    <td class="text-right" style="border-top: 1px solid #000;">0</td>
                </tr>
                
                <!-- Total Row -->
                <tr class="row-total">
                    <td colspan="4" class="text-center" style="border-top: 2px solid #000; border-bottom: 2px solid #000;">JUMLAH</td>
                    <td class="text-right" style="border-top: 2px solid #000; border-bottom: 2px solid #000;">{{ number_format($data['total_idr'] ?? 0, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer">
            <p>Catatan : Jika terdapat Komplain atau kesalahan harap Konfirmasi ke Bagian HRD</p>
            <p>Tanggal Cetak {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
        </div>
    </div>
</body>
</html>
