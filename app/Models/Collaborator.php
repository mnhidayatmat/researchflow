<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Collaborator extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'category',
        'category_other',
        'institution_name',
        'department',
        'faculty',
        'position_title',
        'expertise_area',
        'research_field',
        'working_email',
        'phone_number',
        'country',
        'suitable_for_grant',
        'suitable_for_publication',
        'suggested_reviewer',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'suitable_for_grant' => 'boolean',
            'suitable_for_publication' => 'boolean',
            'suggested_reviewer' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getCategoryLabelAttribute(): string
    {
        if ($this->category === 'other' && $this->category_other) {
            return 'Other: ' . $this->category_other;
        }

        return str($this->category)->replace('-', ' ')->title()->toString();
    }
}
