<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicationAuthor extends Model
{
    protected $fillable = [
        'supervisor_publication_id',
        'name',
        'email',
        'department',
        'institution',
        'order',
    ];

    public function publication(): BelongsTo
    {
        return $this->belongsTo(SupervisorPublication::class, 'supervisor_publication_id');
    }
}
