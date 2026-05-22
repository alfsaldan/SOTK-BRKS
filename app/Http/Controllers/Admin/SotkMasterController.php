<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SotkMaster;
use App\Models\SotkPeriod;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SotkMasterExport;

class SotkMasterController extends Controller
{
    /**
     * Halaman utama — daftar data SOTK dengan filter & paginasi.
     */
    public function index(Request $request)
    {
        // Ambil semua periode untuk dropdown filter
        $periods = SotkPeriod::where('status', 'active')
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->get();

        // Periode yang dipilih (default: periode terbaru)
        $selectedPeriodId = $request->input('period_id', $periods->first()?->id);
        $selectedPeriod   = $periods->find($selectedPeriodId);

        // Ambil nilai filter unik untuk dropdown (berdasarkan periode yang dipilih)
        $unitKantors  = [];
        $kodeCabangs  = [];
        if ($selectedPeriod) {
            $unitKantors = SotkMaster::where('period_id', $selectedPeriod->id)
                ->distinct()->orderBy('unit_kantor')->pluck('unit_kantor');
            $kodeCabangs = SotkMaster::where('period_id', $selectedPeriod->id)
                ->distinct()->orderBy('kode_cabang')->pluck('kode_cabang');
        }

        // Query utama
        $query = SotkMaster::query()->where('period_id', $selectedPeriodId);

        if ($request->filled('unit_kantor')) {
            $query->where('unit_kantor', $request->unit_kantor);
        }
        if ($request->filled('kode_cabang')) {
            $query->where('kode_cabang', $request->kode_cabang);
        }
        if ($request->filled('jabatan')) {
            $query->where('jabatan', 'like', '%' . $request->jabatan . '%');
        }
        if ($request->filled('nama')) {
            $query->where('nama', 'like', '%' . $request->nama . '%');
        }

        $sotk = $query->orderBy('nama')->get();

        return view('admin.sotk.index', compact(
            'periods',
            'selectedPeriod',
            'selectedPeriodId',
            'unitKantors',
            'kodeCabangs',
            'sotk'
        ));
    }

    /**
     * Hapus semua data pada satu periode.
     */
    public function destroy(SotkPeriod $period)
    {
        $period->delete(); // CASCADE akan hapus sotk_master & upload_logs juga
        return redirect()->route('admin.sotk.index')
            ->with('success', "Data periode {$period->label} berhasil dihapus.");
    }

    /**
     * Export data SOTK per periode ke Excel.
     */
    public function export(SotkPeriod $period)
    {
        $filename = 'SOTK_' . str_replace(' ', '_', $period->label) . '_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new SotkMasterExport($period->id), $filename);
    }
}
