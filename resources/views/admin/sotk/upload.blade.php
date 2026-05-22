@extends('admin.layouts.app')

@section('title', 'Upload SOTK')
@section('breadcrumb')
    <a href="{{ route('admin.sotk.index') }}">Data Master SOTK</a>
    <span class="sep">›</span>
    <span class="current">Upload SOTK</span>
@endsection

@section('content')
<div class="page-title">Upload Data Master SOTK</div>
<p class="page-subtitle">Upload file Excel SOTK sesuai periode bulan dan tahun yang dipilih.</p>

<style>
    .upload-grid { display: grid; grid-template-columns: 1fr 320px; gap: 20px; align-items: flex-start; }
    @media (max-width: 992px) {
        .upload-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="upload-grid">

    {{-- ── Form Upload ──────────────────────────── --}}
    <div class="card">
        <div class="card-header">
            <h2>Form Upload</h2>
        </div>
        <div class="card-body">

            @if($errors->any())
                <div class="alert alert-error">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <strong>Terdapat kesalahan:</strong>
                        <ul style="margin-top:6px;padding-left:16px;">
                            @foreach($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.sotk.upload.preview') }}"
                  enctype="multipart/form-data" id="upload-form">
                @csrf

                {{-- Langkah 1: Periode --}}
                <div style="margin-bottom:24px;">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                        <div style="width:28px;height:28px;border-radius:50%;background:var(--brks-blue);color:white;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0;">1</div>
                        <h3 style="font-size:14px;font-weight:600;">Pilih Periode</h3>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label for="bulan">Bulan <span style="color:#ef4444;">*</span></label>
                            <select name="bulan" id="bulan" class="form-control" required>
                                <option value="">Pilih Bulan</option>
                                @foreach($namaBulan as $num => $nama)
                                    <option value="{{ $num }}" {{ old('bulan') == $num ? 'selected' : '' }}>
                                        {{ $nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label for="tahun">Tahun <span style="color:#ef4444;">*</span></label>
                            <select name="tahun" id="tahun" class="form-control" required>
                                <option value="">Pilih Tahun</option>
                                @foreach(array_reverse($tahunRange) as $y)
                                    <option value="{{ $y }}" {{ old('tahun', date('Y')) == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <hr class="divider">

                {{-- Langkah 2: File Upload --}}
                <div style="margin-bottom:24px;">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                        <div style="width:28px;height:28px;border-radius:50%;background:var(--brks-blue);color:white;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0;">2</div>
                        <h3 style="font-size:14px;font-weight:600;">Upload File Excel</h3>
                    </div>

                    <div id="drop-zone" onclick="document.getElementById('file-input').click()"
                         style="border:2px dashed #e2e8f0;border-radius:12px;padding:36px 24px;text-align:center;cursor:pointer;transition:all .2s;background:#fafbfc;">
                        <svg style="width:40px;height:40px;margin:0 auto 12px;display:block;color:#94a3b8;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p id="drop-text" style="font-size:14px;font-weight:500;color:#475569;margin-bottom:4px;">
                            Klik atau seret file Excel ke sini
                        </p>
                        <p style="font-size:12px;color:#94a3b8;">Format: .xlsx / .xls | Maks: 10 MB</p>
                        <input type="file" name="file" id="file-input" accept=".xlsx,.xls"
                               style="display:none;" onchange="onFileSelect(this)">
                    </div>

                    <div id="file-info" style="display:none;margin-top:10px;padding:10px 14px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;display:none;align-items:center;gap:10px;">
                        <svg style="width:18px;height:18px;color:#16a34a;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span id="file-name" style="font-size:13px;color:#15803d;font-weight:500;flex:1;"></span>
                        <button type="button" onclick="clearFile()" style="background:none;border:none;cursor:pointer;color:#16a34a;font-size:18px;line-height:1;">&times;</button>
                    </div>
                </div>

                <hr class="divider">

                {{-- Actions --}}
                <div style="display:flex;gap:10px;justify-content:flex-end;">
                    <a href="{{ route('admin.sotk.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary" id="submit-btn">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        Preview & Validasi
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Panduan ───────────────────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card">
            <div class="card-header">
                <h2>Panduan Upload</h2>
            </div>
            <div class="card-body" style="font-size:13px;line-height:1.7;color:#475569;">
                <p style="margin-bottom:12px;font-weight:600;color:#1e293b;">Kolom wajib di Excel:</p>
                <ol style="padding-left:18px;margin-bottom:14px;">
                    <li>NIK</li>
                    <li>Nama</li>
                    <li>Level Jabatan</li>
                    <li>Jabatan</li>
                    <li>Klasifikasi Jabatan</li>
                    <li>Kode Cabang</li>
                    <li>Unit Kantor</li>
                    <li>Kelas</li>
                    <li>Penempatan</li>
                </ol>
                <div class="alert alert-info" style="margin-bottom:0;font-size:12px;">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Baris pertama Excel harus berisi nama kolom (header). Kolom <strong>Periode</strong> tidak diperlukan di Excel.</span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Aturan Upload</h2>
            </div>
            <div class="card-body" style="font-size:13px;line-height:1.7;color:#475569;">
                <ul style="padding-left:18px;">
                    <li>Upload periode baru <strong>tidak</strong> menghapus data periode lama</li>
                    <li>Upload pada periode yang <strong>sama</strong> akan meminta konfirmasi replace</li>
                    <li>Isi Klasifikasi Jabatan bebas (tidak harus Operasional atau Bisnis)</li>
                    <li>NIK wajib berupa angka, tepat 6 digit, dan tidak boleh duplikat dalam satu file</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
const dropZone = document.getElementById('drop-zone');
const fileInfo = document.getElementById('file-info');

function onFileSelect(input) {
    if (input.files.length > 0) {
        const file = input.files[0];
        document.getElementById('drop-text').textContent = 'File dipilih';
        document.getElementById('file-name').textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
        fileInfo.style.display = 'flex';
        dropZone.style.borderColor = '#16a34a';
        dropZone.style.background = '#f0fdf4';
    }
}

function clearFile() {
    document.getElementById('file-input').value = '';
    document.getElementById('drop-text').textContent = 'Klik atau seret file Excel ke sini';
    fileInfo.style.display = 'none';
    dropZone.style.borderColor = '#e2e8f0';
    dropZone.style.background = '#fafbfc';
}

dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.style.borderColor = 'var(--brks-blue)'; dropZone.style.background = 'var(--brks-blue-lt)'; });
dropZone.addEventListener('dragleave', () => { dropZone.style.borderColor = '#e2e8f0'; dropZone.style.background = '#fafbfc'; });
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    const dt = e.dataTransfer;
    if (dt.files.length) {
        document.getElementById('file-input').files = dt.files;
        onFileSelect(document.getElementById('file-input'));
    }
});

document.getElementById('upload-form').addEventListener('submit', function() {
    const btn = document.getElementById('submit-btn');
    btn.disabled = true;
    btn.innerHTML = '<svg style="width:16px;height:16px;animation:spin 1s linear infinite;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Memproses...';
});
</script>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>
@endsection
