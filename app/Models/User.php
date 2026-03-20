<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Make sure AiConversation is available
use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\AuditLog;
use App\Models\Meeting;
use App\Models\Student;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'staff_id', 'matric_number',
        'phone', 'avatar', 'department', 'faculty', 'status', 'bio',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Role checks
    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isSupervisor(): bool { return in_array($this->role, ['supervisor', 'cosupervisor']); }
    public function isStudent(): bool { return $this->role === 'student'; }

    // Relationships
    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function supervisedStudents(): HasMany
    {
        return $this->hasMany(Student::class, 'supervisor_id');
    }

    public function cosupervisedStudents(): HasMany
    {
        return $this->hasMany(Student::class, 'cosupervisor_id');
    }

    public function allStudents()
    {
        return Student::where('supervisor_id', $this->id)
            ->orWhere('cosupervisor_id', $this->id);
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class, 'created_by');
    }

    public function aiConversations(): HasMany
    {
        return $this->hasMany(AiConversation::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }
}
