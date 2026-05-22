<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SotkMaster;
use App\Models\SotkPeriod;
use App\Models\SotkUploadLog;
use App\Services\SotkExcelImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SotkUploadController extends Controller
{
    public function __construct(private SotkExcelImportService $importService) {}

    /**
     * Tampilkan form upload.
     */
    public function form()
    {
        $namaBulan = SotkPeriod::$namaBulan;
        $tahunRange = range(now()->year - 3, now()->year + 1);
        return view('admin.sotk.upload', compact('namaBulan', 'tahunRange'));
    }

    /**
     * Proses file yang diupload → parse + validasi → simpan ke session → tampilkan preview.
     */
    public function preview(Request $request)
    {
        $request->validate([
            'bulan' => ['required', 'integer', 'between:1,12'],
            'tahun' => ['required', 'integer', 'min:2000', 'max:2099'],
            'file'  => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ], [
            'bulan.required'  => 'Bulan wajib dipilih.',
            'tahun.required'  => 'Tahun wajib diisi.',
            'file.required'   => 'File Excel wajib diupload.',
            'file.mimes'      => 'Format file harus .xlsx atau .xls.',
            'file.max'        => 'Ukuran file maksimal 10 MB.',
        ]);

        $bulan     = (int) $request->bulan;
        $tahun     = (int) $request->tahun;
        $label     = SotkPeriod::generateLabel($bulan, $tahun);
        $file      = $request->file('file');
        $fileName  = $file->getClientOriginalName();

        // Parse Excel
        $result = $this->importService->parse($file);

        if (!$result['headers_valid']) {
            return back()
                ->withInput()
                ->with('error', 'Header kolom tidak valid. Kolom berikut tidak ditemukan: ' . implode(', ', $result['missing_headers']))
                ->with('missing_headers', $result['missing_headers']);
        }

        // Cek apakah periode sudah ada
        $existingPeriod = SotkPeriod::where('bulan', $bulan)->where('tahun', $tahun)->first();

        // Simpan data preview ke session (hindari passing data besar ke view via flash)
        Session::put('sotk_preview', [
            'bulan'           => $bulan,
            'tahun'           => $tahun,
            'label'           => $label,
            'file_name'       => $fileName,
            'total_rows'      => $result['total_rows'],
            'valid_rows'      => $result['valid_rows'],
            'error_rows'      => $result['error_rows'],
            'rows'            => $result['rows'],
            'existing_period' => $existingPeriod ? $existingPeriod->only(['id', 'label', 'total_pegawai']) : null,
        ]);

        return redirect()->route('admin.sotk.upload.preview.show');
    }

    /**
     * Tampilkan halaman preview dari session.
     */
    public function previewShow()
    {
        $preview = Session::get('sotk_preview');
        if (!$preview) {
            return redirect()->route('admin.sotk.upload.form')
                ->with('error', 'Sesi preview telah kedaluwarsa. Silakan upload ulang file.');
        }

        $namaBulan  = SotkPeriod::$namaBulan;
        $tahunRange = range(now()->year - 3, now()->year + 1);
        return view('admin.sotk.preview', compact('preview', 'namaBulan', 'tahunRange'));
    }

    /**
     * Simpan data dari session ke database.
     * Parameter `action` = 'store' (baru) atau 'replace' (ganti periode yang sama).
     */
    public function store(Request $request)
    {
        $request->validate([
            'action' => ['required', 'in:store,replace'],
        ]);

        $preview = Session::get('sotk_preview');
        if (!$preview) {
            return redirect()->route('admin.sotk.upload.form')
                ->with('error', 'Sesi preview kedaluwarsa. Silakan upload ulang file.');
        }

        $validRows = array_filter($preview['rows'], fn($row) => $row['is_valid']);
        if (empty($validRows)) {
            return redirect()->route('admin.sotk.upload.preview.show')
                ->with('error', 'Tidak ada data valid yang dapat disimpan. Perbaiki file Excel terlebih dahulu.');
        }

        DB::transaction(function () use ($request, $preview, $validRows) {
            $action     = $request->action;
            $isReplace  = $action === 'replace' && isset($preview['existing_period']);

            // Dapatkan atau buat periode
            $period = SotkPeriod::firstOrNew([
                'bulan' => $preview['bulan'],
                'tahun' => $preview['tahun'],
            ]);

            if ($isReplace) {
                // Hapus data lama
                SotkMaster::where('period_id', $period->id)->delete();
            }

            $period->label          = $preview['label'];
            $period->total_pegawai  = count($validRows);
            $period->uploaded_by    = auth()->id();
            $period->uploaded_at    = now();
            $period->status         = 'active';
            $period->save();

            // Batch insert per 500 baris
            $now     = now()->toDateTimeString();
            $chunks  = array_chunk(array_values($validRows), 500);

            foreach ($chunks as $chunk) {
                $insert = array_map(fn($row) => [
                    'period_id'           => $period->id,
                    'nik'                 => $row['nik'],
                    'nama'                => $row['nama'],
                    'level_jabatan'       => $row['level_jabatan'],
                    'jabatan'             => $row['jabatan'],
                    'klasifikasi_jabatan' => $row['klasifikasi_jabatan'],
                    'kode_cabang'         => $row['kode_cabang'],
                    'unit_kantor'         => $row['unit_kantor'],
                    'kelas'               => $row['kelas'],
                    'penempatan'          => $row['penempatan'],
                    'row_number'          => $row['row_number'],
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ], $chunk);

                SotkMaster::insert($insert);
            }

            // Simpan log
            SotkUploadLog::create([
                'period_id'  => $period->id,
                'user_id'    => auth()->id(),
                'action'     => $isReplace ? 'replace' : 'upload',
                'file_name'  => $preview['file_name'],
                'total_rows' => $preview['total_rows'],
                'valid_rows' => $preview['valid_rows'],
                'error_rows' => $preview['error_rows'],
                'notes'      => $isReplace ? 'Data periode lama digantikan.' : null,
            ]);
        });

        Session::forget('sotk_preview');

        return redirect()->route('admin.sotk.index', ['period_id' => SotkPeriod::where('bulan', $preview['bulan'])->where('tahun', $preview['tahun'])->value('id')])
            ->with('success', "Data SOTK periode {$preview['label']} berhasil disimpan (" . count($validRows) . " pegawai).");
    }

    /**
     * Batalkan preview & bersihkan session.
     */
    public function cancel()
    {
        Session::forget('sotk_preview');
        return redirect()->route('admin.sotk.upload.form')
            ->with('info', 'Upload dibatalkan.');
    }
}
