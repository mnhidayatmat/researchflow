<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\User;
use App\Models\Student;
use App\Models\AiMessage;

class AiConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'student_id',
        'title',
        'scope',
        'context_files',
    ];

    protected function casts(): array
    {
        return [
            'context_files' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AiMessage::class);
    }
}
