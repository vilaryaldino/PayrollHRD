@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="m-0 text-dark">Input Rekapitulasi Surat Perintah Lembur (SPL)</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h6 class="m-0 font-weight-bold text-primary">Form Input Data SPL</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('spl.calculate') }}" method="POST">
                        @csrf
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Data Karyawan & Periode</h5>
                                <hr>
                                <div class="mb-3">
                                    <label class="form-label">Periode Laporan</label>
                                    <input type="text" class="form-control" name="periode" value="01 - 31 Agustus 2026" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nama Karyawan</label>
                                    <input type="text" class="form-control" name="nama_karyawan" placeholder="Contoh: John Doe" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">No. Karyawan</label>
                                    <input type="text" class="form-control" name="no_karyawan" placeholder="Contoh: KRY-001" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Lokasi</label>
                                    <input type="text" class="form-control" name="lokasi" value="KANTOR" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h5>Detail Lembur & Nominal</h5>
                                <hr>
                                <!-- Hari Kerja -->
                                <div class="row mb-3 align-items-end">
                                    <div class="col-md-6">
                                        <label class="form-label">Total Hari Kerja (J A M)</label>
                                        <input type="number" class="form-control" name="hari_kerja_qty" value="19" required>
                                    </div>
                                </div>

                                <!-- Lembur A -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Jumlah Pegawai Lembur A</label>
                                        <input type="number" class="form-control" name="lembur_a_qty" value="2" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Nominal Lembur A</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" class="form-control" name="lembur_a_rate" value="29200" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Lembur B -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Jumlah Pegawai Lembur B</label>
                                        <input type="number" class="form-control" name="lembur_b_qty" value="4" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Nominal Lembur B</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" class="form-control" name="lembur_b_rate" value="34400" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Luar Kota -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Jumlah Pegawai Luar Kota</label>
                                        <input type="number" class="form-control" name="luar_kota_qty" value="0" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Nominal Luar Kota</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" class="form-control" name="luar_kota_rate" value="30000" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Uang Makan Harian -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Jumlah Pegawai/Hari (Uang Makan)</label>
                                        <input type="number" class="form-control" name="uang_makan_qty" value="19" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Nominal Uang Makan</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" class="form-control" name="uang_makan_rate" value="15000" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Uang Makan Lembur -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Jumlah Pegawai (Uang Makan Lembur)</label>
                                        <input type="number" class="form-control" name="uang_makan_lembur_qty" value="2" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Nominal Uang Makan Lembur</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" class="form-control" name="uang_makan_lembur_rate" value="15000" required>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="d-flex justify-content-end border-top pt-3">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-printer me-2"></i> Preview SPL
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
