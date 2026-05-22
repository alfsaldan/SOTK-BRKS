<?php

namespace App\Exports;

use App\Models\SotkMaster;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SotkMasterExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(private int $periodId) {}

    public function query()
    {
        return SotkMaster::where('period_id', $this->periodId)->orderBy('nama');
    }

    public function headings(): array
    {
        return [
            'NIK',
            'Nama',
            'Level Jabatan',
            'Jabatan',
            'Klasifikasi Jabatan',
            'Kode Cabang',
            'Unit Kantor',
            'Kelas',
            'Penempatan',
        ];
    }

    public function map($row): array
    {
        return [
            $row->nik,
            $row->nama,
            $row->level_jabatan,
            $row->jabatan,
            $row->klasifikasi_jabatan,
            $row->kode_cabang,
            $row->unit_kantor,
            $row->kelas,
            $row->penempatan,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF003DA5']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }
}
