<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SotkUploadLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'period_id',
        'user_id',
        'action',
        'file_name',
        'total_rows',
        'valid_rows',
        'error_rows',
        'notes',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(SotkPeriod::class, 'period_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
