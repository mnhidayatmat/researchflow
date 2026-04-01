<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiteratureMatrixConfig extends Model
{
    protected $fillable = ['student_id', 'columns'];

    protected function casts(): array
    {
        return ['columns' => 'array'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Default column definitions for a new literature matrix.
     */
    public static function defaultColumns(): array
    {
        return [
            ['key' => 'author',             'label' => 'Author(s)',            'visible' => true,  'sort_order' => 0],
            ['key' => 'year',               'label' => 'Year',                 'visible' => true,  'sort_order' => 1],
            ['key' => 'title',              'label' => 'Title',                'visible' => true,  'sort_order' => 2],
            ['key' => 'journal',            'label' => 'Journal/Source',       'visible' => true,  'sort_order' => 3],
            ['key' => 'doi_url',            'label' => 'DOI / URL',            'visible' => true,  'sort_order' => 4],
            ['key' => 'research_objective', 'label' => 'Research Objective',   'visible' => true,  'sort_order' => 5],
            ['key' => 'methodology',        'label' => 'Methodology',          'visible' => true,  'sort_order' => 6],
            ['key' => 'dataset',            'label' => 'Dataset',              'visible' => false, 'sort_order' => 7],
            ['key' => 'findings',           'label' => 'Findings / Results',   'visible' => true,  'sort_order' => 8],
            ['key' => 'limitations',        'label' => 'Limitations',          'visible' => false, 'sort_order' => 9],
            ['key' => 'relevance',          'label' => 'Relevance to Study',   'visible' => true,  'sort_order' => 10],
            ['key' => 'keywords',           'label' => 'Keywords',             'visible' => false, 'sort_order' => 11],
            ['key' => 'notes',              'label' => 'Notes',                'visible' => false, 'sort_order' => 12],
        ];
    }
}
