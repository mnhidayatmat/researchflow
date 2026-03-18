<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiEmbedding extends Model
{
    protected $fillable = ['file_id', 'chunk_index', 'content', 'metadata', 'vector'];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'vector' => 'array',
        ];
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Get the vector dimension.
     */
    public function getDimensionAttribute(): int
    {
        return count($this->vector ?? []);
    }
}
