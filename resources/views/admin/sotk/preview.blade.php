@extends('admin.layouts.app')

@section('title', 'Preview Data SOTK')
@section('breadcrumb')
    <a href="{{ route('admin.sotk.index') }}">Data Master SOTK</a>
    <span class="sep">›</span>
    <a href="{{ route('admin.sotk.upload.form') }}">Upload SOTK</a>
    <span class="sep">›</span>
    <span class="current">Preview & Validasi</span>
@endsection

@section('content')
<div class="page-title">Preview Data SOTK — {{ $preview['label'] }}</div>
<p class="page-subtitle">Periksa data sebelum disimpan ke database.</p>

{{-- ── Summary Stats ──────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:22px;">
    <div class="stat-card">
        <div class="stat-icon" style="background:#e8eef8;">
            <svg fill="none" viewBox="0 0 24 24" stroke="#003DA5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <div>
            <div class="stat-value">{{ $preview['total_rows'] }}</div>
            <div class="stat-label">Total Baris</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#f0fdf4;">
            <svg fill="none" viewBox="0 0 24 24" stroke="#16a34a"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div class="stat-value" style="color:#16a34a;">{{ $preview['valid_rows'] }}</div>
            <div class="stat-label">Baris Valid</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fef2f2;">
            <svg fill="none" viewBox="0 0 24 24" stroke="#dc2626"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div class="stat-value" style="color:{{ $preview['error_rows'] > 0 ? '#dc2626' : '#94a3b8' }};">{{ $preview['error_rows'] }}</div>
            <div class="stat-label">Baris Error</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fffbeb;">
            <svg fill="none" viewBox="0 0 24 24" stroke="#d97706"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
        </div>
        <div>
            <div class="stat-value" style="font-size:14px;">{{ $preview['file_name'] }}</div>
            <div class="stat-label">File</div>
        </div>
    </div>
</div>

{{-- ── Alert jika ada error row --}}
@if($preview['error_rows'] > 0)
<div class="alert alert-warning" style="margin-bottom:20px;">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    <span>
        <strong>{{ $preview['error_rows'] }} baris mengandung error</strong> (ditampilkan dengan latar merah).
        Hanya <strong>{{ $preview['valid_rows'] }} baris valid</strong> yang akan disimpan ke database.
        Baris error akan dilewati. Perbaiki file Excel jika ingin menyimpan semua data.
    </span>
</div>
@endif

{{-- ── Konfirmasi Replace jika periode sudah ada --}}
@if($preview['existing_period'])
<div class="alert alert-error" style="margin-bottom:20px;">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span>
        <strong>Periode {{ $preview['label'] }} sudah ada</strong>
        ({{ number_format($preview['existing_period']['total_pegawai']) }} pegawai tersimpan).
        Jika disimpan, data lama akan digantikan seluruhnya.
    </span>
</div>
@endif

{{-- ── Preview Table ────────────────────────────────── --}}
<div class="card" style="margin-bottom:20px;">
    <div class="card-header">
        <h2>Tabel Preview Data</h2>
        @if($preview['error_rows'] > 0)
            <div style="display:flex;gap:8px;align-items:center;">
                <label style="margin:0;font-size:12px;display:flex;align-items:center;gap:6px;cursor:pointer;font-weight:400;">
                    <input type="checkbox" id="show-errors-only" onchange="toggleErrorOnly(this)">
                    Tampilkan error saja
                </label>
            </div>
        @endif
    </div>
    <div class="table-wrap">
        <table id="preview-table">
            <thead>
                <tr>
                    <th style="width:50px;">Baris</th>
                    <th>NIK</th>
                    <th>Nama</th>
                    <th>Level Jabatan</th>
                    <th>Jabatan</th>
                    <th>Klasifikasi</th>
                    <th>Kode Cabang</th>
                    <th>Unit Kantor</th>
                    <th>Kelas</th>
                    <th>Penempatan</th>
                    <th style="width:70px;text-align:center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($preview['rows'] as $row)
                <tr class="{{ $row['is_valid'] ? 'row-valid' : 'row-error' }}"
                    style="{{ !$row['is_valid'] ? 'background:#fef2f2;' : '' }}">
                    <td class="text-muted text-sm">{{ $row['row_number'] }}</td>
                    <td>{{ $row['nik'] ?: '—' }}</td>
                    <td style="font-weight:{{ $row['is_valid'] ? '500' : 'normal' }};">{{ $row['nama'] ?: '—' }}</td>
                    <td>{{ $row['level_jabatan'] ?: '—' }}</td>
                    <td>{{ $row['jabatan'] ?: '—' }}</td>
                    <td>{{ $row['klasifikasi_jabatan'] ?: '—' }}</td>
                    <td>{{ $row['kode_cabang'] ?: '—' }}</td>
                    <td>{{ $row['unit_kantor'] ?: '—' }}</td>
                    <td>{{ $row['kelas'] ?: '—' }}</td>
                    <td>{{ $row['penempatan'] ?: '—' }}</td>
                    <td style="text-align:center;">
                        @if($row['is_valid'])
                            <span class="badge badge-green" style="font-size:11px;">✓ Valid</span>
                        @else
                            <span class="badge" style="background:#fecaca;color:#dc2626;font-size:11px;" title="{{ implode(' | ', $row['errors']) }}">✗ Error</span>
                        @endif
                    </td>
                </tr>
                @if(!$row['is_valid'] && !empty($row['errors']))
                <tr class="row-error" style="background:#fef2f2;">
                    <td></td>
                    <td colspan="10" style="padding-top:2px;padding-bottom:8px;">
                        @foreach($row['errors'] as $err)
                            <span style="display:inline-block;font-size:11px;color:#dc2626;background:#fee2e2;padding:2px 8px;border-radius:4px;margin:2px;">{{ $err }}</span>
                        @endforeach
                    </td>
                </tr>
                @endif
                @empty
                <tr>
                    <td colspan="11" style="text-align:center;padding:40px;color:#94a3b8;">Tidak ada data ditemukan di file Excel.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── Action Buttons ──────────────────────────────── --}}
