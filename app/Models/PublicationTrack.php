<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicationTrack extends Model
{
    use HasFactory, SoftDeletes;

    public const STAGES = [
        'draft' => 'Draft',
        'submitted' => 'Submitted',
        'under_review' => 'Under Review',
        'revision_required' => 'Revision Required',
        'accepted' => 'Accepted',
        'published' => 'Published',
        'rejected' => 'Rejected',
    ];

    public const QUARTILES = [
        'Q1' => 'Q1',
        'Q2' => 'Q2',
        'Q3' => 'Q3',
        'Q4' => 'Q4',
    ];

    protected $fillable = [
        'student_id',
        'title',
        'journal',
        'quartile',
        'impact_factor',
        'stage',
        'submission_date',
        'rejected_1_date',
        'rejected_1_reviewer_input',
        'rejected_2_date',
        'rejected_2_reviewer_input',
        'rejected_3_date',
        'rejected_3_reviewer_input',
    ];

    protected function casts(): array
    {
        return [
            'submission_date' => 'date',
            'rejected_1_date' => 'date',
            'rejected_2_date' => 'date',
            'rejected_3_date' => 'date',
            'impact_factor' => 'decimal:3',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function getStageLabelAttribute(): string
    {
        return self::STAGES[$this->stage] ?? str($this->stage)->headline()->toString();
    }

    public function hasReviewerInputForRound(int $round): bool
    {
        $field = "rejected_{$round}_reviewer_input";

        return filled($this->{$field});
    }

    public function wasRejectedInRound(int $round): bool
    {
        $field = "rejected_{$round}_date";

        return !is_null($this->{$field});
    }
}
