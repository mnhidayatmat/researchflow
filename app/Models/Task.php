<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    const STATUSES = [
        'backlog', 'planned', 'in_progress', 'waiting_review', 'revision', 'completed',
    ];

    protected $fillable = [
        'student_id', 'milestone_id', 'assigned_by', 'parent_id', 'title',
        'description', 'status', 'priority', 'start_date', 'due_date',
        'completed_at', 'progress', 'sort_order', 'estimated_hours',
        'duration_days', 'is_milestone',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
            'completed_at' => 'date',
            'is_milestone' => 'boolean',
        ];
    }

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function milestone(): BelongsTo { return $this->belongsTo(Milestone::class); }
    public function assignedBy(): BelongsTo { return $this->belongsTo(User::class, 'assigned_by'); }
    public function parent(): BelongsTo { return $this->belongsTo(Task::class, 'parent_id'); }
    public function subtasks(): HasMany { return $this->hasMany(Task::class, 'parent_id'); }
    public function revisions(): MorphMany { return $this->morphMany(Revision::class, 'revisable'); }

    public function dependencies(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'task_id', 'depends_on_id');
    }

    public function dependents(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'depends_on_id', 'task_id');
    }

    /**
     * Get the end date for Gantt chart (due_date or calculated)
     */
    public function getEndDate(): ?\Carbon\Carbon
    {
        if ($this->due_date) {
            return $this->due_date;
        }

        if ($this->start_date && $this->duration_days) {
            return $this->start_date->copy()->addDays($this->duration_days);
        }

        return $this->start_date?->copy()->addDays(7);
    }

    /**
     * Calculate duration in days from start and end dates
     */
    public function calculateDuration(): ?int
    {
        if ($this->start_date && $this->due_date) {
            return $this->start_date->diffInDays($this->due_date);
        }
        return $this->duration_days;
    }

    /**
     * Scope to get only milestone tasks
     */
    public function scopeMilestones($query)
    {
        return $query->where('is_milestone', true);
    }

    /**
     * Scope to get regular (non-milestone) tasks
     */
    public function scopeRegular($query)
    {
        return $query->where('is_milestone', false);
    }

    /**
     * Get Gantt chart data array
     */
    public function toGanttData(): array
    {
        // Use today as default start if not set
        $startDate = $this->start_date ? \Carbon\Carbon::parse($this->start_date) : now()->startOfDay();

        // Calculate end date
        if ($this->due_date) {
            $endDate = \Carbon\Carbon::parse($this->due_date);
        } elseif ($this->duration_days) {
            $endDate = $startDate->copy()->addDays($this->duration_days);
        } else {
            // Default duration based on priority
            $defaultDays = match($this->priority ?? 'medium') {
                'urgent' => 3,
                'high' => 5,
                'medium' => 7,
                'low' => 14,
                default => 7
            };
            $endDate = $startDate->copy()->addDays($defaultDays);
        }

        // Build custom class name for Frappe Gantt styling
        // Format: 'gantt-{status}' for proper CSS matching (with underscores converted to hyphens)
        $customClass = $this->is_milestone ? 'gantt-milestone' : 'gantt-' . str_replace('_', '-', $this->status);

        return [
            'id' => 'task-' . $this->id,
            'name' => $this->title,
            'start' => $startDate->format('Y-m-d'),
            'end' => $endDate->format('Y-m-d'),
            'progress' => $this->progress ?? 0,
            'dependencies' => $this->dependencies->pluck('id')->map(fn($id) => 'task-' . $id)->implode(','),
            'custom_class' => $customClass,
            'task_id' => $this->id,
            'is_milestone' => $this->is_milestone,
            'has_dates' => !empty($this->start_date) || !empty($this->due_date), // Track if dates were explicitly set
            'status' => $this->status, // Include status for frontend color mapping
        ];
    }
}
