<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiteratureEntry extends Model
{
    protected $fillable = [
        'student_id',
        'author',
        'year',
        'title',
        'journal',
        'doi_url',
        'research_objective',
        'methodology',
        'dataset',
        'findings',
        'limitations',
        'relevance',
        'keywords',
        'notes',
        'custom_fields',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'sort_order' => 'integer',
            'custom_fields' => 'array',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
