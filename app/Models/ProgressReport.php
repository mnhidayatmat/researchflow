<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgressReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id', 'reviewed_by', 'title', 'content', 'achievements',
        'challenges', 'next_steps', 'type', 'custom_type', 'status', 'period_start',
        'period_end', 'supervisor_feedback', 'submitted_at', 'reviewed_at',
        'attachment_original_name', 'attachment_mime_type', 'attachment_size',
        'attachment_disk', 'attachment_path', 'attachment_storage_owner_id',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
    public function attachmentStorageOwner(): BelongsTo { return $this->belongsTo(User::class, 'attachment_storage_owner_id'); }
    public function revisions(): MorphMany { return $this->morphMany(Revision::class, 'revisable'); }

    public static function typeOptions(): array
    {
        return [
            'progress_report' => 'Progress Report',
            'thesis' => 'Thesis',
            'manuscript' => 'Manuscript',
            'proposal' => 'Proposal',
            'literature_review' => 'Literature Review',
            'presentation' => 'Presentation',
            'other' => 'Other',
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        if ($this->type === 'other' && !empty($this->custom_type)) {
            return 'Other: ' . $this->custom_type;
        }

        return static::typeOptions()[$this->type] ?? str($this->type)->replace('_', ' ')->title()->toString();
    }
}
