<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SotkPeriod;
use App\Services\OrgChartBuilderService;

class SotkOrgChartController extends Controller
{
    public function index(Request $request, OrgChartBuilderService $chartBuilder)
    {
        $periods = SotkPeriod::orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get();

        $selectedPeriodId = $request->input('period_id');
        if (!$selectedPeriodId && $periods->isNotEmpty()) {
            $selectedPeriodId = $periods->first()->id;
        }

        $selectedPeriod = null;
        $unitsByKelas = [];

        if ($selectedPeriodId) {
            $selectedPeriod = SotkPeriod::find($selectedPeriodId);
            if ($selectedPeriod) {
                $unitsByKelas = $chartBuilder->getUnitsByPeriod($selectedPeriodId);
            }
        }

        return view('admin.orgchart.index', compact('periods', 'selectedPeriodId', 'selectedPeriod', 'unitsByKelas'));
    }

    public function show(Request $request, OrgChartBuilderService $chartBuilder)
    {
        $periodId   = $request->input('period_id');
        $unitKantor = $request->input('unit_kantor');

        if (!$periodId) {
            return redirect()->route('admin.orgchart.index')->with('error', 'Silakan pilih periode.');
        }

        $period = SotkPeriod::findOrFail($periodId);

        $chartNodes = $chartBuilder->build($periodId, $unitKantor);

        $title = $unitKantor ? 'Struktur Organisasi ' . $unitKantor : 'Struktur Organisasi Keseluruhan';

        return view('admin.orgchart.show', compact('period', 'unitKantor', 'chartNodes', 'title'));
    }
}
