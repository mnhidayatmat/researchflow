<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupervisorPublication extends Model
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

    public const JOURNAL_INDEXES = [
        'wos' => 'WoS',
        'scopus' => 'Scopus',
        'scopus_proceeding' => 'Scopus Proceeding',
        'others' => 'Others',
    ];

    protected $fillable = [
        'user_id',
        'title',
        'journal',
        'journal_index',
        'journal_index_other',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function authors(): HasMany
    {
        return $this->hasMany(PublicationAuthor::class)->orderBy('order');
    }

    public function getStageLabelAttribute(): string
    {
        return self::STAGES[$this->stage] ?? str($this->stage)->headline()->toString();
    }

    public function getJournalIndexLabelAttribute(): string
    {
        if ($this->journal_index === 'others' && filled($this->journal_index_other)) {
            return $this->journal_index_other;
        }

        return self::JOURNAL_INDEXES[$this->journal_index] ?? 'N/A';
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
