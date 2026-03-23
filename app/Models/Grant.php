<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grant extends Model
{
    protected $fillable = [
        'user_id',
        'proposal_title',
        'grant_type',
        'grant_name',
        'duration',
        'scope',
        'amount',
        'stage',
        'submission_date',
        'deadline',
        'announcement_date',
        'rejection_count',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'submission_date' => 'date',
            'deadline' => 'date',
            'announcement_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(GrantChecklistItem::class)->orderBy('sort_order');
    }

    public function getChecklistCompletionAttribute(): int
    {
        $total = $this->checklistItems->count();
        if ($total === 0) {
            return 0;
        }

        $completed = $this->checklistItems->where('is_completed', true)->count();

        return (int) round(($completed / $total) * 100);
    }

    public function getFormattedAmountAttribute(): string
    {
        return $this->amount === null ? 'TBA' : 'RM ' . number_format((float) $this->amount, 2);
    }
}
