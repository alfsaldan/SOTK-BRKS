@extends('admin.layouts.app')

@section('title', 'Data Master SOTK')
@section('breadcrumb')
    <span class="current">Data Master SOTK</span>
@endsection

@section('content')
<div class="page-title">Data Master SOTK</div>
<p class="page-subtitle">Struktur Organisasi Tata Kelola — PT Bank Riau Kepri</p>

{{-- ── Stat Cards ─────────────────────────────────── --}}
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:#e8eef8;">
            <svg fill="none" viewBox="0 0 24 24" stroke="#003DA5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <div>
            <div class="stat-value">{{ $selectedPeriod?->total_pegawai ?? 0 }}</div>
            <div class="stat-label">Total Pegawai</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#f0fdf4;">
            <svg fill="none" viewBox="0 0 24 24" stroke="#16a34a"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <div>
            <div class="stat-value">{{ $periods->count() }}</div>
            <div class="stat-label">Total Periode</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fffbeb;">
            <svg fill="none" viewBox="0 0 24 24" stroke="#d97706"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        </div>
        <div>
            <div class="stat-value">{{ count($unitKantors) }}</div>
            <div class="stat-label">Unit Kantor</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fdf2f8;">
            <svg fill="none" viewBox="0 0 24 24" stroke="#9333ea"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <div>
            <div class="stat-value">{{ $selectedPeriod ? $selectedPeriod->label : '—' }}</div>
            <div class="stat-label">Periode Aktif</div>
        </div>
    </div>
</div>

{{-- ── Filter Card ─────────────────────────────────── --}}
<div class="card" style="margin-bottom:20px;">
    <div class="card-header">
        <h2>
            <svg style="width:17px;height:17px;display:inline;margin-right:6px;vertical-align:-3px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
            Filter & Pencarian
        </h2>
        <div style="display:flex;gap:8px;">
            @if($selectedPeriod)
                <a href="{{ route('admin.sotk.export', $selectedPeriod) }}" class="btn btn-success btn-sm">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Export Excel
                </a>
            @endif
            <a href="{{ route('admin.sotk.upload.form') }}" class="btn btn-primary btn-sm">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Upload SOTK
            </a>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.sotk.index') }}" id="filter-form">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;align-items:flex-end;">
                <div class="form-group" style="margin-bottom:0;">
                    <label for="period_id">Periode</label>
                    <select name="period_id" id="period_id" class="form-control" onchange="document.getElementById('filter-form').submit()">
                        @forelse($periods as $p)
                            <option value="{{ $p->id }}" {{ $p->id == $selectedPeriodId ? 'selected' : '' }}>
                                {{ $p->label }}
                            </option>
                        @empty
                            <option value="">Belum ada periode</option>
                        @endforelse
                    </select>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label for="unit_kantor">Unit Kantor</label>
                    <select name="unit_kantor" id="unit_kantor" class="form-control">
                        <option value="">Semua Unit Kantor</option>
                        @foreach($unitKantors as $uk)
                            <option value="{{ $uk }}" {{ request('unit_kantor') == $uk ? 'selected' : '' }}>{{ $uk }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label for="kode_cabang">Kode Cabang</label>
                    <select name="kode_cabang" id="kode_cabang" class="form-control">
                        <option value="">Semua Kode Cabang</option>
                        @foreach($kodeCabangs as $kc)
                            <option value="{{ $kc }}" {{ request('kode_cabang') == $kc ? 'selected' : '' }}>{{ $kc }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label for="jabatan">Jabatan</label>
                    <input type="text" name="jabatan" id="jabatan" class="form-control"
                           value="{{ request('jabatan') }}" placeholder="Cari jabatan...">
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label for="nama">Nama Pegawai</label>
                    <input type="text" name="nama" id="nama" class="form-control"
                           value="{{ request('nama') }}" placeholder="Cari nama...">
                </div>

                <div style="display:flex;gap:8px;">
                    <button type="submit" class="btn btn-primary" style="flex:1;">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Filter
                    </button>
                    <a href="{{ route('admin.sotk.index', ['period_id' => $selectedPeriodId]) }}" class="btn btn-secondary">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ── Data Table ─────────────────────────────────── --}}