<div class="card">
    <div class="card-body">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div style="font-size:13px;color:#475569;">
                @if($preview['valid_rows'] > 0)
                    <strong style="color:#15803d;">{{ $preview['valid_rows'] }} baris valid</strong> siap disimpan ke periode <strong>{{ $preview['label'] }}</strong>.
                @else
                    <strong style="color:#dc2626;">Tidak ada data valid.</strong> Perbaiki file Excel dan upload ulang.
                @endif
            </div>
            <div style="display:flex;gap:10px;">
                <a href="{{ route('admin.sotk.upload.cancel') }}" class="btn btn-secondary">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Batal & Upload Ulang
                </a>

                @if($preview['valid_rows'] > 0)
                    @if($preview['existing_period'])
                        {{-- Periode sudah ada — tampilkan modal konfirmasi --}}
                        <button onclick="showReplaceModal()" class="btn btn-danger">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Ganti Data Periode
                        </button>
                    @else
                        {{-- Periode baru — langsung simpan --}}
                        <form method="POST" action="{{ route('admin.sotk.upload.store') }}" style="margin:0;">
                            @csrf
                            <input type="hidden" name="action" value="store">
                            <button type="submit" class="btn btn-primary">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                Simpan Data Periode {{ $preview['label'] }}
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ── Modal Konfirmasi Replace ────────────────────── --}}
@if($preview['existing_period'])
<div class="modal-backdrop" id="replaceModal">
    <div class="modal-box">
        <h3 style="color:#dc2626;">⚠ Konfirmasi Ganti Data Periode</h3>
        <p>
            Data periode <strong>{{ $preview['label'] }}</strong> sudah ada
            (<strong>{{ number_format($preview['existing_period']['total_pegawai']) }} pegawai</strong>).<br><br>
            Jika Anda melanjutkan, seluruh data lama akan <strong>dihapus dan digantikan</strong>
            dengan <strong>{{ $preview['valid_rows'] }} data baru</strong> dari file
            <em>{{ $preview['file_name'] }}</em>.<br><br>
            Tindakan ini <strong>tidak dapat dibatalkan</strong>.
        </p>
        <div class="modal-actions">
            <button onclick="closeReplaceModal()" class="btn btn-secondary">Tidak, Batal</button>
            <form method="POST" action="{{ route('admin.sotk.upload.store') }}" style="margin:0;">
                @csrf
                <input type="hidden" name="action" value="replace">
                <button type="submit" class="btn btn-danger">Ya, Ganti Data Sekarang</button>
            </form>
        </div>
    </div>
</div>
<script>
function showReplaceModal()  { document.getElementById('replaceModal').classList.add('show'); }
function closeReplaceModal() { document.getElementById('replaceModal').classList.remove('show'); }
document.getElementById('replaceModal').addEventListener('click', function(e) { if (e.target === this) closeReplaceModal(); });
</script>
@endif

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
    // Initialize DataTables
    var table = $('#preview-table').DataTable({
        "pageLength": 50,
        "lengthMenu": [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"] ],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
        },
        "ordering": true,
        "info": true,
        "autoWidth": false
    });

    // Custom filtering function for 'Show errors only'
    $.fn.dataTable.ext.search.push(
        function( settings, data, dataIndex ) {
            var showErrorsOnly = $('#show-errors-only').is(':checked');
            if (!showErrorsOnly) return true;
            
            var rowNode = table.row(dataIndex).node();
            return $(rowNode).hasClass('row-error');
        }
    );

    // Event listener for the checkbox
    $('#show-errors-only').on('change', function() {
        table.draw();
    });
});
</script>
@endsection
