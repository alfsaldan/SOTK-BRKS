<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SotkMaster extends Model
{
    use HasFactory;

    protected $table = 'sotk_master';

    protected $fillable = [
        'period_id',
        'nik',
        'nama',
        'level_jabatan',
        'jabatan',
        'klasifikasi_jabatan',
        'kode_cabang',
        'unit_kantor',
        'kelas',
        'penempatan',
        'row_number',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(SotkPeriod::class, 'period_id');
    }
}
