@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h3 class="mb-0 text-primary fw-bold"><i class="bi bi-clock-history me-2"></i>Data Absensi Pegawai</h3>
            <div>
                <button type="button" class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#modalManual">
                    <i class="bi bi-plus-circle"></i> Tambah Manual
                </button>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalImport">
                    <i class="bi bi-file-earmark-excel"></i> Import Fingerspot
                </button>
            </div>
        </div>
    </div>

    {{-- Notifikasi Sukses/Error --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Kartu Filter & Tabel --}}
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white py-3">
            <form method="GET" action="{{ route('absensi.index') }}" class="row g-3 align-items-center">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Mulai Tanggal</label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Cari Pegawai / PIN</label>
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Ketik nama / pin..." value="{{ request('search') }}">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Cari</button>
                        <a href="{{ route('absensi.index') }}" class="btn btn-light border"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Tanggal</th>
                            <th>PIN Mesin</th>
                            <th>Nama Pegawai</th>
                            <th>Clock In</th>
                            <th>Clock Out</th>
                            <th>Lokasi</th>
                            <th>Status Data</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($absensis as $absen)
                        <tr>
                            <td class="ps-4">{{ \Carbon\Carbon::parse($absen->tanggal)->translatedFormat('d M Y') }}</td>
                            <td><span class="badge bg-secondary">{{ $absen->id_mesin_pegawai ?? $absen->id_mesin ?? $absen->id_pegawai }}</span></td>
                            <td class="fw-medium">
                                @if(str_contains($absen->nama_pegawai, 'Tidak Dikenal'))
                                    <span class="text-danger"><i class="bi bi-exclamation-triangle"></i> {{ $absen->nama_pegawai }}</span>
                                @else
                                    {{ $absen->nama_pegawai }}
                                @endif
                            </td>
                            <td>
                                @if($absen->jam_kehadiran)
                                    <span class="text-success fw-bold">{{ \Carbon\Carbon::parse($absen->jam_kehadiran)->format('H:i') }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($absen->jam_kepulangan)
                                    <span class="text-primary fw-bold">{{ \Carbon\Carbon::parse($absen->jam_kepulangan)->format('H:i') }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $absen->lokasi_absen ?? '-' }}</td>
                            <td>
                                @if($absen->jam_kehadiran && $absen->jam_kepulangan)
                                    <span class="badge bg-success rounded-pill">Lengkap</span>
                                @else
                                    <span class="badge bg-warning text-dark rounded-pill">Tidak Lengkap</span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                <form method="POST" action="{{ route('absensi.destroy', $absen->id) }}" onsubmit="return confirm('Hapus data absensi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Belum ada data absensi yang ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3 border-top-0">
            {{ $absensis->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

{{-- MODAL IMPORT EXCEL --}}
<div class="modal fade" id="modalImport" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('absensi.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white border-0">
                    <h5 class="modal-title"><i class="bi bi-file-earmark-excel me-2"></i>Import Data Fingerspot</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">Pastikan format file Excel Anda (.xls / .xlsx) memiliki Header: <b>pin_mesin, tanggal, jam_in, jam_out, lokasi</b>.</p>
                    <div class="mb-3">
                        <label for="file_excel" class="form-label">Pilih File Excel</label>
                        <input class="form-control" type="file" id="file_excel" name="file_excel" required accept=".xls,.xlsx">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Proses Import</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- MODAL TAMBAH MANUAL --}}
<div class="modal fade" id="modalManual" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('absensi.store') }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Tambah Absensi Manual</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pegawai (PIN Mesin)</label>
                        <select name="id_pegawai" class="form-select" required>
                            <option value="">-- Pilih Pegawai --</option>
                            @foreach($pegawais as $pegawai)
                                <option value="{{ $pegawai->ID_PEGAWAI_MESIN }}">{{ $pegawai->NM_PEGAWAI }} (PIN: {{ $pegawai->ID_PEGAWAI_MESIN }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jam In (Contoh: 08:00)</label>
                            <input type="time" name="jam_kehadiran" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jam Out (Contoh: 17:00)</label>
                            <input type="time" name="jam_kepulangan" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lokasi/Keterangan</label>
                        <input type="text" name="lokasi_absen" class="form-control" placeholder="Misal: WFH / Dinas Luar">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Data</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
