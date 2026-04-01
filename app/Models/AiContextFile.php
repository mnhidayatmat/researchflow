<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiContextFile extends Model
{
    protected $fillable = [
        'user_id',
        'original_name',
        'path',
        'size',
        'mime_type',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        if ($bytes >= 1_048_576) {
            return number_format($bytes / 1_048_576, 1) . ' MB';
        }
        if ($bytes >= 1_024) {
            return number_format($bytes / 1_024, 0) . ' KB';
        }
        return $bytes . ' B';
    }
}