<div class="card">
    <div class="card-header">
        <h2>
            Daftar Pegawai
            @if($selectedPeriod)
                <span class="badge badge-blue" style="margin-left:8px;">{{ $selectedPeriod->label }}</span>
            @endif
        </h2>
        <span style="font-size:12px;color:#94a3b8;">
            {{ $sotk->count() }} data ditemukan
        </span>
    </div>

    <div class="table-wrap">
        @if($sotk->count() > 0)
        <table id="master-table">
            <thead>
                <tr>
                    <th style="width:46px;">No</th>
                    <th>NIK</th>
                    <th>Nama Pegawai</th>
                    <th>Level Jabatan</th>
                    <th>Jabatan</th>
                    <th>Klasifikasi</th>
                    <th>Kode Cabang</th>
                    <th>Unit Kantor</th>
                    <th>Kelas</th>
                    <th>Penempatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sotk as $i => $row)
                <tr>
                    <td class="text-muted text-sm">{{ $i + 1 }}</td>
                    <td><code style="font-size:12px;background:#f8fafc;padding:2px 6px;border-radius:4px;">{{ $row->nik }}</code></td>
                    <td style="font-weight:500;">{{ $row->nama }}</td>
                    <td>{{ $row->level_jabatan }}</td>
                    <td>{{ $row->jabatan }}</td>
                    <td>
                        <span class="badge {{ $row->klasifikasi_jabatan === 'Operasional' ? 'badge-blue' : 'badge-orange' }}">
                            {{ $row->klasifikasi_jabatan }}
                        </span>
                    </td>
                    <td><span class="badge badge-gray">{{ $row->kode_cabang }}</span></td>
                    <td>{{ $row->unit_kantor }}</td>
                    <td>{{ $row->kelas }}</td>
                    <td>{{ $row->penempatan }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>



        @else
        <div class="empty-state">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p>
                @if(!$selectedPeriod)
                    Belum ada data SOTK. <a href="{{ route('admin.sotk.upload.form') }}" style="color:var(--brks-blue);">Upload sekarang →</a>
                @else
                    Tidak ada data yang cocok dengan filter yang dipilih.
                @endif
            </p>
        </div>
        @endif
    </div>
</div>

{{-- ── Daftar Periode (management) ────────────────── --}}
@if($periods->count() > 0)
<div class="card" style="margin-top:20px;">
    <div class="card-header">
        <h2>Manajemen Periode</h2>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Periode</th>
                    <th>Total Pegawai</th>
                    <th>Status</th>
                    <th>Di-upload</th>
                    <th style="width:120px;text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($periods as $p)
                <tr>
                    <td style="font-weight:600;">{{ $p->label }}</td>
                    <td>{{ number_format($p->total_pegawai) }} pegawai</td>
                    <td><span class="badge badge-green">{{ ucfirst($p->status) }}</span></td>
                    <td class="text-muted text-sm">{{ $p->uploaded_at ? $p->uploaded_at->format('d M Y H:i') : '—' }}</td>
                    <td style="text-align:center;">
                        <button onclick="confirmDelete({{ $p->id }}, '{{ $p->label }}')" class="btn btn-danger btn-sm">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Hapus
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ── Modal Hapus ─────────────────────────────────── --}}
<div class="modal-backdrop" id="deleteModal">
    <div class="modal-box">
        <h3 style="color:#dc2626;">⚠ Konfirmasi Hapus Periode</h3>
        <p>Anda akan menghapus data periode <strong id="delete-label"></strong>.<br>
        Semua data pegawai pada periode ini akan dihapus permanen dan <strong>tidak dapat dipulihkan</strong>.</p>
        <div class="modal-actions">
            <button onclick="closeDeleteModal()" class="btn btn-secondary">Batal</button>
            <form id="delete-form" method="POST" style="margin:0;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Ya, Hapus Sekarang</button>
            </form>
        </div>
    </div>
</div>

<style>
/* Custom DataTables Styling */
.dataTables_wrapper .dataTables_length select,
.dataTables_wrapper .dataTables_filter input {
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 4px 8px;
    outline: none;
}
.dataTables_wrapper .dataTables_length select:focus,
.dataTables_wrapper .dataTables_filter input:focus {
    border-color: var(--brks-blue);
}
.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: var(--brks-blue);
    color: white !important;
    border: 1px solid var(--brks-blue);
    border-radius: 6px;
}
.dataTables_wrapper .dataTables_paginate .paginate_button {
    border-radius: 6px;
}
table.dataTable thead th, table.dataTable thead td {
    border-bottom: 1px solid #e2e8f0;
}
table.dataTable.no-footer {
    border-bottom: 1px solid #e2e8f0;
}
</style>

<script>
$(document).ready(function() {
    $('#master-table').DataTable({
        "pageLength": 50,
        "lengthMenu": [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"] ],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
        },
        "ordering": true,
        "info": true,
        "autoWidth": false
    });
});

function confirmDelete(id, label) {
    document.getElementById('delete-label').textContent = label;
    document.getElementById('delete-form').action = '/admin/sotk/' + id;
    document.getElementById('deleteModal').classList.add('show');
}
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
</script>
@endsection
