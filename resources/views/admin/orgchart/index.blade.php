@extends('admin.layouts.app')

@section('title', 'Lihat Struktur Organisasi')
@section('breadcrumb')
    <span class="current">Struktur Organisasi</span>
@endsection

@section('content')
<div class="page-title">Lihat Struktur Organisasi</div>
<p class="page-subtitle">Pilih periode dan unit kantor untuk menampilkan bagan struktur organisasi.</p>

<div class="orgchart-selector-grid">

    {{-- ── Selector Form ─────────────── --}}
    <div class="card">
        <div class="card-header"><h2>Filter Tampilan Struktur</h2></div>
        <div class="card-body">

            {{-- Periode Switcher (Auto-submit to refresh units) --}}
            <form method="GET" action="{{ route('admin.orgchart.index') }}" id="periodForm" style="margin-bottom:20px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;">Pilih Periode</label>
                    <select name="period_id" class="form-control" onchange="document.getElementById('periodForm').submit();">
                        @forelse($periods as $p)
                            <option value="{{ $p->id }}" {{ $p->id == $selectedPeriodId ? 'selected' : '' }}>
                                {{ $p->label }}
                            </option>
                        @empty
                            <option value="">Belum ada periode aktif</option>
                        @endforelse
                    </select>
                </div>
            </form>

            @if($selectedPeriod)
            <hr class="divider">
            <form method="GET" action="{{ route('admin.orgchart.show') }}">
                <input type="hidden" name="period_id" value="{{ $selectedPeriodId }}">

                <div class="form-group">
                    <label>Unit Kantor / Unit Kerja <span style="color:#ef4444;">*</span></label>
                    <select name="unit_kantor" id="unit_kantor" class="form-control" required>
                        <option value="">— Pilih Unit —</option>
                        @foreach($unitsByKelas as $kelas => $units)
                            <optgroup label="{{ $kelas }}">
                                @foreach($units as $unit)
                                    <option value="{{ $unit }}">{{ $unit }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Tampilkan Struktur
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- ── Info Panel ─────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card">
            <div class="card-header"><h2>Panduan</h2></div>
            <div class="card-body" style="font-size:13px;color:#475569;line-height:1.8;">
                <ol style="padding-left:18px;margin:0;">
                    <li>Pilih <strong>Periode</strong> bulan dan tahun</li>
                    <li>Pilih <strong>Unit Kantor</strong> dari dropdown</li>
                    <li>Klik <strong>Tampilkan Struktur</strong></li>
                    <li>Bagan akan muncul di halaman berikutnya</li>
                    <li>Gunakan kontrol <strong>Zoom In/Out</strong> dan <strong>Pan</strong></li>
                    <li>Klik kotak untuk melihat detail pegawai</li>
                    <li>Export ke <strong>PDF</strong> atau <strong>PNG</strong></li>
                </ol>
            </div>
        </div>

        @if($selectedPeriod)
        <div class="card">
            <div class="card-header"><h2>Periode Aktif</h2></div>
            <div class="card-body" style="font-size:13px;">
                <div class="stat-card" style="box-shadow:none;padding:0;gap:12px;">
                    <div class="stat-icon" style="background:#e8eef8;">
                        <svg fill="none" viewBox="0 0 24 24" stroke="#003DA5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <div class="stat-value" style="font-size:18px;">{{ $selectedPeriod->label }}</div>
                        <div class="stat-label">{{ number_format($selectedPeriod->total_pegawai) }} pegawai</div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<style>
    .orgchart-selector-grid {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 20px;
        align-items: flex-start;
    }
    @media (max-width: 992px) {
        .orgchart-selector-grid { grid-template-columns: 1fr; }
    }
</style>
@endsection
