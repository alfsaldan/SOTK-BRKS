<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SotkPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'bulan',
        'tahun',
        'label',
        'total_pegawai',
        'uploaded_by',
        'uploaded_at',
        'status',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'bulan'       => 'integer',
        'tahun'       => 'integer',
    ];

    // Nama bulan dalam Bahasa Indonesia
    public static array $namaBulan = [
        1  => 'Januari',
        2  => 'Februari',
        3  => 'Maret',
        4  => 'April',
        5  => 'Mei',
        6  => 'Juni',
        7  => 'Juli',
        8  => 'Agustus',
        9  => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    public static function generateLabel(int $bulan, int $tahun): string
    {
        return (self::$namaBulan[$bulan] ?? 'Bulan ' . $bulan) . ' ' . $tahun;
    }

    public function sotk(): HasMany
    {
        return $this->hasMany(SotkMaster::class, 'period_id');
    }

    public function uploadLogs(): HasMany
    {
        return $this->hasMany(SotkUploadLog::class, 'period_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
