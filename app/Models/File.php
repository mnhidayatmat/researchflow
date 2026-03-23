<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id', 'folder_id', 'uploaded_by', 'storage_owner_id', 'name', 'original_name',
        'mime_type', 'size', 'disk', 'path', 'description', 'version',
        'parent_file_id', 'is_latest', 'category',
    ];

    protected function casts(): array
    {
        return ['is_latest' => 'boolean'];
    }

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function folder(): BelongsTo { return $this->belongsTo(Folder::class); }
    public function uploader(): BelongsTo { return $this->belongsTo(User::class, 'uploaded_by'); }
    public function uploadedBy(): BelongsTo { return $this->belongsTo(User::class, 'uploaded_by'); }
    public function storageOwner(): BelongsTo { return $this->belongsTo(User::class, 'storage_owner_id'); }
    public function parentFile(): BelongsTo { return $this->belongsTo(File::class, 'parent_file_id'); }
    public function versions(): HasMany { return $this->hasMany(File::class, 'parent_file_id'); }
    public function revisions(): MorphMany { return $this->morphMany(Revision::class, 'revisable'); }

    public function sizeForHumans(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
