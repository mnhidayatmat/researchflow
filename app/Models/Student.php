<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'programme_id', 'programme_name', 'student_category', 'supervisor_id', 'cosupervisor_id',
        'supervisor_email', 'cosupervisor_email',
        'supervisor_approved_at', 'cosupervisor_approved_at',
        'research_title', 'research_abstract', 'intake', 'start_date',
        'expected_completion', 'actual_completion', 'status', 'overall_progress',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'expected_completion' => 'date',
            'actual_completion' => 'date',
            'supervisor_approved_at' => 'datetime',
            'cosupervisor_approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function programme(): BelongsTo { return $this->belongsTo(Programme::class); }
    public function supervisor(): BelongsTo { return $this->belongsTo(User::class, 'supervisor_id'); }
    public function cosupervisor(): BelongsTo { return $this->belongsTo(User::class, 'cosupervisor_id'); }

    public function tasks(): HasMany { return $this->hasMany(Task::class); }
    public function progressReports(): HasMany { return $this->hasMany(ProgressReport::class); }
    public function publicationTracks(): HasMany { return $this->hasMany(PublicationTrack::class); }
    public function files(): HasMany { return $this->hasMany(File::class); }
    public function folders(): HasMany { return $this->hasMany(Folder::class); }
    public function meetings(): HasMany { return $this->hasMany(Meeting::class); }
}
